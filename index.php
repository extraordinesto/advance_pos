<?php include 'inc/header.php'; ?>

<div class="content-container">
  <div class="content">
    <div class="cust">
      <h2>Customers</h2>
      <label for="customerSelect">Customer Name:</label>
      <select id="customerSelect" required>
        <option value="">Select Customer</option>
      </select>
      <a href="./add_customer.php" class="add-customers">Add Customer</a>
    </div>

    <h2>Products</h2>
    <div id="productList"></div>
  </div>

  <div class="content" id="cart-section">
    <h2>Selected Products</h2>
    <table id="salesTable">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <p id="totalAmount"><strong>Total: ₱0.00</strong></p>
    <div class="bottom">
      <input type="submit" value="Submit" class="submit-order">
      <!-- <input type="submit" value="Submit" class="submit-order" onclick="generateQRCode()"> -->
      <a href="./index.php" class="cancel-order">Cancel</a>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script defer src="./js/order.js"></script>

<?php include 'inc/footer.php'; ?>