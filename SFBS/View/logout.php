<?php
require_once "../includes/functions.php";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = [];
session_destroy();

// Redirect to login page
redirect("login.php");
