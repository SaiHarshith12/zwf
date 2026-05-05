<?php // navbar.php — include on every protected page ?>
<nav class="navbar">
    <a href="dashboard.php" class="nav-brand">♻ ZeroWaste Fashion</a>
    <div class="nav-links">
        <a href="dashboard.php"    class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php'    ? 'active' : '' ?>">Dashboard</a>
        <a href="products.php"     class="<?= basename($_SERVER['PHP_SELF']) == 'products.php'     ? 'active' : '' ?>">Products</a>
        <a href="orders.php"       class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php'       ? 'active' : '' ?>">Orders</a>
        <a href="production.php"   class="<?= basename($_SERVER['PHP_SELF']) == 'production.php'   ? 'active' : '' ?>">Production</a>
        <a href="machines.php"     class="<?= basename($_SERVER['PHP_SELF']) == 'machines.php'     ? 'active' : '' ?>">Machines</a>
        <a href="waste_report.php" class="<?= basename($_SERVER['PHP_SELF']) == 'waste_report.php' ? 'active' : '' ?>">Waste Report
        <a href="why3d.php" class="<?= basename($_SERVER['PHP_SELF']) == 'why3d.php' ? 'active' : '' ?>" style="background:rgba(200,240,76,0.15);color:#C8F04C;border:1px solid rgba(200,240,76,0.3);">♻ Why 3D?</a>
        </a>
    </div>
    <div class="nav-user">
        👤 <?= htmlspecialchars($_SESSION['name']) ?>
        <span class="nav-role"><?= strtoupper($_SESSION['role']) ?></span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>
