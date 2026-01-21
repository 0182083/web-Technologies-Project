<?php
session_start();
require_once '../models/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    echo "<p style='color:red;'>You must be logged in to book a ground.
          <a href='../views/login.php'>Login here</a>.</p>";
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
                window.location.href='../views/book_ground.php';
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
                window.location.href='../views/book_ground.php';
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
                window.location.href='../views/book_ground.php';
              </script>";
        exit;
    }
    $availability_stmt->close();

    // --- COOKIE: store recent bookings for 7 days (after INSERT success) ---

    // You should already have these from POST in booking_process.php
    $ground_id = $_POST['id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;

    if ($ground_id && $booking_date) {
        // Ensure DB connection exists here ($conn). If not, require it:
        // require_once '../models/db_connect.php';

        // Fetch ground name for nicer dashboard display
        $ground_name = 'Unknown Ground';
        $stmt2 = $conn->prepare("SELECT ground_name FROM facilities WHERE id = ?");
        $stmt2->bind_param("i", $ground_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($row2 = $res2->fetch_assoc()) {
            $ground_name = $row2['ground_name'];
        }
        $stmt2->close();

        // Build cookie entry (keys your dashboard should read)
        $cookie_booking = [
            'facility'     => $ground_name,
            'booking_date' => $booking_date,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        // Append to existing cookie array
        $existing = [];
        if (isset($_COOKIE['user_bookings'])) {
            $decoded = json_decode($_COOKIE['user_bookings'], true);
            if (is_array($decoded)) $existing = $decoded;
        }

        $existing[] = $cookie_booking;

        // Keep last 20 to avoid cookie getting huge
        if (count($existing) > 20) {
            $existing = array_slice($existing, -20);
        }

        setcookie(
            'user_bookings',
            json_encode($existing),
            time() + (7 * 24 * 60 * 60),
            "/"
        );
    }
    // --- end COOKIE block ---


    // Insert booking (store user_id, NOT username)
    $insert_stmt = $conn->prepare(
        "INSERT INTO bookings (user_id, ground_id, booking_date) VALUES (?, ?, ?)"
    );
    $insert_stmt->bind_param("iis", $user_id, $ground_id, $booking_date);

    if ($insert_stmt->execute()) {
        echo "<script>
                alert('Ground booked successfully!');
                window.location.href='../views/user_dashboard.php';
              </script>";
    } else {
        echo "<p style='color:red;'>Error: {$insert_stmt->error}</p>";
    }

    $insert_stmt->close();
}

$conn->close();
?>
