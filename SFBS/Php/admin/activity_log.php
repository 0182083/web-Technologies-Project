<?php
session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
if($user['role'] !== 'admin'){
    header("Location: ../dashboard.php");
    exit();
}

// Fetch activity log
$stmt = $conn->query("
    SELECT a.log_id, u.username, a.activity_type, a.details, a.created_at
    FROM activity_log a
    JOIN users u ON a.user_id = u.user_id
    ORDER BY a.created_at DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Log - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📋 Activity Log</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Activity Type</th>
                <th>Details</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($logs) > 0): ?>
                <?php foreach($logs as $row): ?>
                    <tr>
                        <td><?= $row['log_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['activity_type']) ?></td>
                        <td><?= htmlspecialchars($row['details']) ?></td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No activity found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="../dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
