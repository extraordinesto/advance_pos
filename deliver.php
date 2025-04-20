<?php include 'inc/header.php'; ?>

<style>
    .content {
        align-items: center;
        justify-content: center;
        margin: auto;
        text-align: center;
    }

    .scan {
        padding: 10px 30px;
        border: 1px solid hsl(0, 0%, 70%);
        border-radius: 8px;
        font-size: 16px;
    }

    #result {
        font-size: 18px;
        font-weight: bolder;
        color: black;
        background-color: lightgray;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
        margin: 20px 0;
    }

    .delivered {
        margin: auto;
    }
    img{
        margin: auto;
    }

    #qrImage {
        display: none;
        width: 200px;
        height: 200px;
    }

    #deli {
        font-size: 20px;
        font-weight: bolder;
        color: black;
        padding: 10px;
        text-align: center;
        display: none;
    }
</style>

<div class="content-container">
    <div class="content">
        <button class="scan" onclick="startScan()">Start Scan</button>
        <p id="result">Scan a QR Code to display data here...</p>
        <input type="submit" value="Mark As Delivered" class="delivered" id="deliveredBtn" onclick="generateQRDelivered()" style="display: none;">
        <img id="qrImage" />
        <p id="deli">Delivered</p>
    </div>
</div>

<script>
    let scannedQRData = "";

    function startScan() {
        fetch("http://127.0.0.1:5000/api/scan-qrcode")
            .then(response => response.json())
            .then(data => {
                scannedQRData = data.qr_code;
                document.getElementById("result").innerText = "Transaction Details\n\n" + scannedQRData;
                document.getElementById("deliveredBtn").style.display = "block";
            })
            .catch(error => console.error("Error:", error));
    }

    function generateQRDelivered() {
        if (!scannedQRData) {
            alert("No QR code scanned!");
            return;
        }

        let transactionId = scannedQRData.trim().split(/\s+/)[0];
        console.log("Extracted Transaction ID:", transactionId);

        fetch("http://127.0.0.1:5000/api/mark-delivered", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ transaction_id: transactionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Delivery marked as completed!");
                    if (data.qr_code) {
                        document.getElementById("qrImage").src = "data:image/png;base64," + data.qr_code;
                        document.getElementById("qrImage").style.display = "block";
                        document.getElementById("deli").style.display = "block";
                    }
                } else {
                    alert("Error: " + data.error);
                }

                document.getElementById("deliveredBtn").style.display = "none";
            })
            .catch(error => console.error("Error:", error));
    }
</script>

<?php include 'inc/footer.php'; ?>
