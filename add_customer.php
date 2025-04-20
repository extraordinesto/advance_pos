<?php
include 'inc/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $_POST['fullName'];
    $mobile = $_POST['mobile'];
    $phone2 = $_POST['phone2'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $address2 = $_POST['address2'];
    $city = $_POST['city'];
    $district = $_POST['districtSelect'];

    $conn = new mysqli('localhost', 'root', '', 'shop_inventory');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $check_sql = "SELECT * FROM `customer` WHERE `fullName` = '$fullName'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $exists = "Customer already exists!";
    } else {
        $sql = "INSERT INTO customer (`fullName`, `mobile`, phone2, email, `address`, `address2`, city, district) 
            VALUES ('$fullName', '$mobile', '$phone2', '$email', '$address', '$address2', '$city', '$district')";
        if ($conn->query($sql) === TRUE) {
            header("Location: ./index.php?success=addcustomer");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
}
?>
<link rel="stylesheet" href="./css/padd.css">
<div class="content-container">
    <div class="content">
        <h2>Customer Information</h2>
        <form method="post" action="">

            <div class="form-group">
                <label for="fullName" class="fullName">Full Name: </label>
                <input type="text" id="fullName" name="fullName" required>
            </div>

            <?php if (!empty($exists)): ?>
                <p class="exists"><?php echo $exists; ?></p>
            <?php endif; ?>

            <div class="form-group">
                <label for="mobile" class="mobile">Phone(mobile): </label>
                <input type="number" id="mobile" name="mobile" required>
            </div>

            <div class="form-group">
                <label for="phone2" class="phone2">Phone 2: </label>
                <input type="number" id="phone2" name="phone2" required>
            </div>

            <div class="form-group">
                <label for="email" class="email">Email: </label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="address" class="address">Address: </label>
                <input type="text" id="address" name="address" required>
            </div>

            <div class="form-group">
                <label for="address2" class="address2">Address 2: </label>
                <input type="text" id="address2" name="address2" required>
            </div>

            <div class="form-group">
                <label for="city" class="city">City: </label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-group">
                <label for="districtSelect" class="districtSelect">District: </label>
                <select id="districtSelect" name="districtSelect" required>
                    <option value="">Select District </option>
                    <option value="Ampara">Ampara</option>
                    <option value="Anuradhapura">Anuradhapura</option>
                    <option value="Badulla">Badulla</option>
                    <option value="Batticalao">Batticalao</option>
                    <option value="Colombo">Colombo</option>
                    <option value="Galle">Galle</option>
                    <option value="Gampaha">Gampaha</option>
                    <option value="Hambantota">Hambantota</option>
                    <option value="Jaffna">Jaffna</option>
                    <option value="Kalutara">Kalutara</option>
                    <option value="Kandy">Kandy</option>
                    <option value="Mannar">Mannar</option>
                    <option value="Puttalam">Puttalam</option>
                    <option value="Ratnapura">Ratnapura</option>
                </select>
            </div>

            <div class="bot">
                <input type="submit" value="Submit" class="sub">
                <a href="./index.php" class="can">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script defer src="./js/script.js"></script>

<? include './inc/footer.php'; ?>