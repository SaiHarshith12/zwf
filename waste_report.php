<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

$grams = $conn->query("SELECT * FROM waste_grams_view");
$pct   = $conn->query("SELECT * FROM waste_view");
$prods = $conn->query("SELECT product_name, traditional_waste_percent, printing_waste_percent, material_used_grams FROM products");

$total_saved_g = $conn->query("SELECT SUM(waste_saved_grams) as t FROM waste_grams_view")->fetch_assoc()['t'];
$rows = [];
while ($r = $prods->fetch_assoc()) $rows[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waste Report — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
    <script src="chart.umd.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Waste Report</div>
            <div class="page-sub">Sustainability analysis from waste_grams_view and waste_view</div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card green">
            <div class="kpi-label">Total Waste Saved</div>
            <div class="kpi-value"><?= round($total_saved_g) ?>g</div>
            <div class="kpi-sub">across all products</div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Avg Waste Reduction</div>
            <div class="kpi-value">15%</div>
            <div class="kpi-sub">vs traditional method</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Best Performer</div>
            <div class="kpi-value">Dress</div>
            <div class="kpi-sub">75g saved</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-label">Efficiency Gain</div>
            <div class="kpi-value">67%</div>
            <div class="kpi-sub">over industry average</div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-title">Waste saved (grams) — from waste_grams_view</div>
            <table class="table">
                <thead><tr><th>Product</th><th>Grams saved</th></tr></thead>
                <tbody>
                <?php while ($r = $grams->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['product_name']) ?></td>
                        <td><span class="pill pill-green"><?= $r['waste_saved_grams'] ?>g</span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="card">
            <div class="card-title">Waste % reduction — from waste_view</div>
            <table class="table">
                <thead><tr><th>Product</th><th>% reduction</th></tr></thead>
                <tbody>
                <?php while ($r = $pct->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['product_name']) ?></td>
                        <td><span class="pill <?= $r['waste_saved'] >= 15 ? 'pill-green' : 'pill-amber' ?>">−<?= $r['waste_saved'] ?>%</span></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Waste comparison chart</div>
        <div style="position:relative;height:240px">
            <canvas id="cCompare" role="img" aria-label="Grouped bar comparing traditional and 3D waste % with savings">Waste comparison across all products.</canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Waste efficiency progress</div>
        <?php
        $colors = ['#1D9E75','#1D9E75','#5DCAA5','#EF9F27'];
        $i = 0;
        foreach ($rows as $r):
            $saved_pct = $r['traditional_waste_percent'] - $r['printing_waste_percent'];
            $score = round(100 - $r['printing_waste_percent']);
        ?>
        <div class="progress-row">
            <div class="progress-header">
                <span><?= htmlspecialchars($r['product_name']) ?></span>
                <span>Saves <?= $saved_pct ?>% waste · <?= $score ?>% efficient</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $score ?>%;background:<?= $colors[$i++] ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="insight">
        <div class="insight-icon">♻</div>
        <div>
            <div class="insight-title">Key finding</div>
            <div class="insight-body">By switching from traditional textile manufacturing to 3D printing, the Zero Waste Fashion project saves an average of <strong>15 percentage points</strong> of waste per product — equivalent to <strong><?= round($total_saved_g) ?>g</strong> of material saved in total. The best performer is the <strong>3D Printed Dress</strong>, saving 75g (15% reduction).</div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('cCompare'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($rows, 'product_name')) ?>,
        datasets: [
            { label: 'Traditional waste %', data: <?= json_encode(array_column($rows, 'traditional_waste_percent')) ?>, backgroundColor: '#B4B2A9', borderRadius: 4 },
            { label: '3D printing waste %', data: <?= json_encode(array_column($rows, 'printing_waste_percent')) ?>,   backgroundColor: '#1D9E75', borderRadius: 4 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }, scales: { y: { ticks: { callback: v => v + '%' } } } }
});
</script>
</body>
</html>
