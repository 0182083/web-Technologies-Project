<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
$uid = $user['user_id'] ?? $user['id'] ?? null;
if ($user['role'] !== 'customer' || !$uid) {
    header("Location: ../dashboard.php");
    exit();
}

// Check booking_id
if (!isset($_GET['booking_id'])) {
    header("Location: booking_history.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);

// Fetch booking details (using slot_id join, since bookings table doesn’t have facility_id)
$stmt = $conn->prepare("
    SELECT 
        b.booking_id,
        b.status,
        s.slot_date,
        s.start_time,
        s.end_time,
        f.name AS facility_name,
        f.price_per_hour
    FROM bookings b
    JOIN facility_slots s ON b.slot_id = s.slot_id
    JOIN facilities f ON s.facility_id = f.facility_id
    WHERE b.booking_id = :booking_id AND b.user_id = :user_id
    LIMIT 1
");
$stmt->execute(['booking_id' => $booking_id, 'user_id' => $uid]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: booking_history.php");
    exit();
}

// Prevent already paid booking
if ($booking['status'] === 'paid') {
    header("Location: booking_history.php");
    exit();
}

$message = '';
if (isset($_POST['pay'])) {
    $payment_method = sanitize($_POST['payment_method']);
    $amount = $booking['price_per_hour'];

    try {
        $conn->beginTransaction();

        // Insert transaction
        $stmt = $conn->prepare("
            INSERT INTO transactions (booking_id, user_id, amount, payment_method, status, created_at)
            VALUES (:booking_id, :user_id, :amount, :method, 'paid', NOW())
        ");
        $stmt->execute([
            'booking_id' => $booking_id,
            'user_id'    => $uid,
            'amount'     => $amount,
            'method'     => $payment_method
        ]);

        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'paid' WHERE booking_id = :booking_id");
        $stmt->execute(['booking_id' => $booking_id]);

        // Log activity
        $stmt = $conn->prepare("
            INSERT INTO activity_log (user_id, activity_type, details, created_at)
            VALUES (:user_id, 'payment', :details, NOW())
        ");
        $stmt->execute([
            'user_id' => $uid,
            'details' => "Payment of $$amount for Booking ID #$booking_id via $payment_method"
        ]);

        $conn->commit();
        $message = "✅ Payment successful!";
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Payment error: " . $e->getMessage());
        $message = "An error occurred while processing payment. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include "../includes/header.php"; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Payment for Booking #<?= htmlspecialchars($booking['booking_id']) ?></h2>

        <?php if ($message): ?>
        <div class="alert alert-<?= $message === '✅ Payment successful!' ? 'success' : 'danger' ?>">
            <?= $message ?>
        </div>
        <?php if ($message === '✅ Payment successful!'): ?>
        <a href="booking_history.php" class="btn btn-primary mt-2">Go to Booking History</a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (!$message || strpos($message, 'success') === false): ?>
        <div class="card shadow p-4">
            <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Facility:</strong>
                    <?= htmlspecialchars($booking['facility_name']) ?></li>
                <li class="list-group-item"><strong>Date:</strong>
                    <?= htmlspecialchars($booking['slot_date']) ?></li>
                <li class="list-group-item"><strong>Time:</strong>
                    <?= htmlspecialchars($booking['start_time']) ?> -
                    <?= htmlspecialchars($booking['end_time']) ?></li>
                <li class="list-group-item"><strong>Amount:</strong>
                    $<?= number_format($booking['price_per_hour'], 2) ?></li>
            </ul>

            <form method="POST">
                <div class="mb-3">
                    <label for="method" class="form-label">Payment Method</label>
                    <select name="payment_method" id="method" class="form-select" required>
                        <option value="">Select Method</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Bkash">Bkash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                <button type="submit" name="pay" class="btn btn-success w-100">Confirm & Pay</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>

</html>