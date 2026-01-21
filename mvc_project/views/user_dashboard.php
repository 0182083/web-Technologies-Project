<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header('Location: login.php');
    exit;
}



$bookings = [];

if (isset($_COOKIE['user_bookings'])) {
    $decoded = json_decode($_COOKIE['user_bookings'], true);
    if (is_array($decoded)) {
        $bookings = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<style>
    * {margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;}
    body {display: flex; min-height: 100vh; background-color: #f4f4f9;}
    .sidebar {width: 250px; background-color: #2c3e50; color: #ecf0f1; display: flex; flex-direction: column; padding: 20px;}
    .sidebar h2 {text-align: center; margin-bottom: 25px;}
    .sidebar a {text-decoration: none; color: #ecf0f1; padding: 12px; margin-bottom: 8px; border-radius: 6px; display: block;}
    .sidebar a:hover {background-color: #34495e;}
    .sidebar .logout {margin-top: auto; background-color: #e74c3c; text-align: center;}
    .sidebar .logout:hover {background-color: #c0392b;}
    .main-content {flex: 1; padding: 25px; display: flex; flex-direction: column; gap: 25px;}
    .header-card, .container, .history {background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);}
    .header-card h1 {font-size: 22px; color: #2c3e50; margin-bottom: 8px;}
    .container h3, .history h3 {margin-bottom: 20px; color: #2c3e50;}
    .images-container {display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;}
    .ground-card {border-radius: 8px; overflow: hidden; box-shadow: 0 3px 6px rgba(0,0,0,0.15); transition: transform 0.3s ease; cursor: pointer;}
    .ground-card:hover {transform: translateY(-5px);}
    .ground-card img {width: 100%; height: 160px; object-fit: cover;}
    .ground-card .caption {padding: 10px; text-align: center; background-color: #f9f9f9; font-size: 14px; font-weight: 600;}
    .booking-item {padding: 10px 0; border-bottom: 1px solid #ddd;}
    .booking-item:last-child {border-bottom: none;}
    @media (max-width: 768px) {.sidebar {width: 200px;}}
</style>
</head>
<body>

<div class="sidebar">
    <h2>User Dashboard</h2>
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <a href="book_ground.php"><i class='bx bx-calendar-check'></i> Book a Ground</a>
    <a href="../controllers/logout.php" class="logout"><i class='bx bx-arrow-out-left-square-half'></i> Logout</a>
</div>

<div class="main-content">

<div class="header-card">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></h1>
    <p>You can browse available sports facilities and book your preferred ground.</p>
</div>

<div class="history">
<h3>Your Recent Bookings</h3>

<?php if (!empty($bookings)): ?>
    <?php foreach (array_reverse($bookings) as $booking): ?>
        <div class="booking-item">
            <strong><?= htmlspecialchars($booking['facility'] ?? 'Unknown Ground'); ?></strong><br>
            <?= !empty($booking['booking_date'])
                    ? date('d M Y', strtotime($booking['booking_date'])) . ' ' .
                    (!empty($booking['created_at']) ? date('h:i A', strtotime($booking['created_at'])) : '')
                    : 'Date not available';
            ?>

        </div>

    <?php endforeach; ?>
<?php else: ?>
    <p>No recent bookings found.</p>
<?php endif; ?>
</div>

    <div class="container">
    <h3>Available Sports Facilities</h3>
        <div class="images-container">
            <a href="book_ground.php?facility_type=Football Ground" style="text-decoration: none;">
                <div class="ground-card">
                    <img src="images/Footballx1.jpg" alt="Football Ground">
                    <div class="caption">Football Ground</div>
                </div>
            </a>

            <a href="book_ground.php?facility_type=Tennis Ground" style="text-decoration: none;">
                <div class="ground-card">
                    <img src="images/tennis2.jpg" alt="Tennis Ground">
                    <div class="caption">Tennis Ground</div>
                </div>
            </a>

            <a href="book_ground.php?facility_type=Cricket Ground" style="text-decoration: none;">
                <div class="ground-card">
                    <img src="images/cricket.jpg" alt="Cricket Ground">
                    <div class="caption">Cricket Ground</div>
                </div>
            </a>

            <a href="book_ground.php?facility_type=Badminton Court" style="text-decoration: none;">
                <div class="ground-card">
                    <img src="images/badminton.jpg" alt="Badminton Court">
                    <div class="caption">Badminton Court</div>
                </div>
            </a>
        </div>
    </div>

</div>
</body>
</html>
