<?php
require_once "functions.php";
$user = currentUser();
?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0d6efd;">
    <div class="container">
        <a class="navbar-brand" href="/sports_booking_system/dashboard.php">Sports Booking</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if($user): ?>
                    <?php if($user['role'] == 'customer'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sports_booking_system/customer/view_slots.php">View Slots</a></li>
                        <li class="nav-item"><a class="nav-link" href="/sports_booking_system/customer/booking_history.php">Booking History</a></li>
                    <?php elseif($user['role'] == 'manager'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sports_booking_system/manager/manage_facilities.php">Facilities</a></li>
                        <li class="nav-item"><a class="nav-link" href="/sports_booking_system/manager/view_bookings.php">Bookings</a></li>
                    <?php elseif($user['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/sports_booking_system/admin/manage_users.php">Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="/sports_booking_system/admin/reports.php">Reports</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link text-danger" href="/sports_booking_system/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/sports_booking_system/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/sports_booking_system/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
