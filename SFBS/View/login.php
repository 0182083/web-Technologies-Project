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
        $_SESSION['id'] = $user['user_id'];       // match currentUser() expectations
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow">
                <h3 class="text-center mb-3">Login</h3>
                <?php if($message != ''): ?>
                    <div class="alert alert-danger"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    <p class="mt-3 text-center">
                        Don't have an account? <a href="register.php">Register</a>
                    </p>
                </form>

                <!-- ✅ Home button -->
                <div class="text-center mt-3">
                    <a href="../index.php" class="btn btn-secondary w-100">🏠 Home</a>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>

