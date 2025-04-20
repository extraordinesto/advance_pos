<?php
include 'inc/header.php';

?>
<style>
    tbody tr:nth-child(odd) {
        background-color: rgb(233, 233, 233);
    }

    table {
        border-radius: 10px;
    }

    .form-group select {
        width: 60%;
    }
</style>
<link rel="stylesheet" href="./css/padd.css">
<div class="content-container">
    <div class="content">
        <h2>Add Product</h2>
        <form method="post" action="">
            <?php if (!empty($exists)): ?>
                <p class="exists"><?php echo $exists; ?></p>
            <?php endif; ?>

            <div class="form-group">
                <label for="itemNumber" class="itemNumber">Item Number: </label>
                <input type="text" id="itemNumber" name="itemNumber" required>
            </div>

            <div class="form-group">
                <label for="itemName" class="itemName">Item Name: </label>
                <input type="text" id="itemName" name="itemName" required>
            </div>

            <div class="form-group">
                <label for="status" class="status">Unit: </label>
                <select id="status" required>
                    <option value="">Select Status </option>
                    <option value="Active">Active</option>
                    <option value="Disable">Disable</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description" class="description">Description: </label>
                <input type="text" id="description" name="description">
            </div>

            <div class="form-group">
                <label for="discount" class="discount">Discount %: </label>
                <input type="number" id="discount" name="discount" required>
            </div>

            <div class="form-group">
                <label for="stock" class="stock">Quantity: </label>
                <input type="number" id="stock" name="stock" required>
            </div>

            <div class="form-group">
                <label for="unitPrice" class="unitPrice">Unit Price: </label>
                <input type="number" id="unitPrice" name="unitPrice" required>
            </div>

            <div class="bot">
                <input type="submit" value="Submit" class="sub">
                <a href="./index.php" class="can">Cancel</a>
            </div>
        </form>
    </div>

    <div class="content">
        <h2>Product Lists</h2>
        <table id="salesTable">
            <thead>
                <tr>
                    <th>Item Number</th>
                    <th>Item Name</th>
                    <th>Stock</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody id="display_product"></tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetchProducts();

        document.querySelector("form").addEventListener("submit", function(event) {
            event.preventDefault();
            addProduct();
        });
    });

    function fetchProducts() {
        fetch("http://127.0.0.1:5000/api/item")
            .then(response => response.json())
            .then(data => displayProducts(data))
            .catch(error => console.error("Error fetching products:", error));
    }

    function displayProducts(add_products) {
        let tableBody = document.getElementById("display_product");
        tableBody.innerHTML = ""; // Clear existing data

        add_products.forEach(product => {
            let row = document.createElement("tr");
            row.innerHTML = `
            <td>${product.itemNumber}</td>
            <td>${product.itemName}</td>
            <td>${product.stock}</td>
            <td>${product.unitPrice}</td>
        `;
            tableBody.appendChild(row);
        });
    }

    function addProduct() {
        let itemNumber = document.getElementById("itemNumber").value;
        let itemName = document.getElementById("itemName").value;
        let discount = document.getElementById("discount").value;
        let stock = document.getElementById("stock").value;
        let unitPrice = document.getElementById("unitPrice").value;
        let status = document.getElementById("status").value;
        let description = document.getElementById("description").value;

        if (!itemNumber || !itemName || !discount || !stock || !unitPrice || !status) {
            alert("All fields are required.");
            return;
        }

        fetch("http://127.0.0.1:5000/api/ims/add_item", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    itemNumber,
                    itemName,
                    discount,
                    stock,
                    unitPrice,
                    status,
                    description
                }),
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                document.querySelector("form").reset();
                fetchProducts();
            })
            .catch(error => console.error("Error adding product:", error));
    }
</script>
<script defer src="./js/script.js"></script>

<? include './inc/footer.php'; ?>