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
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="container">
        <h2>📋 Activity Log</h2>

        <div class="table-container">
            <table>
                <thead>
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
                    <tr>
                        <td colspan="5" class="text-center">No activity found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="../dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
    </div>
</body>


</html>