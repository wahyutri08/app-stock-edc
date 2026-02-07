<?php
session_start();
include_once("../auth_check.php");

header('Content-Type: application/json');

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (empty($_POST['idStatus']) || empty($_POST['status'])) {
    echo json_encode(['status' => 'error', 'message' => 'No Data Selected']);
    exit;
}

$ids = array_map('intval', $_POST['idStatus']);
$idList = implode(',', $ids);

$status = mysqli_real_escape_string($db, $_POST['status']);

$query = "UPDATE return_edc 
          SET status1 = '$status'
          WHERE id_return IN ($idList)";

mysqli_query($db, $query);

if (mysqli_affected_rows($db) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Status EDC Successfully Updated'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No Data Changed'
    ]);
}
exit;
