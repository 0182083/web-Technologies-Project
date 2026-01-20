<?php
require_once "db_connect.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input data
    $ground_name = mysqli_real_escape_string($conn, trim($_POST['ground_name']));
    $ground_location = mysqli_real_escape_string($conn, trim($_POST['ground_location']));
    $facility_type = mysqli_real_escape_string($conn, $_POST['facility_type']);
    $available_duration = mysqli_real_escape_string($conn, trim($_POST['available_duration']));
    
    // Validate and cast fees to float for DECIMAL(10,2) storage
    $fees_input = trim($_POST['fees']);
    if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $fees_input)) {
        die("Error: Fees must be a valid number with up to 2 decimal places.");
    }
    $fees = (float) $fees_input;  // Cast to float for DECIMAL insertion

    // Handle file upload
    $target_dir = "uploads/"; // Directory to store uploaded images
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
    }

    if (!isset($_FILES['ground_picture']) || $_FILES['ground_picture']['error'] !== UPLOAD_ERR_OK) {
        die("Error: No file uploaded or there was an issue with the upload.");
    }

    $target_file = $target_dir . basename($_FILES['ground_picture']['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        die("Error: Only JPG, JPEG, PNG, and GIF files are allowed.");
    }

    // Move uploaded file to the target directory
    if (!move_uploaded_file($_FILES['ground_picture']['tmp_name'], $target_file)) {
        die("Error: There was an error uploading your file.");
    }

    // Insert data into the database (using prepared statement for security)
    $stmt = $conn->prepare("INSERT INTO facilities (ground_name, ground_location, facility_type, available_duration, fees, ground_picture) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssds", $ground_name, $ground_location, $facility_type, $available_duration, $fees, $target_file);

    if ($stmt->execute()) {
        echo "<script>alert('New facility added successfully.'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>