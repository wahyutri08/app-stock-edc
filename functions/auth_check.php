<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 403 Forbidden');
    include("errors/404.html");
    exit();
}
require_once 'functions.php';
if (!is_user_active($_SESSION['id'])) {
    logout();
}
