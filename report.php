<?php include 'inc/header.php'; ?>

<link rel="stylesheet" href="./css/report.css">

<div class="content-container">
    <div class="content">
        <h2 class="top">Top Products</h2>
        <div id="topProducts">
            <p>Loading top products...</p>
        </div>

        <h2 class="top" style=" padding-top: 30px;">Daily Sales Graph</h2>
        <div class="chart">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="content">
        <h2 class="top">Predicted Products</h2>
        <div id="predictedProducts">
            <ul>
                <li>Loading predictions...</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="./js/report.js" defer></script>

<?php include 'inc/footer.php'; ?>
