<?php
session_start();
include_once("../auth_check.php");

header('Content-Type: application/json');

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (empty($_POST['stockIds'])) {
    echo json_encode(['status' => 'error', 'message' => 'No Data Selected']);
    exit;
}

$ids = array_map('intval', $_POST['stockIds']);
$idList = implode(',', $ids);

$query = "DELETE FROM detail_list_stock WHERE stock_id IN ($idList)";
mysqli_query($db, $query);

if (mysqli_affected_rows($db) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Selected Data Deleted Successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Delete Failed'
    ]);
}
