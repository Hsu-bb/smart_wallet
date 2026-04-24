<?php
require '../includes/session.php';
session_destroy();
setcookie("user_id", "", time() - 3600, "/");
header("Location: ../views/login.php");
exit();
