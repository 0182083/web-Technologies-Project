<?php

session_start();

$conn = new mysqli("localhost", "root", "", "user_reg");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "errors" => ["server" => "Database error"]]);
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    echo json_encode([
        "success" => false,
        "errors" => ["server" => "Invalid form submission."]
    ]);
    exit;
}

if (isset($_POST['check'])) {
    $field = $_POST['check'];
    $value = trim($_POST['value']);

    if (!in_array($field, ['email', 'username'], true)) {
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE $field = ?"
    );
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->store_result();

    echo json_encode([
        "exists" => $stmt->num_rows > 0
    ]);

    $stmt->close();
    exit;
}


header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false]);
    exit;
}

$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$dob = $_POST['dob'];
$email = trim($_POST['email']);
$username = trim($_POST['username']);
$role = $_POST['role'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

$errors = [];

/* Role validation */
$allowedRoles = ['User', 'Admin', 'Manager'];
if (!in_array($role, $allowedRoles, true)) {
    $errors['role'] = "Invalid user role.";
}

/* Field validation */
if (!preg_match("/^[a-zA-Z]+$/", $first_name)) {
    $errors['first_name'] = "Only letters allowed.";
}

if (!preg_match("/^[a-zA-Z]+$/", $last_name)) {
    $errors['last_name'] = "Only letters allowed.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email address.";
}

if (empty($username)) {
    $errors['username'] = "Username is required.";
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{6,}$/", $password)) {
    $errors['password'] = "Password too weak.";
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = "Passwords do not match.";
}

/* Duplicate check */
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $errors['email'] = "Email or username already exists.";
}
$stmt->close();

/* If errors, return them */
if (!empty($errors)) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

/* Insert user */
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO users (first_name, last_name, dob, email, username, role, password)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssssss", $first_name, $last_name, $dob, $email, $username, $role, $hashed);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => true]);
$conn->close();
