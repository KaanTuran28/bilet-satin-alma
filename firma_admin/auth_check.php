<?php
require_once '../config/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'firma_admin') {
    header("Location: ../public/login.php");
    exit();
}
if (!isset($_SESSION['company_id'])) {
    header("Location: ../public/logout.php");
    exit();
}
?>