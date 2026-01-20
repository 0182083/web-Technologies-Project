<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'Admin') {
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'Manager') {
        header('Location: manager_dashboard.php');
        exit;
    } else {
        header('Location: user_dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Facility Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-image: url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            color: white;
            position: relative;
        }

        header {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(59, 59, 59, 1);
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            z-index: 10;
        }

        h1 {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 4rem;
            letter-spacing: -2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            z-index: 5;
            background: rgba(0, 0, 0, 0.5); 
            border-radius: 10px; 
            padding: 10px;
            white-space: nowrap; 
        }

        .content {
            position: absolute;
            top: 310px; 
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            max-width: 600px;
            z-index: 5;
            background: rgba(0, 0, 0, 0.5); 
            border-radius: 10px; 
            padding: 20px; 
            backdrop-filter: blur(5px); /* Added blur effect */
        }

        .advertising-text {
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.6;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            font-size: 1rem;
            text-decoration: none;
            color: white;
            background: rgba(0, 123, 255, 1);
            border: 2px solid rgba(0, 123, 255, 1);
            border-radius: 5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .btn:hover {
            background: rgba(0, 123, 255, 1);
            border-color: rgba(0, 123, 255, 1);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
                top: 30px;
            }
            .content {
                top: 180px; /* Adjust for mobile */
            }
            .advertising-text {
                font-size: 1rem;
            }
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            header {
                font-size: 1.2rem;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <header>Sportsbook</header>
    <h1>Welcome to Sports Facility Booking</h1>
    <div class="content">
        <div class="advertising-text">
            Book your favorite sports grounds effortlessly! Whether it's football, tennis, cricket, or badminton, we have the perfect facilities for you. Join thousands of athletes and enjoy seamless booking with competitive rates and top-notch amenities.
        </div>
        <div class="buttons">
            <a href="login.php" class="btn">Login</a>
            <a href="signup.php" class="btn">Sign Up</a>
        </div>
    </div>
</body>
</html>