$(document).ready(function() {
    $.ajax({
        url: "http://127.0.0.1:5000/api/top-products",
        method: "GET",
        success: function(response) {
            let html = "<ul>";
            response.forEach(product => {
                html += `<li><strong>${product.product_name}</strong>: Sold ${product.total_sold} times</li>`;
            });
            html += "</ul>";
            $("#topProducts").html(html);
        },
        error: function() {
            $("#topProducts").html("<p>❌ Error loading top products.</p>");
        }
    });
});

$(document).ready(function() {
    $.ajax({
        url: "http://127.0.0.1:5000/api/monthly-sales",
        method: "GET",
        success: function(response) {
            let labels = [];
            let salesData = [];

            response.forEach(sale => {
                labels.push(sale.month); // X-axis: Month
                salesData.push(sale.total_sales); // Y-axis: Sales Amount
            });

            let ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total Sales",
                        data: salesData,
                        borderColor: "#007bff",
                        backgroundColor: "rgba(0, 123, 255, 0.2)",
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    elements: {
                        line: {
                            tension: 0.3
                        },
                        point: {
                            radius: 3
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Daily'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Sales (₱)'
                            }
                        }
                    }
                }
            });
        },
        error: function() {
            console.error("Error fetching monthly sales data.");
        }
    });
});

$(document).ready(function () {
    fetchTopProducts();
});

function fetchTopProducts() {
    $.get("http://127.0.0.1:5000/api/sales-prediction", function (data) {
        let productContainer = $("#predictedProducts");
        productContainer.html(""); // Clear previous data

        if (data.error) {
            productContainer.html(`<p>Error: ${data.error}</p>`);
            return;
        }

        data.forEach((product) => {
            let card = `
                <div class="product-card">
                    <h4>${product.product_name}</h4>
                    <p>Predicted Sales: ${product.predicted_sales}</p>
                </div>
            `;
            productContainer.append(card);
        });
    }).fail(function () {
        $("#predictedProducts").html("<p>Failed to load predictions.</p>");
    });
}
