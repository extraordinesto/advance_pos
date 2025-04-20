from flask import Flask, request, send_from_directory, jsonify
from flask_cors import CORS  # Import CORS
import mysql.connector
import qrcode
import os
import pandas as pd
import numpy as np
from statsmodels.tsa.statespace.sarimax import SARIMAX
from statsmodels.tsa.holtwinters import ExponentialSmoothing
import cv2
import numpy as np
import pandas as pd
from pyzbar.pyzbar import decode
import pymysql
from flask_cors import cross_origin
from datetime import datetime
import traceback
from io import BytesIO
import base64

app = Flask(__name__, static_url_path='/qrcodes', static_folder='qrcodes')
CORS(app, resources={r"/api/*": {"origins": "*"}})
CORS(app)

# Database connection details
SHOP_DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "shop_inventory"
}

POS_DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "pos_db"
}

# Function to connect to a database
def get_db_connection(config):
    return mysql.connector.connect(**config)

# Endpoint: Fetch all item from shop_inventory
@app.route('/api/item', methods=['GET'])
def get_item():
    conn = get_db_connection(SHOP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM item")
    items = cursor.fetchall()
    cursor.close()
    conn.close()
    return jsonify(items)

@app.route('/api/customer', methods=['GET'])
def get_customer():
    conn = get_db_connection(SHOP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM customer")
    customers = cursor.fetchall()
    cursor.close()
    conn.close()
    return jsonify(customers)

@app.route('/api/submit_order', methods=['POST'])
def submit_order():
    try:
        # Connect to MySQL
        conn = get_db_connection(SHOP_DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        data = request.get_json()

        customer_id = data.get('customer_id')
        customer_name = data.get('customer_name')
        products = data.get('products', [])

        if not customer_id or not products:
            return jsonify({"error": "Missing required fields"}), 400

        # Step 1: Check stock before proceeding
        for product in products:
            product_id = product['product_id']
            quantity = product['quantity']

            cursor.execute("SELECT stock FROM item WHERE productID = %s", (product_id,))
            result = cursor.fetchone()

            if result is None:
                return jsonify({"error": f"Product ID {product_id} not found in item table."}), 400

            current_stock = result['stock']

            # Check if stock is 0 or less
            if current_stock <= 0:
                return jsonify({
                    "error": f"Product ID {product_id} is out of stock."
                }), 400

            if current_stock < quantity:
                return jsonify({
                    "error": f"Insufficient stock for Product ID {product_id} (Available: {current_stock}, Requested: {quantity})"
                }), 400

        # Step 2: Insert into `sale` table
        sale_date = datetime.today().strftime('%Y-%m-%d')

        for product in products:
            product_id = product['product_id']
            quantity = product['quantity']
            unit_price = product['price']

            # Get item info
            cursor.execute("SELECT itemName, itemNumber, discount FROM item WHERE productID = %s", (product_id,))
            item = cursor.fetchone()

            if not item:
                return jsonify({"error": f"Item info not found for Product ID {product_id}"}), 400

            item_name = item['itemName']
            item_number = item['itemNumber']
            item_discount = item['discount']

            # Insert sale into `sale` table
            cursor.execute(""" 
                INSERT INTO sale (itemNumber, customerID, customerName, itemName, saleDate, discount, quantity, unitPrice)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                item_number,
                customer_id,
                customer_name,
                item_name,
                sale_date,
                item_discount,
                quantity,
                unit_price
            ))

            # Update stock
            cursor.execute(""" 
                UPDATE item SET stock = stock - %s WHERE productID = %s
            """, (quantity, product_id))

        conn.commit()
        cursor.close()
        conn.close()

        # Redirect URL with customer_id and sale_date parameters
        receipt_url = f"receipt.php?customer_id={customer_id}&sale_date={sale_date}"

        return jsonify({
            "success": "Order placed successfully",
            "redirect_url": receipt_url
        })

    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500
    
# Ensure the 'qrcodes' folder exists
qr_folder = "qrcodes"
if not os.path.exists(qr_folder):
    os.makedirs(qr_folder)

@app.route('/generate_qr', methods=['POST'])
def generate_qr():
    try:
        data = request.get_json()
        print("Received Data:", data)

        # Extract data
        transaction_id = data.get("transactionId")
        customer_name = data.get("customerName")
        products = data.get("products", [])
        total_amount = data.get("totalAmount")

        # Validate
        if not transaction_id or not customer_name or not products:
            return jsonify({"error": "Missing required fields"}), 400

        # Format QR data
        qr_data = f"{transaction_id}\n\nCustomer: {customer_name}\n\nProducts:\n"
        for p in products:
            product_name = p.get("productName")
            quantity = p.get("quantity")
            if product_name and quantity:
                qr_data += f"- {product_name} x{quantity}\n"

        qr_data += f"\nTotal: {total_amount} PHP"

        # Ensure QR code folder exists
        qr_folder = "qrcodes"
        if not os.path.exists(qr_folder):
            os.makedirs(qr_folder)

        # Generate and save QR code
        qr_filename = f"{transaction_id}.png"
        qr_path = os.path.join(qr_folder, qr_filename)

        qr = qrcode.make(qr_data)
        qr.save(qr_path)

        return jsonify({"qr_url": f"http://127.0.0.1:5000/qrcodes/{qr_filename}"})

    except Exception as e:
        print("Error:", str(e))
        return jsonify({"error": str(e)}), 500

@app.route('/qrcodes/<filename>')
def serve_qr(filename):
    return send_from_directory('qrcodes', filename)

# Report
@app.route('/api/top-products', methods=['GET'])
def get_top_products():
    try:
        conn = get_db_connection(SHOP_DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        # Query to get total sold products with their sum of quantities, ensuring no duplicates by grouping by itemName
        query = """
            SELECT itemName AS product_name, SUM(quantity) AS total_sold
            FROM sale
            GROUP BY itemName
            ORDER BY total_sold DESC
            LIMIT 3
        """
        
        cursor.execute(query)
        top_products = cursor.fetchall()

        # Ensure we don't get duplicate entries (this should already be handled by the query)
        unique_products = {}
        for product in top_products:
            product_name = product['product_name']
            if product_name in unique_products:
                unique_products[product_name]['total_sold'] += product['total_sold']
            else:
                unique_products[product_name] = product

        cursor.close()
        conn.close()

        # Convert to a list again after merging duplicates
        top_products = list(unique_products.values())

        # If no products are found
        if not top_products:
            return jsonify({"message": "No products found."}), 404

        return jsonify(top_products)

    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Get Monthly Sales Data
@app.route('/api/monthly-sales', methods=['GET'])
def get_monthly_sales():
    try:
        conn = get_db_connection(SHOP_DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        query = """
            SELECT DATE_FORMAT(saleDate, '%Y-%m') AS month,
                   SUM(quantity * unitPrice) AS total_sales
            FROM sale
            GROUP BY month
            ORDER BY month;
        """

        cursor.execute(query)
        sales_data = cursor.fetchall()

        cursor.close()
        conn.close()
        return jsonify(sales_data)

    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Fetch Sales Data for Prediction
def fetch_sales_data():
    conn = get_db_connection(SHOP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)

    query = """
        SELECT itemNumber, itemName AS product_name, SUM(quantity) AS total_sold,
               DATE_FORMAT(saleDate, '%Y-%m') AS month
        FROM sale
        GROUP BY itemNumber, month
        ORDER BY month;
    """
    cursor.execute(query)
    sales_data = cursor.fetchall()
    cursor.close()
    conn.close()
    return sales_data

def fetch_sales_data():
    conn = get_db_connection(SHOP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)

    query = """
        SELECT itemNumber, itemName AS product_name,
               SUM(quantity) AS total_sold,
               DATE_FORMAT(saleDate, '%Y-%m') AS month
        FROM sale
        GROUP BY itemNumber, month
        ORDER BY month;
    """
    
    cursor.execute(query)
    sales_data = cursor.fetchall()
    cursor.close()
    conn.close()
    
    return sales_data

# Predict top 3 products for the next 3 months
@app.route('/api/sales-prediction', methods=['GET'])
def predict_sales():
    try:
        sales_data = fetch_sales_data()

        if not sales_data:
            return jsonify({"error": "No sales data found"}), 404

        df = pd.DataFrame(sales_data)

        # Ensure numeric and clean data
        df["total_sold"] = pd.to_numeric(df["total_sold"], errors="coerce")
        df = df.dropna(subset=["total_sold", "month", "itemNumber"])
        df["month"] = pd.to_datetime(df["month"])

        predictions = {}

        for item in df["itemNumber"].unique():
            product_sales = df[df["itemNumber"] == item].set_index("month")["total_sold"]

            if len(product_sales) < 1:  # Make sure there's enough data to predict
                continue

            try:
                model = SARIMAX(product_sales, order=(1, 1, 1), seasonal_order=(1, 1, 1, 12))
                results = model.fit(disp=False)

                # Predict next 3 months
                forecast = results.predict(start=len(product_sales), end=len(product_sales) + 2)
                predicted_sales = int(forecast.sum())

                predictions[item] = {
                    "product_name": df[df["itemNumber"] == item]["product_name"].iloc[0],
                    "predicted_sales": predicted_sales
                }

            except Exception as e:
                print(f"Prediction error for item {item}: {e}")
                continue

        if not predictions:
            return jsonify({"error": "No predictions were made."}), 404

        # Sort predictions
        top_3 = sorted(predictions.values(), key=lambda x: x["predicted_sales"], reverse=True)[:3]

        return jsonify(top_3)

    except Exception as e:
        return jsonify({"error": str(e)}), 500
    
# Database connection to shop_pos
def get_db1_connection():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='',
        db='shop_pos',
        cursorclass=pymysql.cursors.DictCursor
    )

@app.route('/api/mark-delivered', methods=['POST'])
def mark_delivered():
    connection = None
    try:
        data = request.json
        transaction_id = data.get('transaction_id')

        if not transaction_id:
            return jsonify({"error": "Missing transaction ID"}), 400

        print("Received Transaction ID:", transaction_id)

        connection = get_db1_connection()
        with connection.cursor() as cursor:
            # Check if the transaction exists in deliver table
            cursor.execute("SELECT COUNT(*) AS count FROM deliver WHERE transaction_id = %s", (transaction_id,))
            result = cursor.fetchone()

            if result['count'] == 0:
                return jsonify({"error": f"Transaction ID {transaction_id} not found in deliver"}), 404

            # Update status
            cursor.execute("UPDATE deliver SET status = 'delivered' WHERE transaction_id = %s", (transaction_id,))
            connection.commit()

            # Get transaction details
            cursor.execute("SELECT transaction_id, customer_name, transaction_date FROM pos_transaction WHERE transaction_id = %s", (transaction_id,))
            transaction = cursor.fetchone()

            if not transaction:
                return jsonify({"error": "Transaction details not found"}), 404

            # Generate new QR code
            qr_data = f"{transaction['transaction_id']} - Delivered\nCustomer: {transaction['customer_name']}\nDate: {transaction['transaction_date']}"
            qr = qrcode.make(qr_data)
            buffer = BytesIO()
            qr.save(buffer, format="PNG")
            qr_base64 = base64.b64encode(buffer.getvalue()).decode()

            return jsonify({
                "success": f"Delivery for Transaction ID {transaction_id} marked as completed",
                "qr_code": qr_base64
            }), 200

    except Exception as e:
        print("Error in mark_delivered:", str(e))
        return jsonify({"error": str(e)}), 500

    finally:
        if connection:
            connection.close()

@app.route('/api/scan-qrcode', methods=['GET'])
def scan_qrcode_from_camera():
    cap = cv2.VideoCapture(0)
    print("Scanning for QR code. Press 'q' to quit.")

    while True:
        ret, frame = cap.read()
        if not ret:
            continue

        decoded_objects = decode(frame)
        data_list = []

        for obj in decoded_objects:
            qr_data = obj.data.decode('utf-8')
            qr_type = obj.type
            data_list.append({"qr_code": qr_data, "type": qr_type})

            # Draw rectangle
            pts = obj.polygon
            if len(pts) == 4:
                pts = [(p.x, p.y) for p in pts]
                cv2.polylines(frame, [np.array(pts, dtype=np.int32)], True, (0, 255, 0), 2)

        cv2.imshow("QR Code Scanner", frame)

        if data_list:
            cap.release()
            cv2.destroyAllWindows()
            return jsonify(data_list[0])

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()
    return jsonify({"error": "No QR code detected"})

@app.route("/api/ims/add_item", methods=["POST"])
def add_item():
    try:
        data = request.json
        print("Received Data:", data)  # Debugging

        itemNumber = data["itemNumber"]
        itemName = data["itemName"]
        discount = data["discount"]
        stock = data["stock"]
        unitPrice = data["unitPrice"]
        status = data["status"]
        description = data["description"]

        db = get_db_connection(SHOP_DB_CONFIG)
        cursor = db.cursor()

        insert_query = """INSERT INTO item 
                          (itemNumber, itemName, discount, stock, unitPrice, status, description) 
                          VALUES (%s, %s, %s, %s, %s, %s, %s)"""
        
        values = (itemNumber, itemName, discount, stock, unitPrice, status, description)

        cursor.execute(insert_query, values)
        db.commit()

        cursor.close()
        db.close()

        return jsonify({"message": "Product added successfully!"}), 201

    except Exception as e:
        print("Error:", str(e))  # Debugging
        return jsonify({"error": "Failed to add product", "details": str(e)}), 500

# @app.route('/api/ims/display_item', methods=['GET'])
# def get_item():
#     conn = get_db_connection(SHOP_DB_CONFIG)
#     cursor = conn.cursor(dictionary=True)
#     cursor.execute("""SELECT * FROM ims_product p 
#                    JOIN ims_category c ON c.categoryid = p.categoryid 
#                    JOIN ims_brand b ON b.id = p.brandid
#                    JOIN ims_supplier s ON s.supplier_id = p.supplier
#                    """)
#     add_products = cursor.fetchall()
#     cursor.close()
#     conn.close()
#     return jsonify(add_products)

# Endpoint: Fetch all orders from pos_db
@app.route('/api/pos/orders', methods=['GET'])
def get_orders():
    conn = get_db_connection(POS_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM orders")
    orders = cursor.fetchall()
    cursor.close()
    conn.close()
    return jsonify(orders)

# Run the Flask server
if __name__ == '__main__':
    app.run(debug=True)
