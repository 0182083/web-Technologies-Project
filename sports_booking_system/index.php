<?php
session_start();
include("config/db.php");
include("includes/functions.php");

// Check if user is logged in
$loggedIn = isset($_SESSION['id']); // <- Use 'id' as we set in login.php

// Fetch some facilities to showcase
$stmt = $conn->query("SELECT * FROM facilities ORDER BY facility_id DESC LIMIT 6");
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sports Facility Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a class="brand" href="index.php">Sports Booking</a>
            <div class="nav-links">
                <?php if($loggedIn): ?>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="auth/logout.php" class="btn danger">Logout</a>
                <?php else: ?>
                <a href="auth/login.php" class="btn">Login</a>
                <a href="auth/register.php" class="btn success">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="container">
            <h1>Welcome to Sports Facility Booking</h1>
            <p>Book football, tennis, basketball courts easily online!</p>
        </div>
    </header>

    <!-- Facilities -->
    <main class="container">
        <h2 class="section-title">Available Facilities</h2>
        <div class="grid">
            <?php if(count($facilities) > 0): ?>
            <?php foreach($facilities as $f): ?>
            <div class="card">
                <div class="card-body">
                    <h3><?= htmlspecialchars($f['name']) ?></h3>
                    <p>Location: <?= htmlspecialchars($f['location']) ?></p>
                    <p>Price: $<?= htmlspecialchars($f['price_per_hour']) ?>/hour</p>
                </div>
                <div class="card-footer">
                    <?php if($loggedIn): ?>
                    <a href="customer/book_facility.php?facility_id=<?= $f['facility_id'] ?>" class="btn primary">Book
                        Now</a>
                    <?php else: ?>
                    <small class="muted">Login to book</small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>No facilities available yet.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Sports Facility Booking. All rights reserved.</p>
    </footer>

</body>

</html>