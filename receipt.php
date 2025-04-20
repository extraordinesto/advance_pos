<?php
$conn = new mysqli('localhost', 'root', '', 'shop_pos');

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get transaction ID from URL
$transaction_id = $_GET['transaction_id'] ?? 0;

if (!$transaction_id) {
    die("Invalid transaction ID.");
}

// Fetch transaction details
$transaction_query = $conn->prepare("SELECT customer_name, transaction_date FROM pos_transaction WHERE transaction_id = ?");
$transaction_query->bind_param("i", $transaction_id);
$transaction_query->execute();
$transaction_query->bind_result($customer_name, $transaction_date);
$transaction_query->fetch();
$transaction_query->close();

if (!$customer_name) {
    die("Transaction not found.");
}

// Fetch purchased products
$items_query = $conn->prepare("SELECT product_name, quantity, total FROM pos_transaction_details WHERE transaction_id = ?");
$items_query->bind_param("i", $transaction_id);
$items_query->execute();
$items_result = $items_query->get_result();

// Fetch total amount
$total_amount_query = $conn->prepare("SELECT SUM(total) FROM pos_transaction_details WHERE transaction_id = ?");
$total_amount_query->bind_param("i", $transaction_id);
$total_amount_query->execute();
$total_amount_query->bind_result($total_amount);
$total_amount_query->fetch();
$total_amount_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="./css/receipt.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="receipt">
        <h2>🛒 POS Receipt</h2>
        <p><strong>Transaction ID:</strong> <?php echo $transaction_id; ?></p>
        <p><strong>Customer:</strong> <?php echo $customer_name; ?></p>
        <p><strong>Date:</strong> <?php echo $transaction_date; ?></p>

        <table>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
            </tr>
            <?php $products = []; ?>
            <?php while ($row = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['product_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['total']; ?></td>
                </tr>
                <?php $products[] = ["productName" => $row['product_name'], "quantity" => $row['quantity']]; ?>
            <?php endwhile; ?>
        </table>

        <div class="total-section">
            <p><strong>Total Amount: </strong> ₱<?php echo number_format($total_amount, 2); ?></p>
        </div>

        <!-- QR Code Section -->
        <div class="qr-section">
            <h3>Scan QR Code</h3>
            <img id="qrImage" src="" alt="QR Code" style="display: none; width:100px; height:100px;">
        </div>

        <button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
        <button><a class="back" href="./index.php">Back</a></button>
    </div>

    <script>
        $(document).ready(function () {
            let transactionId = "<?php echo $transaction_id; ?>";
            let customerName = "<?php echo $customer_name; ?>";
            let products = <?php echo json_encode($products); ?>;
            let totalAmount = "<?php echo $total_amount; ?>";

            $.ajax({
                url: "http://127.0.0.1:5000/generate_qr",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({ transactionId, customerName, products, totalAmount }),
                success: function (response) {
                    if (response.qr_url) {
                        $("#qrImage").attr("src", response.qr_url).show();
                    } else {
                        console.error("Error generating QR code.");
                    }
                },
                error: function (error) {
                    console.error("QR Code generation failed:", error);
                }
            });
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>
