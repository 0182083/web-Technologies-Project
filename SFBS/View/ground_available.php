<?php
// Database connection
require_once 'db_connect.php'; 

// Fetch facilities from the database
$sql = "SELECT * FROM facilities";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ground Availability</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
        }
        .no-data {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Available Facilities</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ground Name</th>
                    <th>Location</th>
                    <th>Facility Type</th>
                    <th>Available Duration</th>
                    <th>Fees</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $counter++; ?></td>
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
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No facilities available at the moment.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
