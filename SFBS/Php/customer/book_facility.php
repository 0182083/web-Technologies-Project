<?php
session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
if ($user['role'] != 'customer') {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch facilities
$facilities_stmt = $conn->query("SELECT facility_id, name FROM facilities ORDER BY name");
$facilities = $facilities_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

// Booking submission
if (isset($_POST['book'])) {
    $facility_id = intval($_POST['facility_id'] ?? 0);
    $slot_id = intval($_POST['slot_id'] ?? 0);

    if ($facility_id > 0 && $slot_id > 0) {
        // Fetch slot info
        $stmt = $conn->prepare("
            SELECT s.slot_id, s.facility_id, s.is_booked, f.name AS facility_name
            FROM facility_slots s
            JOIN facilities f ON s.facility_id = f.facility_id
            WHERE s.slot_id = ? AND s.facility_id = ?
        ");
        $stmt->execute([$slot_id, $facility_id]);
        $slot = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($slot && $slot['is_booked'] == 0) {
            try {
                $conn->beginTransaction();

                // Mark slot as booked
                $conn->prepare("UPDATE facility_slots SET is_booked = 1 WHERE slot_id = ?")->execute([$slot_id]);

                // Insert booking
                $stmt = $conn->prepare("
                    INSERT INTO bookings (user_id, facility_id, slot_id, status, created_at)
                    VALUES (?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([$user['user_id'], $facility_id, $slot_id]);
                $booking_id = $conn->lastInsertId();

                // Insert into activity log
                $stmt = $conn->prepare("
                    INSERT INTO activity_log (user_id, activity_type, details, action, created_at)
                    VALUES (:user_id, 'booking', :details, :action, NOW())
                ");
                $stmt->execute([
                    'user_id' => $user['user_id'],
                    'details' => "Booked slot ID: $slot_id, Facility: " . $slot['facility_name'],
                    'action'  => 'booked_slot'
                ]);

                $conn->commit();
                header("Location: payment.php?booking_id=" . $booking_id);
                exit();
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Booking failed. Please try again.";
                error_log("Booking error: " . $e->getMessage());
            }
        } else {
            $error = "Sorry, this slot has already been booked.";
        }
    } else {
        $error = "Please select a valid facility and slot.";
    }
}

// Fetch available slots for selected facility (AJAX support)
$slots = [];
if (isset($_GET['facility_id'])) {
    $fid = intval($_GET['facility_id']);
    $stmt = $conn->prepare("SELECT slot_id, slot_date, start_time, end_time, is_booked FROM facility_slots WHERE facility_id=? ORDER BY slot_date, start_time");
    $stmt->execute([$fid]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Book Facility - Sports Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function loadSlots() {
        const facilityId = document.getElementById('facility').value;
        window.location.href = "?facility_id=" + facilityId;
    }
    </script>
</head>

<body class="bg-light">
    <?php include "../includes/header.php"; ?>

    <div class="container mt-5">
        <h2>Book Facility</h2>

        <?php if($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="facility" class="form-label">Select Facility</label>
                <select name="facility_id" id="facility" class="form-select" onchange="loadSlots()" required>
                    <option value="">-- Choose Facility --</option>
                    <?php foreach($facilities as $f): ?>
                    <option value="<?= $f['facility_id'] ?>"
                        <?= (isset($fid) && $fid==$f['facility_id'])?'selected':'' ?>>
                        <?= htmlspecialchars($f['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="slot" class="form-label">Select Slot</label>
                <select name="slot_id" id="slot" class="form-select" required>
                    <option value="">-- Choose Slot --</option>
                    <?php foreach($slots as $s): ?>
                    <?php if($s['is_booked']==0): ?>
                    <option value="<?= $s['slot_id'] ?>">
                        <?= htmlspecialchars($s['slot_date']) ?> | <?= htmlspecialchars($s['start_time']) ?> -
                        <?= htmlspecialchars($s['end_time']) ?>
                    </option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="book" class="btn btn-success">Confirm & Book</button>
        </form>
    </div>

    <?php include "../includes/footer.php"; ?>
</body>

</html>