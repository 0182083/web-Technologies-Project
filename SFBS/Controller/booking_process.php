<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    echo "<p style='color:red;'>You must be logged in to book a ground.
          <a href='login.php'>Login here</a>.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_ground'])) {

    $user_id = intval($_SESSION['user_id']);       // UNIQUE user ID
    $username = $_SESSION['username'];              // DISPLAY ONLY
    $ground_id = intval($_POST['id']);
    $booking_date = $_POST['booking_date'];

    if (empty($ground_id) || empty($booking_date)) {
        echo "<script>
                alert('Invalid input.');
                window.location.href='book_ground.php';
              </script>";
        exit;
    }

    // Check if THIS USER already booked THIS ground on THIS date
    $check_stmt = $conn->prepare(
        "SELECT 1 FROM bookings WHERE user_id = ? AND ground_id = ? AND booking_date = ?"
    );
    $check_stmt->bind_param("iis", $user_id, $ground_id, $booking_date);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>
                alert('You have already booked this ground on this date.');
                window.location.href='book_ground.php';
              </script>";
        exit;
    }
    $check_stmt->close();

    // Check if ground is already booked by ANY user on this date
    $availability_stmt = $conn->prepare(
        "SELECT 1 FROM bookings WHERE ground_id = ? AND booking_date = ?"
    );
    $availability_stmt->bind_param("is", $ground_id, $booking_date);
    $availability_stmt->execute();
    $availability_stmt->store_result();

    if ($availability_stmt->num_rows > 0) {
        echo "<script>
                alert('This ground is already booked on the selected date.');
                window.location.href='book_ground.php';
              </script>";
        exit;
    }
    $availability_stmt->close();

    // Insert booking (store user_id, NOT username)
    $insert_stmt = $conn->prepare(
        "INSERT INTO bookings (user_id, ground_id, booking_date) VALUES (?, ?, ?)"
    );
    $insert_stmt->bind_param("iis", $user_id, $ground_id, $booking_date);

    if ($insert_stmt->execute()) {
        echo "<script>
                alert('Ground booked successfully!');
                window.location.href='user_dashboard.php';
              </script>";
    } else {
        echo "<p style='color:red;'>Error: {$insert_stmt->error}</p>";
    }

    $insert_stmt->close();
}

$conn->close();
?>
