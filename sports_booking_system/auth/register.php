<?php
require_once "../config/db.php";
require_once "../includes/functions.php";

$message = '';

if(isset($_POST['register'])){
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    // Check if username already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if($existingUser){
        $message = "Username already taken!";
    } else {
        // Insert new user
        $hashedPassword = md5($password); // You can switch to password_hash later
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, status, created_at) VALUES (?, ?, 'customer', 'active', NOW())");
        $stmt->execute([$username, $hashedPassword]);
        $message = "✅ Registration successful! You can now <a href='login.php'>Login</a>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - Sports Booking</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <h3 class="auth-title">📝 Register</h3>

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
                <button type="submit" name="register" class="btn-primary">Register</button>
            </form>

            <a href="../index.php" class="btn-secondary">🏠 Home</a>

            <p class="auth-footer">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>
</body>

</html>