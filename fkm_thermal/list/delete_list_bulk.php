<?php
session_start();
include_once("../../auth_check.php");

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

header('Content-Type: application/json');

/* =============================
   VALIDASI INPUT
============================= */

if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No data selected'
    ]);
    exit;
}

$ids = array_map('intval', $_POST['ids']);

// ❗ FILTER KOSONG
$ids = array_filter($ids);

if (empty($ids)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid data'
    ]);
    exit;
}

$idList = implode(',', $ids);

/* =============================
   DELETE QUERY
============================= */

$query = "DELETE FROM fkm WHERE id_fkm IN ($idList)";
$result = mysqli_query($db, $query);

/* =============================
   HANDLE RESULT
============================= */

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => mysqli_error($db)
    ]);
    exit;
}

if (mysqli_affected_rows($db) > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Selected Data Deleted Successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No data deleted or data not found'
    ]);
}
