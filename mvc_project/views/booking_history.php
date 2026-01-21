<?php
require_once '../models/db_connect.php';

// Fetch booking history using correct relationships
$sql = "
    SELECT
        b.booking_id AS booking_id,
        u.username,
        f.ground_name,
        b.booking_date,
        b.created_at
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN facilities f ON b.ground_id = f.id
    ORDER BY b.created_at DESC
";

$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking History</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        /* Page title */
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
        }

        /* Card container effect */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            font-size: 14px;
        }

        /* Table cells */
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        /* Table header */
        th {
            background-color: #667eea;
            color: white;
            font-weight: 600;
        }

        /* Row hover */
        tbody tr:hover {
            background-color: #f0f0f0;
        }

        /* Back button */
        button {
            padding: 10px 20px;
            background-color: #140205;  
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        button:hover {
            background-color: #560a0d;
        }

        /* Responsive */
        @media (max-width: 900px) {
            body {
                padding: 10px;
            }

            table {
                font-size: 13px;
            }
        }

    </style>
</head>
<body>

<div style="margin-bottom: 15px;">
    <a style="text-decoration: none;">
        <button type="button" onclick="window.history.back()">
            Back to Dashboard
        </button>
    </a>
</div>


<h1>Booking History</h1>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Booking ID</th>
            <th>User Name</th>
            <th>Ground</th>
            <th>Booking Date</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$counter}</td>";
                echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['ground_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['booking_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
                $counter++;
            }
        } else {
            echo "<tr><td colspan='6'>No bookings found</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>
