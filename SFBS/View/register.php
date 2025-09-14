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
        $message = "Registration successful! You can now <a href='login.php'>login</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow">
                <h3 class="text-center mb-3">Register</h3>
                <?php if($message != ''): ?>
                    <div class="alert alert-info"><?= $message ?></div>
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
                    <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                    <a href="../" class="btn btn-secondary w-100 mt-2">🏠 Home</a>
                    <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
