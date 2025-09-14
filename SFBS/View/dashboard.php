<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/db.php";
require_once "includes/functions.php";

checkLogin();

$user = currentUser();
$role = $user['role'];

// Reusable discount check
function hasDiscount($user_id, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn() >= 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sports Booking</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-light btn-sm me-3">🏠 Home</a>
                <span class="text-light me-3">Hello, <?= htmlspecialchars($user['username']) ?></span>
                <a href="auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Dashboard</h2>
        <p>Role: <strong><?= ucfirst($role) ?></strong></p>

        <?php if ($role == 'customer'): ?>
        <!-- Quick Links -->
        <div class="mb-4">
            <a href="customer/booking_history.php" class="btn btn-outline-primary me-2">📖 Booking History</a>
            <a href="customer/book_facility.php" class="btn btn-outline-success me-2">🎾 Book a Facility</a>
        </div>

        <h4 class="mt-4">Your Bookings</h4>
        <?php
        $stmt = $conn->prepare("
            SELECT b.booking_id, f.name AS facility, s.slot_date AS date, s.start_time, s.end_time, b.status
            FROM bookings b
            JOIN facility_slots s ON b.slot_id = s.slot_id
            JOIN facilities f ON s.facility_id = f.facility_id
            WHERE b.user_id=? ORDER BY b.booking_id DESC
        ");
        $stmt->execute([$user['user_id']]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (count($bookings) == 0): ?>
        <p>No bookings yet.</p>
        <?php else: ?>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-secondary text-center">
                <tr>
                    <th>Booking ID</th>
                    <th>Facility</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td>#<?= $b['booking_id'] ?></td>
                    <td><?= htmlspecialchars($b['facility']) ?></td>
                    <td><?= $b['date'] ?></td>
                    <td><?= $b['start_time'] ?> - <?= $b['end_time'] ?></td>
                    <td class="text-center">
                        <span
                            class="badge bg-<?= $b['status'] === 'paid' ? 'success' : ($b['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                            <?= ucfirst($b['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (hasDiscount($user['user_id'], $conn)): ?>
        <div class="alert alert-success">
            🎉 You are an old customer! You get a discount on your next booking.
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php elseif ($role == 'manager'): ?>
        <h4 class="mt-4">Manager Tools</h4>
        <a href="manager/manage_facilities.php" class="btn btn-primary me-2 mb-2">Manage Facilities</a>
        <a href="manager/manage_bookings.php" class="btn btn-success me-2 mb-2">Manage Bookings</a>
        <a href="manager/manage_slots.php" class="btn btn-warning mb-2">Manage Slots</a>

        <?php elseif ($role == 'admin'): ?>
        <h4 class="mt-4">Admin Tools</h4>
        <a href="admin/manage_users.php" class="btn btn-primary me-2 mb-2">Manage Users</a>
        <a href="admin/reports.php" class="btn btn-success me-2 mb-2">Booking Reports</a>
        <a href="admin/activity_log.php" class="btn btn-warning mb-2">Activity Log</a>
        <?php endif; ?>
    </div>

</body>

</html>