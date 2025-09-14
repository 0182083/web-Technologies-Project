<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
if ($user['role'] != 'manager') {
    header("Location: ../dashboard.php");
    exit();
}

// Cancel booking
if (isset($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);

    // Fetch booking safely
    $stmt = $conn->prepare("SELECT slot_id FROM bookings WHERE booking_id=:id");
    $stmt->execute(['id' => $booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $slot_id = $booking['slot_id'];

        // Free up the slot only if it is booked
        $conn->prepare("UPDATE facility_slots SET is_booked=0 WHERE slot_id=:slot_id AND is_booked=1")
             ->execute(['slot_id' => $slot_id]);

        // Update booking status safely
        $conn->prepare("UPDATE bookings SET status='cancelled' WHERE booking_id=:id AND status='confirmed'")
             ->execute(['id' => $booking_id]);
    }

    header("Location: manage_bookings.php?cancelled=1");
    exit();
}

// Fetch all bookings with facility, slot, and user info
$stmt = $conn->query("
    SELECT b.booking_id, u.username, f.name AS facility_name, 
           s.slot_date, s.start_time, s.end_time, b.status, b.created_at
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN facility_slots s ON b.slot_id = s.slot_id
    JOIN facilities f ON s.facility_id = f.facility_id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../dashboard.php">Manager Panel</a>
        <div>
            <a href="../dashboard.php" class="btn btn-light btn-sm">⬅ Back to Dashboard</a>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Manage Bookings</h2>

    <?php if (isset($_GET['cancelled'])): ?>
        <div class="alert alert-success">Booking cancelled successfully.</div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Facility</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Booked On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($bookings) == 0): ?>
            <tr><td colspan="8" class="text-center">No bookings found.</td></tr>
        <?php else: ?>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= $b['booking_id'] ?></td>
                    <td><?= htmlspecialchars($b['username']) ?></td>
                    <td><?= htmlspecialchars($b['facility_name']) ?></td>
                    <td><?= $b['slot_date'] ?></td>
                    <td><?= $b['start_time'] ?> - <?= $b['end_time'] ?></td>
                    <td>
                        <?php if ($b['status'] == 'confirmed'): ?>
                            <span class="badge bg-success">Confirmed</span>
                        <?php elseif ($b['status'] == 'cancelled'): ?>
                            <span class="badge bg-danger">Cancelled</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= ucfirst($b['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $b['created_at'] ?></td>
                    <td>
                        <?php if ($b['status'] == 'confirmed'): ?>
                            <a href="manage_bookings.php?cancel=<?= $b['booking_id'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Cancel this booking?')">Cancel</a>
                        <?php else: ?>
                            <span class="text-muted">No Action</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
</body>
</html>
