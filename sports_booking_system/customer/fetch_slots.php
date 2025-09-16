<?php
session_start();
require_once "../config/db.php";
require_once "../includes/functions.php";
checkLogin();

$user = currentUser();
if ($user['role'] != 'customer') {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$facility_id = isset($_GET['facility_id']) ? intval($_GET['facility_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!$facility_id || !$date) {
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare("
    SELECT slot_id, slot_date, start_time, end_time 
    FROM facility_slots 
    WHERE facility_id = :facility_id 
      AND slot_date = :slot_date 
      AND is_booked = 0
    ORDER BY start_time
");
$stmt->execute([
    'facility_id' => $facility_id,
    'slot_date'   => $date
]);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($slots);