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

// Fetch transactions for the user
$stmt = $conn->prepare("
    SELECT t.transaction_id, t.booking_id, f.name AS facility_name,
           t.amount, t.payment_method, t.status, t.created_at
    FROM transactions t
    LEFT JOIN bookings b ON t.booking_id = b.booking_id
    LEFT JOIN facility_slots s ON b.slot_id = s.slot_id
    LEFT JOIN facilities f ON s.facility_id = f.facility_id
    WHERE t.user_id = :user_id
    ORDER BY t.created_at DESC
");
$stmt->execute(['user_id' => $uid]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transaction History - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include "../includes/header.php"; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Transaction History</h2>

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Booking ID</th>
                            <th>Facility</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                        <?php
                                $status = $t['status'] ?? 'unknown';
                                $cls = $status === 'paid' ? 'success' : ($status === 'pending' ? 'warning' : 'secondary');
                            ?>
                        <tr>
                            <td><?= htmlspecialchars($t['transaction_id']) ?></td>
                            <td>#<?= htmlspecialchars($t['booking_id']) ?></td>
                            <td><?= htmlspecialchars($t['facility_name']) ?></td>
                            <td>$<?= number_format($t['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($t['payment_method']) ?></td>
                            <td><span class="badge bg-<?= $cls ?>"><?= ucfirst(htmlspecialchars($status)) ?></span></td>
                            <td><?= !empty($t['created_at']) ? date("M d, Y H:i", strtotime($t['created_at'])) : '-' ?>
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