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
        qr_data = f"Transaction ID: {transaction_id}\n\nCustomer: {customer_name}\n\nProducts:\n"
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

# Run the Flask server
if __name__ == '__main__':
    app.run(debug=True)
