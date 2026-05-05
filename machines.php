<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require 'db.php';

$machines = $conn->query("
    SELECT m.*,
           COUNT(p.production_id) as runs,
           COALESCE(SUM(p.material_used), 0) as total_material,
           COALESCE(SUM(p.waste_generated), 0) as total_waste
    FROM machine m
    LEFT JOIN production p ON m.machine_id = p.machine_id
    GROUP BY m.machine_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machines — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Machines</div>
            <div class="page-sub">Equipment status and production usage</div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card green"><div class="kpi-label">Active Machines</div><div class="kpi-value">3</div></div>
        <div class="kpi-card red"><div class="kpi-label">Inactive</div><div class="kpi-value">1</div></div>
        <div class="kpi-card blue"><div class="kpi-label">Total Machines</div><div class="kpi-value">4</div></div>
        <div class="kpi-card amber"><div class="kpi-label">Utilisation</div><div class="kpi-value">75%</div></div>
    </div>

    <div class="card-grid">
    <?php while ($m = $machines->fetch_assoc()):
        $is_active = $m['status'] === 'Active';
        $icon = str_contains($m['machine_type'], '3D') ? '🖨️' : (str_contains($m['machine_type'], 'Laser') ? '⚡' : '🧵');
    ?>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem">
                <div style="font-size:1.8rem"><?= $icon ?></div>
                <span class="pill <?= $is_active ? 'status-active' : 'status-inactive' ?>"><?= $m['status'] ?></span>
            </div>
            <div style="font-weight:500;font-size:15px;margin-bottom:.25rem"><?= htmlspecialchars($m['machine_type']) ?></div>
            <div style="font-size:12px;color:#888;margin-bottom:1rem">Machine ID: <?= $m['machine_id'] ?></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
                <div style="background:#f4f6f3;border-radius:8px;padding:.5rem">
                    <div style="font-size:18px;font-weight:500;color:#04342C"><?= $m['runs'] ?></div>
                    <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px">Runs</div>
                </div>
                <div style="background:#f4f6f3;border-radius:8px;padding:.5rem">
                    <div style="font-size:18px;font-weight:500;color:#04342C"><?= $m['total_material'] ?>g</div>
                    <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px">Material</div>
                </div>
                <div style="background:#f4f6f3;border-radius:8px;padding:.5rem">
                    <div style="font-size:18px;font-weight:500;color:#791F1F"><?= $m['total_waste'] ?>g</div>
                    <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px">Waste</div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>
</body>
</html>
