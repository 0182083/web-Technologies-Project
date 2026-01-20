<?php
session_start();
require_once 'db_connect.php';

$msg = "";

// Show registration success message
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $msg = '<div class="success-message">
                Registration successful! You can now log in using your credentials.
            </div>';
}

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, strtolower(trim($_POST['username'])));
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username ='$username'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $count = mysqli_num_rows($result);

    if ($count == 1) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'Admin') {
                header('Location: admin_dashboard.php');
                exit;
            } elseif ($row['role'] === 'User') {
                header('Location: user_dashboard.php');
                exit;
            } elseif ($row['role'] === 'Manager') {
                header('Location: Manager_dashboard.php');
                exit;
            } else {
                $msg = '<div class="alert alert-danger">
                        <strong>Error!</strong> Invalid role assigned to your account.
                    </div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">
                    <strong>Unsuccessful!</strong> Incorrect password.
                </div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">
                <strong>Unsuccessful!</strong> User not found.
            </div>';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .box1 {
            background: #ffffff;
            width: 75%;
            max-width: 460px;
            padding: 22px 30px;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        }
        .box1 h1 {
            text-align: center;
            margin-bottom: 16px;
            color: #333;
            font-size: 24px;
        }
        .field {
            position: relative;
            margin-bottom: 14px;
        }
        .field input {
            width: 100%;
            padding: 12px 10px;
            font-size: 13px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            background: none;
        }
        .field label {
            position: absolute;
            top: 50%;
            left: 10px;
            color: #777;
            font-size: 13px;
            background: #fff;
            padding: 0 5px;
            transform: translateY(-50%);
            pointer-events: none;
            transition: 0.3s ease;
        }
        .field input:focus + label,
        .field input:not(:placeholder-shown) + label {
            top: -6px;
            font-size: 11px;
            color: #667eea;
        }
        .submit-btn {
            margin-top: 18px;
            width: 100%;
            padding: 10px;
            font-size: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .submit-btn:hover {
            background: #5a67d8;
        }
        .alert, .success-message {
            padding: 10px 15px;
            margin-bottom: 14px;
            border-radius: 5px;
            font-size: 13px;
            text-align: center;
        }
        .alert-danger {
            color: #e53e3e;
            background-color: #ffe5e5;
        }
        .success-message {
            color: #2f855a;
            background-color: #e6ffed;
        }
        .login-link {
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="main">
        <div class="box1">
            <h1>Login</h1>

            <?php echo $msg; ?>

            <form id="loginForm" method="post">
                <div class="field">
                    <input type="text" id="username" name="username" placeholder=" " required>
                    <label for="username">Username</label>
                </div>

                <div class="field">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Password</label>
                </div>

                <input type="submit" class="submit-btn" name="submit" value="Log in">

                <div class="login-link">
                    Don't have an account? <a href="signup.php">Sign Up</a>
                </div>
            </form>
        </div>
    </div>
</body>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const successMsg = document.querySelector(".success-message");
    if(successMsg){
        successMsg.style.opacity = 0;
        successMsg.style.transition = "opacity 0.5s ease-in-out";
        successMsg.style.display = "block";

        // Fade in
        setTimeout(() => {
            successMsg.style.opacity = 1;
        }, 100);

        // Fade out after 3 seconds
        setTimeout(() => {
            successMsg.style.opacity = 0;
            // Remove from DOM after fade out
            setTimeout(() => successMsg.remove(), 500);
        }, 3100);
    }
});
</script>


</html>