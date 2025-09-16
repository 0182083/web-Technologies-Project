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

$search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
$facility_type = isset($_GET['facility_type']) ? $_GET['facility_type'] : '';

// Build query
$query = "
    SELECT s.slot_id, f.name AS facility_name, f.location, f.price_per_hour,
           s.slot_date, s.start_time, s.end_time, s.is_booked
    FROM facility_slots s
    JOIN facilities f ON s.facility_id = f.facility_id
    WHERE s.is_booked = 0
";

$params = [];

// date filter
if ($search_date !== '') {
    $query .= " AND s.slot_date = :slot_date";
    $params['slot_date'] = $search_date;
}

// facility filter
if ($facility_type !== '') {
    $query .= " AND f.name = :facility_type";
    $params['facility_type'] = $facility_type;
}

$query .= " ORDER BY s.slot_date, s.start_time";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all facility types
$facilities_stmt = $conn->prepare("SELECT DISTINCT name FROM facilities ORDER BY name");
$facilities_stmt->execute();
$facility_types = $facilities_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Available Slots - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include "../includes/header.php"; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Available Facility Slots</h2>

        <form class="row g-3 mb-4" method="GET">
            <div class="col-md-4">
                <input type="date" name="search_date" value="<?= htmlspecialchars($search_date) ?>"
                    class="form-control">
            </div>
            <div class="col-md-4">
                <select name="facility_type" class="form-select">
                    <option value="">All Facility Types</option>
                    <?php foreach ($facility_types as $ft): ?>
                    <option value="<?= htmlspecialchars($ft) ?>" <?= $ft == $facility_type ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ft) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Search / Filter</button>
            </div>
        </form>

        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Facility</th>
                    <th>Location</th>
                    <th>Price/Hour</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($slots)): ?>
                <tr>
                    <td colspan="6" class="text-center">No slots found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($slots as $slot): ?>
                <tr>
                    <td><?= htmlspecialchars($slot['facility_name']) ?></td>
                    <td><?= htmlspecialchars($slot['location']) ?></td>
                    <td>$<?= number_format($slot['price_per_hour'], 2) ?></td>
                    <td><?= htmlspecialchars($slot['slot_date']) ?></td>
                    <td><?= htmlspecialchars($slot['start_time']) ?> - <?= htmlspecialchars($slot['end_time']) ?></td>
                    <td>
                        <a href="book_facility.php?facility_id=<?= $slot['facility_id'] ?? '' ?>"
                            class="btn btn-success btn-sm">Book</a>
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