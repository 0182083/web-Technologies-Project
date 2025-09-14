<?php
session_start();
include("config/db.php");
include("includes/functions.php");

$loggedIn = isset($_SESSION['id']);
$stmt = $conn->query("SELECT * FROM facilities ORDER BY facility_id DESC LIMIT 6");
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sports Facility Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('images/bg.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .content-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sports Booking</a>
        <div>
            <?php if($loggedIn): ?>
                <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
                <a href="auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-light btn-sm">Login</a>
                <a href="auth/register.php" class="btn btn-success btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-5 content-container">
    <h1 class="mb-4">Welcome to Sports Facility Booking</h1>
    <p class="mb-4">Book football, tennis, basketball courts easily online!</p>
    <h3>Available Facilities</h3>
    <div class="row">
        <?php if(count($facilities) > 0): ?>
            <?php foreach($facilities as $f): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($f['name']) ?></h5>
                            <p class="card-text">Location: <?= htmlspecialchars($f['location']) ?></p>
                            <p class="card-text">Price: $<?= htmlspecialchars($f['price_per_hour']) ?>/hour</p>
                        </div>
                        <div class="card-footer">
                            <?php if($loggedIn): ?>
                                <a href="customer/book_facility.php?facility_id=<?= $f['facility_id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
                            <?php else: ?>
                                <small class="text-muted">Login to book</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No facilities available yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>