<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

    <!-- You can move this CSS into style.css if you want -->
    <style>
        * {
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
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
            max-width: 460px;   /* smaller than before */
            padding: 22px 30px;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        }



        /* Headline */
        .box1 h1 {
            text-align: center;
            margin-bottom: 16px;
            color: #333;
            font-size: 24px;
        }

        label h3 {
            margin: 8px 0 4px;
            color: #444;
            font-size: 13px;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 8px 10px;
            font-size: 13px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border 0.3s, box-shadow 0.3s;
        }

        input:focus,
        select:focus {
            border-color: #667eea;
            box-shadow: 0 0 4px rgba(102, 126, 234, 0.4);
        }

        select {
            background-color: #fff;
        }

        /* Submit button */
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

        /* Small screens */
        @media (max-width: 768px) {
            .box1 {
                width: 90%;
                padding: 20px;
            }
        }

        /* Floating label container */
        .field {
            position: relative;
            margin-bottom: 14px;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 12px 10px;
            font-size: 13px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            background: none;
        }

        /* Floating label */
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

        /* Float on focus or input */
        .field input:focus + label,
        .field input:not(:placeholder-shown) + label,
        .field select:focus + label,
        .field select:focus + label,
        .field select:valid + label {
            top: -6px;
            font-size: 11px;
            color: #667eea;
        }

        /* Login link */
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

        /* Error styles */
        .error-text {
            color: #e53e3e;
            font-size: 11px;
            margin-top: 4px;
            display: none;
        }

        .field.error input,
        .field.error select {
            border-color: #e53e3e;
        }

        .field.error label {
            color: #e53e3e;
        }
    </style>

</head>

<body>
    <div class="main">
        <div class="box1">
            <!-- Sign Up Headline -->
            <h1>Sign Up</h1>

            <form id="signupForm" action="process.php" method="post" >
                <input type="hidden" name="csrf_token"
                    value="<?= $_SESSION['csrf_token'] ?>">


                <div class="field">
                    <input type="text" id="first_name" name="first_name" placeholder=" " />
                    <label for="first_name">First Name</label>
                    <div class="error-text" id="firstNameError"></div>
                </div>

                <div class="field">
                    <input type="text" id="last_name" name="last_name" placeholder=" " />
                    <label for="last_name">Last Name</label>
                    <div class="error-text" id="lastNameError"></div>
                </div>

                <div class="field">
                    <input type="date" id="dob" name="dob" placeholder=" " />
                    <label for="dob">Date of Birth</label>
                </div>

                <div class="field">
                    <input type="text" id="email" name="email" placeholder=" " />
                    <label for="email">Email</label>
                    <div class="error-text" id="emailError"></div>
                </div>

                <div class="field">
                    <input type="text" id="username" name="username" placeholder=" " />
                    <label for="username">Username</label>
                    <div class="error-text" id="usernameError"></div>
                </div>

                <div class="field">
                    <select id="role" name="role" value="select">
                        <option value="" disabled selected hidden></option>
                        <option value="User">User</option>
                        <option value="Manager">Manager</option>
                        <option value="Admin">Admin</option>
                    </select>
                    <label for="role">User Type</label>
                    <div class="error-text" id="roleError"></div>
                </div>

                <div class="field">
                    <input type="password" id="password" name="password" placeholder=" " />
                    <label for="password">Password</label>
                    <div class="error-text" id="passwordError"></div>
                </div>

                <div class="field">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder=" " />
                    <label for="confirm_password">Confirm Password</label>
                    <div class="error-text" id="confirmPasswordError"></div>
                </div>

                <input type="submit" class="submit-btn" value="Create Account">

                <div class="login-link">
                    Already have an account?
                    <a href="login.php">Login</a>
                </div>

            </form>

        </div>
    </div>

    <script>
        const form = document.getElementById("signupForm");

        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            clearErrors();

            // Run all validators locally first
            const errors = {};

            for (const field in validators) {
                const input = document.getElementById(field);
                if (!input) continue;

                const result = validators[field](input.value);
                if (result !== true) {
                    errors[field] = result;
                }
            }

            // Also validate select role here if needed
            const role = document.getElementById("role").value;
            if (role === "") {
                errors.role = "Please select a user type.";
            }

            if (Object.keys(errors).length > 0) {
                showErrors(errors);
                return; // stop form submission
            }

            const formData = new FormData(form);

            const response = await fetch("process.php", {
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = "login.php?registered=1";
            } else {
                showErrors(data.errors);
            }
        });

        function showErrors(errors) {
            for (const field in errors) {
                const input = document.getElementById(field);
                if (!input) continue;

                const fieldDiv = input.parentElement;
                fieldDiv.classList.add("error");

                const errorDiv = fieldDiv.querySelector(".error-text");
                errorDiv.innerText = errors[field];
                errorDiv.style.display = "block";
            }
        }

        // Updated clearErrors to accept optional field argument
        function clearErrors(field = null) {
            if (field) {
                const input = document.getElementById(field);
                if (input) {
                    const fieldDiv = input.parentElement;
                    fieldDiv.classList.remove("error");
                    const errorDiv = fieldDiv.querySelector(".error-text");
                    if (errorDiv) {
                        errorDiv.innerText = "";
                        errorDiv.style.display = "none";
                    }
                }
            } else {
                // Clear all errors
                document.querySelectorAll(".field").forEach(field => {
                    field.classList.remove("error");
                    const error = field.querySelector(".error-text");
                    if (error) {
                        error.innerText = "";
                        error.style.display = "none";
                    }
                });
            }
        }

        const validators = {
            first_name: value => /^[a-zA-Z]+$/.test(value) || "Only letters allowed.",
            last_name: value => /^[a-zA-Z]+$/.test(value) || "Only letters allowed.",
            email: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || "Invalid email.",
            username: value => value.trim() !== "" || "Username is required.",
            password: value =>
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{6,}$/.test(value) || "Weak password."
        };

        // Real-time validation on blur, only clear error for that field
        Object.keys(validators).forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;

            input.addEventListener("blur", () => {
                const result = validators[id](input.value);
                if (result !== true) {
                    showErrors({ [id]: result });
                } else {
                    clearErrors(id);
                }
            });
        });

        function debounce(fn, delay = 500) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        function availabilityCheck(field, validator) {
            const input = document.getElementById(field);

            const runCheck = () => {
                // Only check if field passes local validation
                const valid = validator(input.value);
                if (valid !== true) return; // stop if format invalid

                const data = new FormData();
                data.append("check", field);
                data.append("value", input.value);
                data.append("csrf_token",
                    document.querySelector('[name="csrf_token"]').value
                );

                const xhr = new XMLHttpRequest();
                xhr.open("POST", "process.php", true);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

                xhr.onload = () => {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.exists) {
                            showErrors({ [field]: `${field} already exists.` });
                        } else {
                            clearErrors(field); // remove error if available
                        }
                    } catch (e) {
                        console.error("Invalid JSON from server");
                    }
                };

                xhr.send(data);
            };

            // Debounce function
            const debounced = debounce(runCheck, 500);

            // Check while typing
            input.addEventListener("input", debounced);

            // Also check immediately on blur
            input.addEventListener("blur", runCheck);
        }

        // Initialize availability checks on email and username
        availabilityCheck("email", validators.email);
        availabilityCheck("username", validators.username);
    </script>



</body>
</html>
