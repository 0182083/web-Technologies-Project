<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
if ($user['role'] != 'manager') {
    header("Location: ../dashboard.php");
    exit();
}

// Handle add facility
if (isset($_POST['add_facility'])) {
    $name = sanitize($_POST['name']);
    $location = sanitize($_POST['location']);
    $price = max(0, floatval($_POST['price']));

    $stmt = $conn->prepare("INSERT INTO facilities (name, location, price_per_hour) VALUES (:name, :location, :price)");
    $stmt->execute([
        'name' => $name,
        'location' => $location,
        'price' => $price
    ]);
    header("Location: manage_facilities.php?success=1");
    exit();
}

// Handle delete facility
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM facilities WHERE facility_id=:id");
    $stmt->execute(['id' => $id]);
    header("Location: manage_facilities.php?deleted=1");
    exit();
}

// Fetch all facilities
$stmt = $conn->query("SELECT * FROM facilities ORDER BY facility_id DESC");
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Facilities - Manager Panel</title>
    <link rel="stylesheet" href="../assets/css/manager.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a class="brand" href="../dashboard.php">Manager Panel</a>
            <div class="nav-links">
                <a href="../dashboard.php" class="btn btn-secondary">⬅ Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Manage Facilities</h2>

        <!-- Alerts -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert success">✅ Facility added successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert danger">🗑 Facility deleted successfully.</div>
        <?php endif; ?>

        <!-- Add Facility Form -->
        <div class="card mb-4">
            <div class="card-header">Add New Facility</div>
            <div class="card-body">
                <form method="POST">
                    <label>Facility Name</label>
                    <input type="text" name="name" required>

                    <label>Location</label>
                    <input type="text" name="location" required>

                    <label>Price per Hour ($)</label>
                    <input type="number" name="price" required min="0" step="0.01">

                    <button type="submit" name="add_facility" class="btn btn-success mt-2">Add Facility</button>
                </form>
            </div>
        </div>

        <!-- Facility List -->
        <h4>Existing Facilities</h4>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Price/hr</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($facilities) == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">No facilities found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($facilities as $f): ?>
                    <tr>
                        <td><?= $f['facility_id'] ?></td>
                        <td><?= htmlspecialchars($f['name']) ?></td>
                        <td><?= htmlspecialchars($f['location']) ?></td>
                        <td>$<?= htmlspecialchars($f['price_per_hour']) ?></td>
                        <td>
                            <a href="manage_facilities.php?delete=<?= $f['facility_id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this facility?')">Delete</a>
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