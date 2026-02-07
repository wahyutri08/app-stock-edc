<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

if (isset($_GET["id_return"]) && is_numeric($_GET["id_return"])) {
    $id_return = $_GET["id_return"];
} else {
    header("HTTP/1.1 404 Not Found");
    include("../errors/404.html");
    exit;
}

if (deleteList($id_return) > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
exit;
