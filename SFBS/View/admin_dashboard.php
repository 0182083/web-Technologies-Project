<?php
session_start();
require_once 'db_connect.php';

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: unauthorized.php");
    exit;
}

// Fetch facilities
$sql = "SELECT * FROM facilities";
$result = $conn->query($sql);

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        $updateMsg = "Please provide a valid Facility ID.";
    } else {
        $existing = $conn->query("SELECT * FROM facilities WHERE id=$id")->fetch_assoc();
        if ($existing) {
            $ground_name = $_POST['ground_name'] ?: $existing['ground_name'];
            $ground_location = $_POST['ground_location'] ?: $existing['ground_location'];
            $facility_type = $_POST['facility_type'] ?: $existing['facility_type'];
            $available_duration = $_POST['available_duration'] ?: $existing['available_duration'];
            $fees = $_POST['fees'] ?: $existing['fees'];

            // Handle image
            $ground_picture = $existing['ground_picture'];
            if (!empty($_FILES['ground_picture']['name'])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES['ground_picture']['name']);
                if (move_uploaded_file($_FILES['ground_picture']['tmp_name'], $target_file)) {
                    $ground_picture = $target_file;
                }
            }

            $conn->query("UPDATE facilities SET 
                ground_name='$ground_name', 
                ground_location='$ground_location',
                facility_type='$facility_type',
                available_duration='$available_duration',
                fees='$fees',
                ground_picture='$ground_picture'
                WHERE id=$id");

            $updateMsg = "Facility updated successfully!";
        } else {
            $updateMsg = "Facility ID not found.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Inter', sans-serif;
    margin:0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background-color: #2c3e50;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 30px 20px;
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
}
.sidebar h2 {
    text-align:center;
    margin-bottom:30px;
    font-weight:600;
}
.sidebar a {
    display:flex;
    align-items:center;
    text-decoration:none;
    color:#ecf0f1;
    padding:12px 15px;
    margin-bottom:10px;
    border-radius:8px;
    transition:0.2s;
}
.sidebar a:hover {
    background-color:#34495e;
}
.sidebar a .icon {
    margin-right:10px;
}
.sidebar .logout {
    margin-top:auto;
    background-color:#e74c3c;
    text-align:center;
}

/* Main content */
.main-content {
    flex:1;
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:15px;
}

/* Card containers */
.container {
    background-color:#fff;
    padding:18px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.12);
}
.container h3 {
    margin-bottom:20px;
    color:#333;
}

/* Table styling */



table {
    width:100%;
    border-collapse: collapse;
}
table th, table td {
    padding:12px;
    text-align:left;
    border-bottom:1px solid #ddd;
}
table th {
    background:#667eea;
    color:white;
    border-radius:8px;
}
table tr:hover {
    background:#f0f0f0;
}
table img {
    width:100px;
    height:70px;
    object-fit:cover;
    border-radius:6px;
}

/* Form styling */
form {
    display:flex;
    flex-direction:column;
    gap:15px;
}
label {
    font-weight:600;
}
input, select, button {
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    outline:none;
    font-size:14px;
}
input:focus, select:focus {
    border-color:#667eea;
    box-shadow:0 0 5px rgba(102,126,234,0.4);
}
button {
    background:#667eea;
    color:white;
    border:none;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover {
    background:#5a67d8;
}

.form-group {
    position: relative;
    margin-bottom: 20px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
    background: transparent;
    font-size: 14px;
}

.form-group label {
    position: absolute;
    top: 12px;
    left: 12px;
    color: #999;
    font-size: 14px;
    pointer-events: none;
    transition: 0.2s ease all;
}

.form-group input:focus + label,
.form-group input:not(:placeholder-shown) + label,
.form-group select:focus + label,
.form-group select:not([value=""]) + label {
    top: -10px;
    left: 10px;
    font-size: 12px;
    color: #667eea;
    background: #fff;
    padding: 0 4px;
}

.form-group select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}


/* Success message */
.alert-success {
    padding:12px 15px;
    border-radius:8px;
    background:#38a169;
    color:white;
    font-weight:500;
    animation: fadeIn 1s ease forwards;
}

/* Fade in animation */
@keyframes fadeIn {
    from {opacity:0; transform:translateY(-5px);}
    to {opacity:1; transform:translateY(0);}
}

/* Responsive */
@media(max-width:900px){
    body{flex-direction:column;}
    .sidebar{width:100%; border-radius:0 0 20px 20px; flex-direction:row; justify-content:space-around;}
    .main-content{padding:15px;}
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Dashboard</h2>
    <!-- Basic Icons -->
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <a href="user_information.php"><span class="icon"><i class='bx  bx-group'></i> </span>Manage Users</a>
    <a href="add_facilities.php"><span class="icon"><i class='bx  bx-list-plus'></i> </span>Add Facility</a>
    <a href="booking_history.php"><span class="icon"><i class='bx  bx-book-library'></i> </span>Booking History</a>
    <a href="logout.php" class="logout"><span class="icon"><i class='bx  bx-arrow-out-left-square-half'></i> </span>Logout</a>
</div>

<div class="main-content">
    <!-- Facility Table -->
    <div class="container">
        <h3>Ground Availability</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ground Name</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Fees</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']); ?></td>
                            <td><?= htmlspecialchars($row['ground_name']); ?></td>
                            <td><?= htmlspecialchars($row['ground_location']); ?></td>
                            <td><?= htmlspecialchars($row['facility_type']); ?></td>
                            <td><?= htmlspecialchars($row['available_duration']); ?></td>
                            <td><?= htmlspecialchars(number_format($row['fees'], 2)) ?> Tk</td>
                            <td><img src="<?= htmlspecialchars($row['ground_picture']); ?>" alt="Ground"></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No facilities available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Update Form -->
    <div class="container">
        <h3>Update Facility</h3>
        <?php if(!empty($updateMsg)): ?>
            <div class="alert-success"><?= htmlspecialchars($updateMsg); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <input type="number" name="id" id="id" placeholder=" " required>
                <label for="id">Facility ID</label>
            </div>

            <div class="form-group">
                <input type="text" name="ground_name" id="ground_name" placeholder=" ">
                <label for="ground_name">Ground Name</label>
            </div>

            <div class="form-group">
                <input type="text" name="ground_location" id="ground_location" placeholder=" ">
                <label for="ground_location">Ground Location</label>
            </div>

            <div class="form-group">
                <select name="facility_type" id="facility_type" placeholder=" ">
                    <option value="" disabled selected hidden></option>
                    <option value="Football Ground">Football Ground</option>
                    <option value="Cricket Ground">Cricket Ground</option>
                    <option value="Badminton Court">Badminton Court</option>
                    <option value="Basketball Court">Basketball Court</option>
                    <option value="Tennis Court">Tennis Court</option>
                </select>
                <label for="facility_type">Facility Type</label>
            </div>

            <div class="form-group">
                <input type="text" name="available_duration" id="available_duration" placeholder=" ">
                <label for="available_duration">Available Duration</label>
            </div>

            <div class="form-group">
                <input type="text" name="fees" id="fees" placeholder=" ">
                <label for="fees">Fees (Tk)</label>
            </div>

            <div class="form-group">
                <input type="file" name="ground_picture" id="ground_picture" accept="image/*">
                <label for="ground_picture">Ground Image</label>
            </div>

            <button type="submit" name="update">Update Facility</button>
        </form>
    </div>

</div>

</body>
</html>
