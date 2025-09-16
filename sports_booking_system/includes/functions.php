<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['id']);
}

/**
 * Redirect to login page if not logged in
 */
function checkLogin() {
    if(!isLoggedIn()){
        redirect("auth/login.php"); // Relative path to login
        exit();
    }
}

/**
 * Get current logged-in user info
 */
function currentUser() {
    if(isLoggedIn()) {
        return [
            'user_id'  => $_SESSION['id'],       // note: user_id for dashboard.php
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['role']
        ];
    }
    return null;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Role-specific checks
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isManager() {
    return isLoggedIn() && $_SESSION['role'] === 'manager';
}

function isCustomer() {
    return isLoggedIn() && $_SESSION['role'] === 'customer';
}
?>
