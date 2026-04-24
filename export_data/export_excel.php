<?php
session_start();
require '../vendor/autoload.php';
require_once("../auth_check.php");

// 🔐 CEK LOGIN
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

// ❌ JANGAN pakai validasi AJAX di export
// karena ini file download, bukan API

// ✅ WAJIB POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/* =============================
   AMBIL FILTER (SAMA PERSIS)
============================= */

$where = [];

$keyword     = mysqli_real_escape_string($db, $_POST['search'] ?? '');
$date_pickup = $_POST['date_pickup'] ?? '';
$date_used   = $_POST['date_used'] ?? '';
$date_sendto_ho = $_POST['date_sendto_ho'] ?? '';
$user_id     = $_POST['user_id'] ?? 'all';
$requirements  = $_POST['requirements'] ?? 'all';
$id_product  = $_POST['id_product_name'] ?? [];
$id_member   = $_POST['id_member_bank'] ?? 'all';
$work_type   = $_POST['work_type'] ?? 'all';
$status_edc  = $_POST['status_edc'] ?? [];
$status_condition  = $_POST['status_condition'] ?? 'all';

/* =============================
   FILTER (COPY DARI AJAX)
============================= */

if ($date_pickup != '')
    $where[] = "stock.date_pickup = '$date_pickup'";

if ($date_sendto_ho != '')
    $where[] = "stock.date_sendto_ho = '$date_sendto_ho'";

if ($date_used != '')
    $where[] = "detail_list_stock.date_used = '$date_used'";

if ($user_id != 'all')
    $where[] = "stock.user_id = '$user_id'";

if (!empty($id_product) && !in_array('all', $id_product)) {
    $ids = array_map('intval', $id_product);
    $where[] = "stock.id_product_name IN (" . implode(',', $ids) . ")";
}

if ($requirements != 'all')
    $where[] = "stock.requirements = '$requirements'";

if ($status_condition != 'all')
    $where[] = "stock.status_condition = '$status_condition'";

if ($id_member != 'all')
    $where[] = "detail_list_stock.id_member_bank = '$id_member'";

if ($work_type != 'all')
    $where[] = "detail_list_stock.work_type = '$work_type'";

if (!empty($status_edc)) {
    $statuses = array_map(function ($val) use ($db) {
        return "'" . mysqli_real_escape_string($db, $val) . "'";
    }, $status_edc);

    $where[] = "stock.status_edc IN (" . implode(',', $statuses) . ")";
}

if ($keyword != '') {
    $where[] = "(
        detail_list_stock.tid LIKE '%$keyword%' OR
        detail_list_stock.mid LIKE '%$keyword%' OR
        detail_list_stock.merchant_name LIKE '%$keyword%' OR
        stock.sn_edc LIKE '%$keyword%' OR
        stock.sn_simcard LIKE '%$keyword%' OR
        stock.sn_samcard1 LIKE '%$keyword%' OR
        stock.sn_samcard2 LIKE '%$keyword%' OR
        stock.sn_samcard3 LIKE '%$keyword%'
    )";
}

$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

/* =============================
   QUERY
============================= */

$query = "SELECT
    stock.*,
    users.name,
    product_type.name_product,
    color_type.name_color,
    member_bank.name_member,
    detail_list_stock.*
FROM stock
LEFT JOIN users ON stock.user_id = users.id
LEFT JOIN product_type ON stock.id_product_name = product_type.id_product
LEFT JOIN color_type ON stock.id_edc_color = color_type.id_color
LEFT JOIN detail_list_stock ON stock.id_stock = detail_list_stock.stock_id
LEFT JOIN member_bank ON detail_list_stock.id_member_bank = member_bank.id_member
$whereSQL
ORDER BY stock.id_stock DESC";

$result = mysqli_query($db, $query);

/* =============================
   EXCEL
============================= */

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = [
    'Name',
    'Requirements',
    'Product',
    'SN EDC',
    'SIM',
    'SAM1',
    'SAM2',
    'SAM3',
    'TID',
    'MID',
    'Merchant',
    'Member Bank',
    'Work Type',
    'Date Pickup',
    'Date Used',
    'Date Send HO',
    'Status',
    'Condition'
];

$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $col++;
}

$rowNum = 2;

while ($row = mysqli_fetch_assoc($result)) {

    $sheet->setCellValue('A' . $rowNum, $row['name']);
    $sheet->setCellValue('B' . $rowNum, $row['requirements']);
    $sheet->setCellValue('C' . $rowNum, $row['name_product'] . ' ' . $row['name_color']);
    $sheet->setCellValue('D' . $rowNum, $row['sn_edc']);
    $sheet->setCellValue('E' . $rowNum, $row['sn_simcard']);
    $sheet->setCellValue('F' . $rowNum, $row['sn_samcard1']);
    $sheet->setCellValue('G' . $rowNum, $row['sn_samcard2']);
    $sheet->setCellValue('H' . $rowNum, $row['sn_samcard3']);
    $sheet->setCellValue('I' . $rowNum, $row['tid']);
    $sheet->setCellValue('J' . $rowNum, $row['mid']);
    $sheet->setCellValue('K' . $rowNum, $row['merchant_name']);
    $sheet->setCellValue('L' . $rowNum, $row['name_member']);
    $sheet->setCellValue('M' . $rowNum, $row['work_type']);
    $sheet->setCellValue('N' . $rowNum, $row['date_pickup']);
    $sheet->setCellValue('O' . $rowNum, $row['date_used']);
    $sheet->setCellValue('P' . $rowNum, $row['date_sendto_ho']);
    $sheet->setCellValue('Q' . $rowNum, $row['status_edc']);
    $sheet->setCellValue('R' . $rowNum, $row['status_condition']);

    $rowNum++;
}

/* =============================
   DOWNLOAD
============================= */

$filename = "export_filter_" . date('Ymd') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
