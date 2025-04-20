<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory System</title>
  <link rel="stylesheet" href="./css/padd.css" />
  <link rel="stylesheet" href="./css/styles.css" />
</head>

<body>
  <div class="sidebar hidden" id="sidebar">
    <h2>POS</h2>
    <nav>
      <ul>
        <li><a href="./index.php">Order</a></li>
      </ul>
      <ul>
        <li><a href="./report.php">Report</a></li>
      </ul>
      <ul>
        <li><a href="./add_product.php">Add Product</a></li>
      </ul>
      <ul>
        <li><a href="./deliver.php">Deliver</a></li>
      </ul>
    </nav>
  </div>

  <div class="container">
    <div class="header">
      <button class="toggle-btn" id="toggleBtn">☰</button>

      <h3>Point of Sales - PHP</h3>

      <button class="logout-btn">
        <a href="action.php?action=logout">LOGOUT</a>
      </button>
    </div>