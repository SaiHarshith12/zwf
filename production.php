<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

$production = $conn->query("
    SELECT p.production_id, p.production_date, p.material_used, p.waste_generated,
           g.garment_name, m.machine_type,
           ROUND((p.waste_generated / p.material_used) * 100, 2) AS waste_pct
    FROM production p
    JOIN garment g ON p.garment_id = g.garment_id
    JOIN machine  m ON p.machine_id = m.machine_id
    ORDER BY p.production_date
");

$totals = $conn->query("SELECT SUM(material_used) as tm, SUM(waste_generated) as tw FROM production")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
    <script src="chart.umd.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Production</div>
            <div class="page-sub">Production runs, material usage, and waste generated</div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card green">
            <div class="kpi-label">Total Material Used</div>
            <div class="kpi-value"><?= $totals['tm'] ?>g</div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Total Waste Generated</div>
            <div class="kpi-value"><?= $totals['tw'] ?>g</div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-label">Overall Waste Rate</div>
            <div class="kpi-value"><?= round($totals['tw'] / $totals['tm'] * 100, 1) ?>%</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Production Runs</div>
            <div class="kpi-value">4</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-title">Material used vs waste generated per run (g)</div>
        <div style="position:relative;height:220px">
            <canvas id="cProd" role="img" aria-label="Grouped bar chart for each production run">Material used and waste generated per run.</canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Production run details</div>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>#</th><th>Date</th><th>Garment</th><th>Machine</th><th>Material Used</th><th>Waste Generated</th><th>Waste %</th></tr>
            </thead>
            <tbody>
            <?php
            $rows = [];
            while ($r = $production->fetch_assoc()) $rows[] = $r;
            foreach ($rows as $r):
                $wpct = $r['waste_pct'];
                $pill = $wpct <= 5 ? 'pill-green' : ($wpct <= 10 ? 'pill-amber' : 'pill-red');
            ?>
                <tr>
                    <td><?= $r['production_id'] ?></td>
                    <td><?= date('M d, Y', strtotime($r['production_date'])) ?></td>
                    <td><?= htmlspecialchars($r['garment_name']) ?></td>
                    <td><span class="pill pill-blue"><?= htmlspecialchars($r['machine_type']) ?></span></td>
                    <td><?= $r['material_used'] ?>g</td>
                    <td><?= $r['waste_generated'] ?>g</td>
                    <td><span class="pill <?= $pill ?>"><?= $wpct ?>%</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('cProd'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($rows, 'garment_name')) ?>,
        datasets: [
            { label: 'Material used (g)',   data: <?= json_encode(array_column($rows, 'material_used')) ?>,   backgroundColor: '#1D9E75', borderRadius: 4 },
            { label: 'Waste generated (g)', data: <?= json_encode(array_column($rows, 'waste_generated')) ?>, backgroundColor: '#E24B4A', borderRadius: 4 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }, scales: { y: { ticks: { callback: v => v + 'g' } } } }
});
</script>
</body>
</html>
