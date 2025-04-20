<?php
header('Content-Type: application/json');

// Connect to shop_pos for POS transactions
$conn = new mysqli('localhost', 'root', '', 'shop_pos');
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Retrieve POST data
$data = json_decode(file_get_contents("php://input"), true);
$customer_id = $data['customer_id'];
$customer_name = $data['customer_name'];
$products = $data['products'];

if (!$customer_id || empty($products)) {
    die(json_encode(["error" => "Missing required fields"]));
}

// Connect to shop_inventory for stock and sale handling
$inventory_conn = new mysqli('localhost', 'root', '', 'shop_inventory');
if ($inventory_conn->connect_error) {
    die(json_encode(["error" => "Inventory DB connection failed: " . $inventory_conn->connect_error]));
}

// Step 1: Check stock before proceeding
foreach ($products as $product) {
    $product_id = $product['product_id'];
    $quantity = $product['quantity'];

    $check_stock = $inventory_conn->prepare("SELECT stock FROM item WHERE productID = ?");
    $check_stock->bind_param("i", $product_id);
    $check_stock->execute();
    $check_stock->bind_result($current_stock);
    $check_stock->fetch();
    $check_stock->close();

    if ($current_stock === null) {
        die(json_encode(["error" => "Product ID $product_id not found in item table"]));
    }

    if ($current_stock < $quantity) {
        die(json_encode(["error" => "Insufficient stock for Product ID $product_id (Available: $current_stock, Requested: $quantity)"]));
    }
}

// Step 2: Insert into pos_transaction
$insert_transaction = $conn->prepare("INSERT INTO pos_transaction (customer_name, transaction_date) VALUES (?, NOW())");
$insert_transaction->bind_param("s", $customer_name);
$insert_transaction->execute();
$transaction_id = $insert_transaction->insert_id;
$insert_transaction->close();

// Step 3: Process each product
foreach ($products as $product) {
    $product_id = $product['product_id'];
    $quantity = $product['quantity'];
    $price = $product['price'];
    $total = $quantity * $price;

    // Get item name from inventory
    $get_item_name = $inventory_conn->prepare("SELECT itemName FROM item WHERE productID = ?");
    $get_item_name->bind_param("i", $product_id);
    $get_item_name->execute();
    $get_item_name->bind_result($item_name);
    $get_item_name->fetch();
    $get_item_name->close();

    // Insert transaction detail
    $insert_detail = $conn->prepare("INSERT INTO pos_transaction_details (transaction_id, product_id, quantity, total, product_name) VALUES (?, ?, ?, ?, ?)");
    $insert_detail->bind_param("iiids", $transaction_id, $product_id, $quantity, $total, $item_name);
    $insert_detail->execute();
    $insert_detail->close();

    // Insert delivery status
    $insert_delivery = $conn->prepare("INSERT INTO deliver (transaction_id, status) VALUES (?, 'pending')");
    $insert_delivery->bind_param("i", $transaction_id);
    $insert_delivery->execute();
    $insert_delivery->close();

    // Deduct stock
    $update_stock = $inventory_conn->prepare("UPDATE item SET stock = stock - ? WHERE productID = ?");
    $update_stock->bind_param("ii", $quantity, $product_id);
    if (!$update_stock->execute()) {
        die(json_encode(["error" => "Failed to update stock: " . $update_stock->error]));
    }
    $update_stock->close();

    // Insert into sale
    $sale_date = date("Y-m-d");
    $insert_sale = $inventory_conn->prepare("INSERT INTO sale (itemNumber, customerID, customerName, itemName, saleDate, discount, quantity, unitPrice) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
    $insert_sale->bind_param("iisssii", $product_id, $customer_id, $customer_name, $item_name, $sale_date, $quantity, $price);
    if (!$insert_sale->execute()) {
        die(json_encode(["error" => "Failed to insert into sale: " . $insert_sale->error]));
    }
    $insert_sale->close();
}

$inventory_conn->close();
$conn->close();

echo json_encode([
    "success" => "Order placed successfully",
    "transaction_id" => $transaction_id,
    "redirect_url" => "receipt.php?transaction_id=$transaction_id"
]);
