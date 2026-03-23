<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

// Hanya boleh method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}


if (!isset($_POST['ids'])) {
    echo json_encode(['status' => 'error', 'message' => 'No data selected']);
    exit;
}

$ids = $_POST['ids'];
$ids = array_map('intval', $ids);
$idList = implode(',', $ids);

$query = "DELETE FROM product_type WHERE id_product IN ($idList)";
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
