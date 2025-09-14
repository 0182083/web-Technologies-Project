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

// Allowed actions
$allowed_actions = ['block', 'unblock', 'delete', 'change_role'];

// Handle actions
if(isset($_GET['action'], $_GET['id'])){
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    if(!in_array($action, $allowed_actions)){
        header("Location: manage_users.php");
        exit();
    }

    if($action === "block"){
        $stmt = $conn->prepare("UPDATE users SET status='blocked' WHERE user_id=?");
        $stmt->execute([$user_id]);
    } elseif($action === "unblock"){
        $stmt = $conn->prepare("UPDATE users SET status='active' WHERE user_id=?");
        $stmt->execute([$user_id]);
    } elseif($action === "delete"){
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->execute([$user_id]);
    } elseif($action === "change_role" && isset($_GET['role'])){
        $newRole = ($_GET['role'] === 'manager') ? 'manager' : 'customer';
        $stmt = $conn->prepare("UPDATE users SET role=? WHERE user_id=?");
        $stmt->execute([$newRole, $user_id]);
    }

    header("Location: manage_users.php");
    exit();
}

// Fetch all users
$stmt = $conn->query("SELECT user_id, username, role, status, created_at FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1>Manage Users</h1>
    <a href="../dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>

    <table class="table table-bordered table-striped">
        <thead class="table-secondary">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($users) === 0): ?>
                <tr><td colspan="6" class="text-center">No users found.</td></tr>
            <?php else: ?>
                <?php foreach($users as $row): ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= ucfirst($row['role']) ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <?php if($row['status'] === 'active'): ?>
                                <a href="?action=block&id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Block</a>
                            <?php else: ?>
                                <a href="?action=unblock&id=<?= $row['user_id'] ?>" class="btn btn-success btn-sm">Unblock</a>
                            <?php endif; ?>

                            <a href="?action=delete&id=<?= $row['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>

                            <!-- Role change dropdown -->
                            <?php if($row['role'] !== 'admin'): ?>
                                <div class="dropdown d-inline">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Change Role
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?action=change_role&id=<?= $row['user_id'] ?>&role=manager">Manager</a></li>
                                        <li><a class="dropdown-item" href="?action=change_role&id=<?= $row['user_id'] ?>&role=customer">Customer</a></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
