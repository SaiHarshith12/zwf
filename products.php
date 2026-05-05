<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

$products = $conn->query("SELECT * FROM products ORDER BY product_id");
$waste    = $conn->query("SELECT * FROM waste_grams_view");
$saved    = [];
while ($r = $waste->fetch_assoc()) $saved[$r['product_name']] = round($r['waste_saved_grams']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
    <script src="chart.umd.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Products</div>
            <div class="page-sub">All garments with material and waste data</div>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom:1.25rem">
        <div class="card">
            <div class="card-title">Waste saved per product (grams)</div>
            <div style="position:relative;height:200px">
                <canvas id="cSaved" role="img" aria-label="Horizontal bar chart of waste saved in grams per product">3D Dress 75g, Eco Shoes 42g, ZW Jacket 72g, Smart Hoodie 60g.</canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-title">Waste %: traditional vs 3D printing</div>
            <div style="position:relative;height:200px">
                <canvas id="cWaste" role="img" aria-label="Radar chart comparing traditional and 3D waste percentages">Traditional higher than 3D printing across all products.</canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">All products</div>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product name</th>
                    <th>Material</th>
                    <th>Used (g)</th>
                    <th>Trad. waste %</th>
                    <th>3D waste %</th>
                    <th>Saved (g)</th>
                    <th>Price</th>
                    <th>Efficiency</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $products->fetch_assoc()):
                $diff = $p['traditional_waste_percent'] - $p['printing_waste_percent'];
                $eff  = $diff >= 15 ? ['pill-green','High'] : ($diff >= 10 ? ['pill-amber','Medium'] : ['pill-red','Low']);
            ?>
                <tr>
                    <td><?= $p['product_id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['product_name']) ?></strong></td>
                    <td><span class="pill pill-blue"><?= htmlspecialchars($p['material_type']) ?></span></td>
                    <td><?= $p['material_used_grams'] ?>g</td>
                    <td><?= $p['traditional_waste_percent'] ?>%</td>
                    <td><?= $p['printing_waste_percent'] ?>%</td>
                    <td><strong><?= $saved[$p['product_name']] ?? '—' ?>g</strong></td>
                    <td>₹<?= number_format($p['price']) ?></td>
                    <td><span class="pill <?= $eff[0] ?>"><?= $eff[1] ?></span></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('cSaved'), {
    type: 'bar',
    data: {
        labels: ['3D Dress','Eco Shoes','ZW Jacket','Smart Hoodie'],
        datasets: [{ data: [75,42,72,60], backgroundColor: ['#1D9E75','#5DCAA5','#0F6E56','#9FE1CB'], borderRadius: 4 }]
    },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { callback: v => v + 'g' } } } }
});
new Chart(document.getElementById('cWaste'), {
    type: 'radar',
    data: {
        labels: ['3D Dress','Eco Shoes','ZW Jacket','Smart Hoodie'],
        datasets: [
            { label: 'Traditional %', data: [20,18,22,25], borderColor: '#B4B2A9', backgroundColor: 'rgba(180,178,169,0.15)', pointBackgroundColor: '#B4B2A9' },
            { label: '3D Printing %', data: [5,4,6,10],   borderColor: '#1D9E75', backgroundColor: 'rgba(29,158,117,0.15)',   pointBackgroundColor: '#1D9E75' }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
});
</script>
</body>
</html>
