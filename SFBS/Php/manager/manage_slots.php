<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
if($user['role'] != 'manager'){
    header("Location: ../dashboard.php");
    exit();
}

// Fetch all facilities for dropdown
$stmt = $conn->query("SELECT * FROM facilities ORDER BY name ASC");
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add slot
if(isset($_POST['add_slot'])){
    $facility_id = intval($_POST['facility_id']);
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Validate date & time
    if(strtotime($date) < strtotime(date('Y-m-d'))){
        $error = "Cannot add slot for past date.";
    } elseif($start_time >= $end_time){
        $error = "Start time must be before end time.";
    } else {
        // Check for overlapping slots
        $stmt = $conn->prepare("
            SELECT * FROM facility_slots 
            WHERE facility_id=:facility_id AND slot_date=:date
              AND ((start_time < :end_time AND end_time > :start_time))
        ");
        $stmt->execute([
            'facility_id'=>$facility_id,
            'date'=>$date,
            'start_time'=>$start_time,
            'end_time'=>$end_time
        ]);
        $overlap = $stmt->fetch(PDO::FETCH_ASSOC);

        if($overlap){
            $error = "This time slot overlaps with an existing slot for the same facility.";
        } else {
            // Insert new slot
            $stmt = $conn->prepare("
                INSERT INTO facility_slots (facility_id, slot_date, start_time, end_time, is_booked) 
                VALUES (:facility_id, :date, :start_time, :end_time, 0)
            ");
            $stmt->execute([
                'facility_id'=>$facility_id,
                'date'=>$date,
                'start_time'=>$start_time,
                'end_time'=>$end_time
            ]);
            header("Location: manage_slots.php?success=1");
            exit();
        }
    }
}

// Handle delete slot
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM facility_slots WHERE slot_id=:id");
    $stmt->execute(['id'=>$id]);
    header("Location: manage_slots.php?deleted=1");
    exit();
}

// Fetch all slots with facility info
$stmt = $conn->query("
    SELECT s.slot_id, f.name AS facility_name, s.slot_date, s.start_time, s.end_time, s.is_booked
    FROM facility_slots s
    JOIN facilities f ON s.facility_id = f.facility_id
    ORDER BY s.slot_date DESC, s.start_time ASC
");
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Slots - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Sports Booking</a>
        <div class="d-flex align-items-center">
            <a href="../dashboard.php" class="btn btn-light btn-sm me-3">⬅ Dashboard</a>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Manage Slots</h2>

    <!-- Alerts -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">Slot added successfully.</div>
    <?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-danger">Slot deleted successfully.</div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add Slot Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Add New Slot</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Facility</label>
                    <select name="facility_id" class="form-select" required>
                        <option value="">-- Select Facility --</option>
                        <?php foreach($facilities as $f): ?>
                            <option value="<?= $f['facility_id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                <button type="submit" name="add_slot" class="btn btn-success">Add Slot</button>
            </form>
        </div>
    </div>

    <!-- Slot List -->
    <h4>Existing Slots</h4>
    <table class="table table-striped table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>ID</th>
                <th>Facility</th>
                <th>Date</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if(count($slots) == 0): ?>
            <tr><td colspan="7" class="text-center">No slots available.</td></tr>
        <?php else: ?>
            <?php foreach($slots as $s): ?>
                <tr>
                    <td><?= $s['slot_id'] ?></td>
                    <td><?= htmlspecialchars($s['facility_name']) ?></td>
                    <td><?= $s['slot_date'] ?></td>
                    <td><?= $s['start_time'] ?></td>
                    <td><?= $s['end_time'] ?></td>
                    <td><?= $s['is_booked'] ? "<span class='badge bg-danger'>Booked</span>" : "<span class='badge bg-success'>Available</span>" ?></td>
                    <td>
                        <a href="manage_slots.php?delete=<?= $s['slot_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this slot?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
