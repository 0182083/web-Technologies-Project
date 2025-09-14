<?php
session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

// Only admin can access
$user = currentUser();
if($user['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// Default report type
$type = isset($_GET['type']) ? $_GET['type'] : 'daily';

// Query based on type
if ($type === 'monthly') {
    $sql = "SELECT DATE_FORMAT(s.slot_date, '%Y-%m') as period,
                   f.name as facility,
                   COUNT(b.booking_id) as total_bookings
            FROM bookings b
            JOIN facility_slots s ON b.slot_id = s.slot_id
            JOIN facilities f ON s.facility_id = f.facility_id
            GROUP BY period, f.name
            ORDER BY period DESC";
} else {
    $sql = "SELECT DATE(s.slot_date) as period,
                   f.name as facility,
                   COUNT(b.booking_id) as total_bookings
            FROM bookings b
            JOIN facility_slots s ON b.slot_id = s.slot_id
            JOIN facilities f ON s.facility_id = f.facility_id
            GROUP BY period, f.name
            ORDER BY period DESC";
}

$stmt = $conn->prepare($sql);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">📊 Booking Reports (<?= ucfirst($type); ?>)</h2>

    <div class="mb-3">
        <a href="reports.php?type=daily" class="btn btn-primary btn-sm">Daily Report</a>
        <a href="reports.php?type=monthly" class="btn btn-success btn-sm">Monthly Report</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Period</th>
                <th>Facility</th>
                <th>Total Bookings</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($reports) > 0): ?>
                <?php foreach($reports as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['period']) ?></td>
                        <td><?= htmlspecialchars($row['facility']) ?></td>
                        <td><?= $row['total_bookings'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No records found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="../dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>

</div>

</body>
</html>
