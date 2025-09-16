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
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>


<body>
    <div class="admin-container">
        <h1>👥 Manage Users</h1>
        <a href="../dashboard.php" class="btn-secondary">⬅ Back to Dashboard</a>

        <div class="table-container">
            <table>
                <thead>
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
                    <tr>
                        <td colspan="6" class="text-center">No users found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($users as $row): ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= ucfirst($row['role']) ?></td>
                        <td>
                            <span class="badge <?= $row['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <?php if($row['status'] === 'active'): ?>
                            <a href="?action=block&id=<?= $row['user_id'] ?>" class="btn-warning">Block</a>
                            <?php else: ?>
                            <a href="?action=unblock&id=<?= $row['user_id'] ?>" class="btn-success">Unblock</a>
                            <?php endif; ?>

                            <a href="?action=delete&id=<?= $row['user_id'] ?>" class="btn-danger"
                                onclick="return confirm('Are you sure?')">Delete</a>

                            <?php if($row['role'] !== 'admin'): ?>
                            <div class="dropdown">
                                <button class="btn-info">Change Role ▾</button>
                                <div class="dropdown-content">
                                    <a href="?action=change_role&id=<?= $row['user_id'] ?>&role=manager">Manager</a>
                                    <a href="?action=change_role&id=<?= $row['user_id'] ?>&role=customer">Customer</a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>