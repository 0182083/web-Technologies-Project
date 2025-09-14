<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
$uid = $user['user_id'] ?? $user['id'] ?? null;
if ($user['role'] != 'customer' || !$uid) {
    header("Location: ../dashboard.php");
    exit();
}

// Handle cancellation
if (isset($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);

    // Only cancel if booking belongs to this user and is not paid
    $stmt = $conn->prepare("
        SELECT b.booking_id, b.status, s.slot_id
        FROM bookings b
        JOIN facility_slots s ON b.slot_id = s.slot_id
        WHERE b.booking_id = :bid AND b.user_id = :uid
        LIMIT 1
    ");
    $stmt->execute(['bid' => $cancel_id, 'uid' => $uid]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking && $booking['status'] !== 'paid' && $booking['status'] !== 'cancelled') {
        try {
            $conn->beginTransaction();

            // Update booking
            $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = :bid");
            $stmt->execute(['bid' => $cancel_id]);

            // Free up the slot again
            $stmt = $conn->prepare("UPDATE facility_slots SET is_booked = 0 WHERE slot_id = :sid");
            $stmt->execute(['sid' => $booking['slot_id']]);

            // Log activity
            $stmt = $conn->prepare("
                INSERT INTO activity_log (user_id, activity_type, details, created_at)
                VALUES (:uid, 'cancellation', :details, NOW())
            ");
            $stmt->execute([
                'uid' => $uid,
                'details' => "Cancelled booking ID: $cancel_id"
            ]);

            $conn->commit();
            header("Location: booking_history.php?msg=cancel_success");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Cancel error: " . $e->getMessage());
        }
    }
}

// Fetch bookings for the user
$stmt = $conn->prepare("
    SELECT b.booking_id,
           f.name AS facility_name,
           f.location,
           f.price_per_hour,
           s.slot_date,
           s.start_time,
           s.end_time,
           b.status,
           b.created_at
    FROM bookings b
    JOIN facility_slots s ON b.slot_id = s.slot_id
    JOIN facilities f ON s.facility_id = f.facility_id
    WHERE b.user_id = :user_id
    ORDER BY s.slot_date DESC, s.start_time DESC
");
$stmt->execute(['user_id' => $uid]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Old customer discount
$booking_count = count($bookings);
$has_discount = $booking_count >= 3; // Eligible if 3+ bookings
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Booking History - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include "../includes/header.php"; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Your Booking History</h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancel_success'): ?>
        <div class="alert alert-info">✅ Booking cancelled successfully.</div>
        <?php endif; ?>

        <?php if ($has_discount): ?>
        <div class="alert alert-success">
            🎉 Congratulations! You are eligible for an <strong>Old Customer Discount</strong>.
        </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Booking ID</th>
                            <th>Facility</th>
                            <th>Location</th>
                            <th>Price/Hour</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Booked On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No bookings found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($b['booking_id']) ?></td>
                            <td><?= htmlspecialchars($b['facility_name']) ?></td>
                            <td><?= htmlspecialchars($b['location']) ?></td>
                            <td>$<?= number_format($b['price_per_hour'], 2) ?></td>
                            <td><?= htmlspecialchars($b['slot_date']) ?></td>
                            <td><?= htmlspecialchars($b['start_time']) ?> - <?= htmlspecialchars($b['end_time']) ?></td>
                            <td>
                                <span
                                    class="badge bg-<?= $b['status'] === 'paid' ? 'success' : ($b['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst(htmlspecialchars($b['status'])) ?>
                                </span>
                            </td>
                            <td><?= date("M d, Y H:i", strtotime($b['created_at'])) ?></td>
                            <td>
                                <?php if ($b['status'] !== 'paid' && $b['status'] !== 'cancelled'): ?>
                                <a href="payment.php?booking_id=<?= $b['booking_id'] ?>"
                                    class="btn btn-sm btn-primary">Pay</a>
                                <a href="booking_history.php?cancel=<?= $b['booking_id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Cancel this booking?')">Cancel</a>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>

</html>