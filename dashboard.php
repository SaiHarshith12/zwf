<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

// KPIs
$total_orders    = $conn->query("SELECT SUM(quantity) as t FROM orders")->fetch_assoc()['t'];
$waste_generated = $conn->query("SELECT SUM(waste_generated) as t FROM production")->fetch_assoc()['t'];
$waste_saved     = $conn->query("SELECT SUM(waste_saved_grams) as t FROM waste_grams_view")->fetch_assoc()['t'];
$products_count  = $conn->query("SELECT COUNT(*) as t FROM products")->fetch_assoc()['t'];
$machines_active = $conn->query("SELECT COUNT(*) as t FROM machine WHERE status='Active'")->fetch_assoc()['t'];
$total_revenue   = $conn->query("SELECT SUM(o.quantity * p.price) as t FROM orders o JOIN products p ON o.garment_id = p.product_id")->fetch_assoc()['t'];

// Chart data
$chart_data = $conn->query("SELECT product_name, traditional_waste_percent, printing_waste_percent FROM products");
$names = $trad = $print = [];
while ($r = $chart_data->fetch_assoc()) {
    $names[] = $r['product_name'];
    $trad[]  = $r['traditional_waste_percent'];
    $print[] = $r['printing_waste_percent'];
}

// Production chart
$prod_data = $conn->query("SELECT g.garment_name, p.material_used, p.waste_generated FROM production p JOIN garment g ON p.garment_id = g.garment_id");
$pnames = $pmats = $pwastes = [];
while ($r = $prod_data->fetch_assoc()) {
    $pnames[]  = $r['garment_name'];
    $pmats[]   = $r['material_used'];
    $pwastes[] = $r['waste_generated'];
}

// Recent orders
$recent = $conn->query("SELECT o.order_id, o.order_date, o.quantity, g.garment_name, p.price FROM orders o JOIN garment g ON o.garment_id = g.garment_id JOIN products p ON o.garment_id = p.product_id ORDER BY o.order_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
    <script src="chart.umd.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Dashboard</div>
            <div class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> — here's your sustainability overview</div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card green">
            <div class="kpi-label">Waste Saved</div>
            <div class="kpi-value"><?= round($waste_saved) ?>g</div>
            <div class="kpi-sub">across all products</div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Avg 3D Waste</div>
            <div class="kpi-value">7%</div>
            <div class="kpi-sub">vs 21% traditional</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Total Orders</div>
            <div class="kpi-value"><?= $total_orders ?></div>
            <div class="kpi-sub">units placed</div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Waste Generated</div>
            <div class="kpi-value"><?= $waste_generated ?>g</div>
            <div class="kpi-sub">in production runs</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-label">Active Machines</div>
            <div class="kpi-value"><?= $machines_active ?>/4</div>
            <div class="kpi-sub">operational</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">₹<?= number_format($total_revenue/1000, 1) ?>K</div>
            <div class="kpi-sub">from all orders</div>
        </div>
    </div>

    <!-- Charts row -->
    <div class="grid-2">
        <div class="card">
            <div class="card-title">Traditional vs 3D Printing Waste (%)</div>
            <div style="position:relative;height:240px">
                <canvas id="cWaste" role="img" aria-label="Grouped bar chart comparing waste percentages">Traditional: 20,18,22,25%. 3D Printing: 5,4,6,10%.</canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-title">Material Used vs Waste Generated (g)</div>
            <div style="position:relative;height:240px">
                <canvas id="cProd" role="img" aria-label="Grouped bar chart of material used vs waste generated">Material 500,300,450,400g. Waste 25,12,27,60g.</canvas>
            </div>
        </div>
    </div>

    <!-- Waste efficiency progress -->
    <div class="card">
        <div class="card-title">Waste efficiency score per product</div>
        <?php
        $prods = $conn->query("SELECT product_name, printing_waste_percent FROM products");
        $colors = ['#1D9E75','#1D9E75','#5DCAA5','#EF9F27'];
        $i = 0;
        while ($p = $prods->fetch_assoc()):
            $pct = $p['printing_waste_percent'];
            $score = 100 - $pct;
        ?>
        <div class="progress-row">
            <div class="progress-header">
                <span><?= $p['product_name'] ?></span>
                <span><?= $pct ?>% waste · <?= $score ?>% efficient</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $score ?>%;background:<?= $colors[$i++] ?>"></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Recent orders -->
    <div class="card">
        <div class="card-title">Recent orders</div>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Order ID</th><th>Date</th><th>Garment</th><th>Qty</th><th>Revenue</th></tr>
            </thead>
            <tbody>
            <?php while ($o = $recent->fetch_assoc()): ?>
                <tr>
                    <td>#<?= str_pad($o['order_id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                    <td><?= htmlspecialchars($o['garment_name']) ?></td>
                    <td><?= $o['quantity'] ?></td>
                    <td><strong>₹<?= number_format($o['quantity'] * $o['price']) ?></strong></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Insight -->
    <div class="insight">
        <div class="insight-icon">♻</div>
        <div>
            <div class="insight-title">Sustainability insight</div>
            <div class="insight-body">3D printing reduces textile waste from <strong>21%</strong> (industry average) to just <strong>7%</strong> — a <strong>67% improvement</strong>. Across all products, your process has saved <strong><?= round($waste_saved) ?>g</strong> of material from landfill.</div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('cWaste'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($names) ?>,
        datasets: [
            { label: 'Traditional %', data: <?= json_encode($trad) ?>,  backgroundColor: '#B4B2A9', borderRadius: 4 },
            { label: '3D Printing %', data: <?= json_encode($print) ?>, backgroundColor: '#1D9E75', borderRadius: 4 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }, scales: { y: { ticks: { callback: v => v + '%' } } } }
});
new Chart(document.getElementById('cProd'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($pnames) ?>,
        datasets: [
            { label: 'Material used (g)', data: <?= json_encode($pmats) ?>,   backgroundColor: '#1D9E75', borderRadius: 4 },
            { label: 'Waste generated (g)', data: <?= json_encode($pwastes) ?>, backgroundColor: '#E24B4A', borderRadius: 4 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }, scales: { y: { ticks: { callback: v => v + 'g' } } } }
});
</script>
</body>
</html>
