<?php
require_once "../config/db.php";
require_once "../includes/functions.php";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect("../dashboard.php");
}

$message = '';

if (isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check password (using md5 for your dummy data)
    if ($user && md5($password) === $user['password']) {
        // Login success: set session
        $_SESSION['id'] = $user['user_id'];       
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect to dashboard
        redirect("../dashboard.php");
    } else {
        $message = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Sports Booking</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <h3 class="auth-title">🔑 Login</h3>

            <?php if($message != ''): ?>
            <div class="alert"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn-primary">Login</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="register.php">Register</a>
            </p>

            <a href="../index.php" class="btn-secondary">🏠 Home</a>
        </div>
    </div>
</body>

</html>