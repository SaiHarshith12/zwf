<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

$sort = in_array($_GET['sort'] ?? '', ['date','qty','rev']) ? $_GET['sort'] : 'date';
$order_by = match($sort) {
    'qty' => 'o.quantity DESC',
    'rev' => '(o.quantity * p.price) DESC',
    default => 'o.order_date ASC'
};

$orders = $conn->query("
    SELECT o.order_id, o.order_date, o.quantity, g.garment_name, p.price,
           (o.quantity * p.price) AS revenue
    FROM orders o
    JOIN garment g ON o.garment_id = g.garment_id
    JOIN products p ON o.garment_id = p.product_id
    ORDER BY $order_by
");

$totals = $conn->query("
    SELECT SUM(o.quantity * p.price) as total_rev,
           SUM(o.quantity) as total_qty,
           COUNT(o.order_id) as total_orders
    FROM orders o JOIN products p ON o.garment_id = p.product_id
")->fetch_assoc();

// Revenue by garment for chart
$rev_data = $conn->query("
    SELECT g.garment_name, SUM(o.quantity * p.price) as rev
    FROM orders o
    JOIN garment g ON o.garment_id = g.garment_id
    JOIN products p ON o.garment_id = p.product_id
    GROUP BY g.garment_name
");
$rev_labels = $rev_vals = [];
while ($r = $rev_data->fetch_assoc()) {
    $rev_labels[] = $r['garment_name'];
    $rev_vals[]   = $r['rev'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
    <script src="chart.umd.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Orders</div>
            <div class="page-sub">All customer orders with revenue breakdown</div>
        </div>
        <div>
            Sort by:
            <a href="?sort=date" class="btn <?= $sort=='date' ? 'btn-primary' : 'btn-outline' ?>" style="margin-left:6px">Date</a>
            <a href="?sort=qty"  class="btn <?= $sort=='qty'  ? 'btn-primary' : 'btn-outline' ?>" style="margin-left:4px">Quantity</a>
            <a href="?sort=rev"  class="btn <?= $sort=='rev'  ? 'btn-primary' : 'btn-outline' ?>" style="margin-left:4px">Revenue</a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card green">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">₹<?= number_format($totals['total_rev'] / 1000, 1) ?>K</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Total Orders</div>
            <div class="kpi-value"><?= $totals['total_orders'] ?></div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-label">Units Sold</div>
            <div class="kpi-value"><?= $totals['total_qty'] ?></div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Avg Order Value</div>
            <div class="kpi-value">₹<?= number_format($totals['total_rev'] / $totals['total_orders']) ?></div>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom:1.25rem">
        <div class="card">
            <div class="card-title">Revenue by garment (donut)</div>
            <div style="position:relative;height:220px">
                <canvas id="cRev" role="img" aria-label="Donut chart showing revenue share by garment">Revenue breakdown by garment.</canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-title">Orders quantity by garment</div>
            <div style="position:relative;height:220px">
                <canvas id="cQty" role="img" aria-label="Bar chart of quantity ordered per garment">Quantities per garment.</canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">All orders</div>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Order ID</th><th>Date</th><th>Garment</th><th>Qty</th><th>Unit Price</th><th>Revenue</th></tr>
            </thead>
            <tbody>
            <?php while ($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?= str_pad($o['order_id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                    <td><?= htmlspecialchars($o['garment_name']) ?></td>
                    <td><?= $o['quantity'] ?></td>
                    <td>₹<?= number_format($o['price']) ?></td>
                    <td><strong>₹<?= number_format($o['revenue']) ?></strong></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('cRev'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($rev_labels) ?>,
        datasets: [{ data: <?= json_encode($rev_vals) ?>, backgroundColor: ['#1D9E75','#5DCAA5','#0F6E56','#9FE1CB'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } } }
});
new Chart(document.getElementById('cQty'), {
    type: 'bar',
    data: {
        labels: ['3D Dress','Eco Shoes','ZW Jacket','Smart Hoodie'],
        datasets: [{ data: [20,30,16,24], backgroundColor: '#1D9E75', borderRadius: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { stepSize: 5 } } } }
});
</script>
</body>
</html>
