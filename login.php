<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); exit();
}
require 'db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        header("Location: dashboard.php"); exit();
    } else {
        $error = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Zero Waste Fashion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">♻</div>
        <h1>Zero Waste Fashion</h1>
        <p class="auth-sub">Sign in to your dashboard</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email"
                   placeholder="admin@gmail.com" required
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••" required>

            <button type="submit">Sign in</button>
        </form>

        <p class="auth-footer">
            Demo — admin@gmail.com / admin123
        </p>
    </div>
</body>
</html>
