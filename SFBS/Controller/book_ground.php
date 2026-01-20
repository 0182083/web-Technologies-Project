<?php
session_start();
// Database connection
require_once 'db_connect.php'; 

// Check for facility_type filter
$facility_filter = $_GET['facility_type'] ?? null;
$where_clause = $facility_filter ? " WHERE facility_type = '" . mysqli_real_escape_string($conn, $facility_filter) . "'" : "";

// Fetch available grounds with optional filter
$sql = "SELECT * FROM facilities" . $where_clause;
$result = $conn->query($sql);

// Fetch user ID and username from session
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ground</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Page background */
        body {
            background-color: #f4f4f9;
            padding: 25px;
            color: #2c3e50;
        }

        /* Page title */
        h1 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        /* Main layout */
        .container {
            display: flex;
            gap: 25px;
        }

        /* Cards (same style as dashboard cards) */
        .available-grounds,
        .booking-form {
            flex: 1;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        /* Section headings */
        h3 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #2c3e50;
        }

        /* Filter info */
        .filter-info {
            margin-bottom: 10px;
            font-size: 14px;
            color: #555;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table th,
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #34495e;
            color: #ffffff;
            font-weight: 600;
        }

        table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Images */
        img {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Form layout */
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Labels */
        label {
            font-size: 14px;
            color: #2c3e50;
        }

        /* Inputs & selects */
        input,
        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Buttons */
        button {
            padding: 10px;
            background-color: #2c3e50;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background-color: #34495e;
        }

        /* Mobile responsiveness */
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }
        }

    </style>
</head>
<body>
    <h1>Book a Sports Ground</h1>
    <div class="container">
        <!-- Available Grounds -->
        <div class="available-grounds">
            <h3>Available Grounds</h3>
            <?php if ($facility_filter): ?>
                <div class="filter-info">Showing only: <?= htmlspecialchars($facility_filter) ?> <a href="book_ground.php">Show All</a></div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ground Name</th>
                        <th>Location</th>
                        <th>Facility Type</th>
                        <th>Available Duration</th>
                        <th>Fees</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr onclick="selectGround(this)">
                                <td><?= htmlspecialchars($row['id']); ?></td>
                                <td><?= htmlspecialchars($row['ground_name']); ?></td>
                                <td><?= htmlspecialchars($row['ground_location']); ?></td>
                                <td><?= htmlspecialchars($row['facility_type']); ?></td>
                                <td><?= htmlspecialchars($row['available_duration']); ?></td>
                                <td><?= htmlspecialchars($row['fees']); ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($row['ground_picture']); ?>" alt="Ground Image">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No grounds available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Booking Form -->
        <div class="booking-form">
            <h3>Book a Ground</h3>
                <form action="payment_gateway.php" method="post" onsubmit="return validateBookingForm(event)">
                <label for="user_name">Your Name:</label>
                <input type="text" id="user_name" name="user_name" value="<?= htmlspecialchars($username) ?>" readonly required>

                <label for="id">Select Ground:</label>
                <select id="id" name="id" required>
                    <option value="">Select a Ground</option>
                    <?php
                    if ($result->num_rows > 0) {
                        $result->data_seek(0);
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['id']) . ' - ' . htmlspecialchars($row['ground_name']) . '</option>';
                        }
                    }
                    ?>
                </select>

                <label for="booking_date">Booking Date:</label>
                <input type="date" id="booking_date" name="booking_date" required>

                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>" required>

                <button type="submit">Proceed to Payment</button>
                <button type="button" onclick="window.history.back()">Back</button>
            </form>

            <script>
                function selectGround(row) {
                    const groundId = row.cells[0].textContent.trim();
                    const selectElement = document.getElementById('id');
                    selectElement.value = groundId;
                    document.querySelector('.booking-form').scrollIntoView({ behavior: 'smooth' });
                }

                function validateBookingForm(event) {
                    let isValid = true;

                    const userName = document.getElementById('user_name').value.trim();
                    const groundId = document.getElementById('id').value;
                    const bookingDate = document.getElementById('booking_date').value;

                    if (!/^[a-zA-Z\s]+$/.test(userName)) {
                        alert("Name should only contain letters and spaces.");
                        document.getElementById('user_name').focus();
                        isValid = false;
                    }

                    if (!groundId || !/^\d+$/.test(groundId) || parseInt(groundId) <= 0) {
                        alert("Please select a valid ground.");
                        document.getElementById('id').focus();
                        isValid = false;
                    }

                    const today = new Date().toISOString().split('T')[0];
                    if (bookingDate < today) {
                        alert("Booking date cannot be in the past.");
                        document.getElementById('booking_date').focus();
                        isValid = false;
                    }

                    if (!isValid) {
                        event.preventDefault();
                        return false;
                    }

                    return true;
                }

                document.querySelector('form').addEventListener('submit', validateBookingForm);
            </script>
        </div>
    </div>
</body>
</html>