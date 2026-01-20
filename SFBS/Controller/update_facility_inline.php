<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

/* Admin-only access */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

/* Read JSON input */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    echo json_encode(["success" => false, "error" => "Invalid data"]);
    exit;
}

/* Update each changed row */
foreach ($data as $id => $fields) {
    $id = (int)$id;

    foreach ($fields as $column => $value) {
        $allowed = [
            'ground_name',
            'ground_location',
            'facility_type',
            'available_duration',
            'fees'
        ];

        if (!in_array($column, $allowed, true)) {
            continue;
        }

        $stmt = $conn->prepare("UPDATE facilities SET $column = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $id);
        $stmt->execute();
        $stmt->close();
    }
}

echo json_encode(["success" => true]);
$conn->close();
