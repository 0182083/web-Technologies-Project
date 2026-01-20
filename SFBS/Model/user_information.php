<?php
session_start();
require_once 'db_connect.php';

// Handle form submissions
$msg = '';
$conflicts_to_resolve = [];

// --- Handle Edit Role ---
if (isset($_POST['edit_role']) && !empty($_POST['selected_users']) && !empty($_POST['new_role'])) {
    $selected_users = $_POST['selected_users'];
    $new_role = $_POST['new_role'];

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    foreach ($selected_users as $user_id) {
        $user_id = intval($user_id);
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt->execute();
    }
    $stmt->close();
    $msg = "Role updated successfully for selected user(s)!";
}

// --- Handle Deactivate ---
if (isset($_POST['deactivate']) && !empty($_POST['selected_users'])) {
    $selected_users = $_POST['selected_users'];

    $stmt_user = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    $stmt_booking = $conn->prepare("UPDATE bookings SET is_active = 0 WHERE user_id = ?");
    foreach ($selected_users as $user_id) {
        $user_id = intval($user_id);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_booking->bind_param("i", $user_id);
        $stmt_booking->execute();
    }
    $stmt_user->close();
    $stmt_booking->close();
    $msg = "Selected user(s) deactivated successfully!";
}

// --- Step 1: Handle Reactivate button ---
if (isset($_POST['reactivate']) && !empty($_POST['selected_users'])) {
    $selected_users = $_POST['selected_users'];
    $_SESSION['reactivate_users'] = $selected_users; // Store for step 2

    foreach ($selected_users as $user_id) {
        $user_id = intval($user_id);

        // Check conflicts: inactive booking vs active booking on same date/ground
        $conflict_stmt = $conn->prepare("
            SELECT b.booking_id AS old_booking_id, b.booking_date, f.ground_name,
                   b2.booking_id AS active_booking_id, u.username AS active_user
            FROM bookings b
            JOIN facilities f ON b.ground_id = f.id
            JOIN bookings b2 ON b2.ground_id = b.ground_id AND b.booking_date = b2.booking_date
            JOIN users u ON u.id = b2.user_id
            WHERE b.user_id = ? AND b.is_active = 0
              AND b2.is_active = 1
        ");
        $conflict_stmt->bind_param("i", $user_id);
        $conflict_stmt->execute();
        $result = $conflict_stmt->get_result();
        
        echo "Conflicts for user $user_id: " . $result->num_rows . " rows<br>";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $conflicts_to_resolve[$user_id][] = $row;
            }
        }
        $conflict_stmt->close();
    }

    if (!empty($conflicts_to_resolve)) {
        // Conflicts exist → show below user table
        $_SESSION['conflicts_to_resolve'] = $conflicts_to_resolve;
        $msg = "Conflicts detected! Resolve below before reactivation.";
    } else {
        // No conflicts → Reactivate users & their bookings
        $stmt_user = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt_booking = $conn->prepare("UPDATE bookings SET is_active = 1 WHERE user_id = ?");
        foreach ($selected_users as $user_id) {
            $user_id = intval($user_id);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $stmt_booking->bind_param("i", $user_id);
            $stmt_booking->execute();
        }
        $stmt_user->close();
        $stmt_booking->close();
        $msg = "Selected user(s) reactivated successfully!";
    }
}

// --- Step 2: Handle conflict resolution submission ---
if (isset($_POST['resolve_conflicts']) && !empty($_POST['choice'])) {
    $choices = $_POST['choice'];
    foreach ($choices as $old_booking_id => $keep) {
        $old_booking_id = intval($old_booking_id);
        $active_booking_id = intval($_POST['active_booking'][$old_booking_id]);

        if ($keep === 'old') {
            // Reactivate old booking
            $stmt = $conn->prepare("UPDATE bookings SET is_active = 1 WHERE booking_id = ?");
            $stmt->bind_param("i", $old_booking_id);
            $stmt->execute();
            $stmt->close();

            // Deactivate conflicting active booking
            $stmt2 = $conn->prepare("UPDATE bookings SET is_active = 0 WHERE booking_id = ?");
            $stmt2->bind_param("i", $active_booking_id);
            $stmt2->execute();
            $stmt2->close();
        }
        // else: keep active booking → old booking remains inactive
    }

    // Reactivate users after conflicts resolved
    foreach ($_SESSION['reactivate_users'] as $user_id) {
        $stmt_user = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->close();
    }

    unset($_SESSION['conflicts_to_resolve'], $_SESSION['reactivate_users']);
    $msg = "Users reactivated and conflicts resolved successfully!";
}

// --- Fetch all users for display ---
$sql = "SELECT id, first_name, last_name, dob, email, username, role, is_active FROM users";
$result = $conn->query($sql);
$conflicts = $_SESSION['conflicts_to_resolve'] ?? [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Information</title>
<style>
    body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 20px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        min-height: 100vh;
    }

    /* Main card container */
    .container {
        max-width: 1200px;
        margin: auto;
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }

    /* Headings */
    h1 {
        text-align: center;
        margin-bottom: 20px;
        font-weight: 600;
        color: #333;
    }

    h2, h3 {
        color: #333;
        margin-top: 25px;
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }

    table th,
    table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    table th {
        background-color: #667eea;
        color: #ffffff;
        font-weight: 600;
    }

    table tr:hover {
        background-color: #f0f0f0;
    }

    /* Conflict table */
    .conflict-table {
        margin-top: 15px;
        border-collapse: collapse;
        width: 100%;
    }

    .conflict-table th,
    .conflict-table td {
        padding: 10px;
        border: 1px solid #ddd;
    }

    .conflict-table th {
        background-color: #e67e22;
        color: white;
    }

    /* Buttons */
    button {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: 0.3s;
        font-size: 14px;
    }

    button#edit_role {
        background-color: #667eea;
        color: white;
    }

    button#edit_role:hover {
        background-color: #5a67d8;
    }

    button#deactivate {
        background-color: #e74c3c;
        color: white;
    }

    button#deactivate:hover {
        background-color: #c0392b;
    }

    button#reactivate {
        background-color: #38a169;
        color: white;
    }

    button#reactivate:hover {
        background-color: #2f855a;
    }

    /* Back button */
    button[type="button"] {
        background-color: #140205;
        color: white;
    }

    button[type="button"]:hover {
        background-color: #560a0d;
    }

    /* Select dropdown */
    select {
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-left: 8px;
    }

    /* Checkboxes */
    input[type="checkbox"] {
        transform: scale(1.2);
    }

    /* Messages */
    .msg {
        padding: 12px 15px;
        background-color: #38a169;
        color: white;
        border-radius: 8px;
        margin-bottom: 15px;
        font-weight: 500;
        animation: fadeIn 0.8s ease forwards;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 900px) {
        body {
            padding: 10px;
        }

        .container {
            padding: 15px;
        }

        table {
            font-size: 13px;
        }
    }

</style>
</head>
<body>

<div style="margin-bottom: 15px;">
    <a style="text-decoration: none;">
        <button type="button" onclick="window.history.back()" >
            Back to Dashboard
        </button>
    </a>
</div>

<div class="container">
<h1>User Information</h1>
<?php if($msg) echo "<div class='msg'>$msg</div>"; ?>

<form method="post">
    <table>
        <thead>
            <tr>
                <th>Select</th>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Email</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($_SESSION['role'] === 'Admin' && $row['id'] != $_SESSION['user_id']): ?>
                            <input type="checkbox" name="selected_users[]" value="<?= $row['id']; ?>">
                        <?php elseif ($row['role'] !== 'Admin' && $row['id'] != $_SESSION['user_id']): ?>
                            <input type="checkbox" name="selected_users[]" value="<?= $row['id']; ?>">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['id']); ?></td>
                    <td><?= htmlspecialchars($row['first_name']); ?></td>
                    <td><?= htmlspecialchars($row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['dob']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td><?= htmlspecialchars($row['role']); ?></td>
                    <td><?= $row['is_active'] ? 'Active' : 'Deactivated'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">No users found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top:15px;">
        <label for="new_role">New Role:</label>
        <select name="new_role" id="new_role">
            <option value="User">User</option>
            <option value="Manager">Manager</option>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <option value="Admin">Admin</option>
            <?php endif; ?>
        </select>
        <button type="submit" name="edit_role" id="edit_role">Edit Role</button>
        <button type="submit" name="deactivate" id="deactivate" onclick="return confirm('Are you sure you want to deactivate selected users?');">Deactivate</button>
        <button type="submit" name="reactivate" id="reactivate">Reactivate</button>
    </div>
</form>

<?php if(!empty($conflicts)): ?>
    <h2>Resolve Booking Conflicts</h2>
    <form method="post">
        <?php foreach($conflicts as $user_id => $user_conflicts): ?>
            <h3>User ID <?= $user_id ?></h3>
            <table class="conflict-table">
                <tr>
                    <th>Booking Date</th>
                    <th>Ground Name</th>
                    <th>Conflict With</th>
                    <th>Action</th>
                </tr>
                <?php foreach($user_conflicts as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['booking_date']) ?></td>
                        <td><?= htmlspecialchars($c['ground_name']) ?></td>
                        <td><?= htmlspecialchars($c['active_user']) ?>'s booking</td>
                        <td>
                            <input type="radio" name="choice[<?= $c['old_booking_id'] ?>]" value="old" required> Keep old booking
                            <input type="radio" name="choice[<?= $c['old_booking_id'] ?>]" value="active"> Keep active booking
                            <input type="hidden" name="active_booking[<?= $c['old_booking_id'] ?>]" value="<?= $c['active_booking_id'] ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
        <button type="submit" name="resolve_conflicts">Resolve Conflicts & Reactivate</button>
    </form>
<?php endif; ?>

</div>
<?php $conn->close(); ?>
</body>
</html>