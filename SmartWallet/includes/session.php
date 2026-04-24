<?php
session_start();

if (!function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('logout')) {
    function logout()
    {
        session_destroy();
        header("Location: ../views/login.php");
        exit();
    }
}
