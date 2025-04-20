$(document).ready(function () {
    fetchCustomers();
    fetchItem();
});

function fetchCustomers() {
    $.get("http://127.0.0.1:5000/api/customer", function (data) {
        let customerSelect = $("#customerSelect");
        data.forEach((customer) => {
            customerSelect.append(`
                <option value="${customer.customerID}">${customer.fullName}</option>
            `);
        });
    });
}

function fetchItem() {
    $.get("http://127.0.0.1:5000/api/item", function (data) {
        let productList = $("#productList");
        data.forEach((product) => {
            productList.append(`
                <div class="product-card" data-id="${product.productID}" data-price="${product.unitPrice}" onclick="addToCart(this)">
                    <h4>${product.itemName}</h4>
                    <p>Price: ₱${product.unitPrice}</p>
                    <p>Stock: ${product.stock}</p>
                </div>
            `);
        });
    });
}

function addToCart(element) {
    let productId = $(element).data("id");
    let price = $(element).data("price");
    let productName = $(element).find("h4").text();
    let existingRow = $(`#salesTable tbody tr[data-id='${productId}']`);

    if (existingRow.length) {
        let quantityInput = existingRow.find(".quantityInput");
        quantityInput.val(parseInt(quantityInput.val()) + 1);
        updateTotal(quantityInput[0]);
    } else {
        let row = `
        <tr data-id="${productId}">
          <td>${productName}</td>
          <td class="priceColumn">${price.toFixed(2)}</td>
          <td class="cart-controls">
            <button onclick="changeQuantity(this, -1)">-</button>
            <input type="number" class="quantityInput" value="1" min="1" oninput="updateTotal(this)">
            <button onclick="changeQuantity(this, 1)">+</button>
          </td>
          <td class="totalColumn">${price.toFixed(2)}</td>
          <td><button onclick="deleteRow(this)">🗑️</button></td>
        </tr>`;
        $("#salesTable tbody").append(row);

        // Immediately update total upon adding
        updateTotal($("#salesTable tbody tr:last-child .quantityInput")[0]);
    }
}

function changeQuantity(button, change) {
    let input = $(button).siblings(".quantityInput");
    let newValue = parseInt(input.val()) + change;
    if (newValue >= 1) {
        input.val(newValue);
        updateTotal(input[0]);
    }
}

function updateTotal(input) {
    let row = $(input).closest("tr");
    let price = parseFloat(row.find(".priceColumn").text()) || 0;
    let quantity = parseInt(input.value) || 1;
    let total = price * quantity;

    row.find(".totalColumn").text(`₱${total.toFixed(2)}`);

    calculateTotalAmount();
}

function calculateTotalAmount() {
    let total = 0;
    $("#salesTable tbody tr").each(function () {
        let rowTotal = parseFloat($(this).find(".totalColumn").text().replace("₱", "")) || 0;
        total += rowTotal;
    });

    $("#totalAmount").html(`<strong>Total: ₱${total.toFixed(2)}</strong>`);
}

function deleteRow(button) {
    $(button).closest("tr").remove();
    calculateTotalAmount();
}

$(document).on("click", ".submit-order", function (e) {
    e.preventDefault();

    let customer_id = $("#customerSelect").val();
    let customer_name = $("#customerSelect option:selected").text();
    let products = [];

    $("#salesTable tbody tr").each(function () {
        let product_id = $(this).data("id");
        let price = parseFloat($(this).find(".priceColumn").text());
        let quantity = parseInt($(this).find(".quantityInput").val());

        if (product_id && quantity > 0) {
            products.push({
                product_id,
                price,
                quantity
            });
        }
    });

    if (!customer_id || products.length === 0) {
        alert("Please select a customer and at least one product.");
        return;
    }

    $.ajax({
        url: "submit_order.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            customer_id,
            customer_name,
            products
        }),
        success: function (response) {
            alert(response.success || response.error);
            if (response.success) {
                window.location.href = response.redirect_url;
            }
        },
        error: function () {
            alert("Failed to process order.");z
        }
    });
});

function generateQRCode() {
    let transactionId = Date.now().toString(); // Or your actual ID
    let customerName = $("#customerSelect option:selected").text().trim();
    let totalAmount = $("#totalAmount").text().replace(/[^\d.]/g, ""); // Extract number only
    let products = [];

    $("#salesTable tbody tr").each(function () {
        let productName = $(this).find("td:first").text().trim();
        let quantity = parseInt($(this).find(".quantityInput").val()) || 1;

        if (productName && quantity > 0) {
            products.push({ productName, quantity });
        }
    });

    if (!customerName || products.length === 0) {
        alert("Please select a customer and at least one product.");
        return;
    }

    fetch("http://127.0.0.1:5000/generate_qr", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transactionId, customerName, products, totalAmount })
    })
    .then(response => response.json())
    .then(data => {
        if (data.qr_url) {
            document.getElementById("qrResult").innerHTML = `<img src="${data.qr_url}" alt="QR Code" width="120">`;
        } else {
            alert("Error generating QR code.");
        }
    })
    .catch(error => console.error("Error:", error));
}
