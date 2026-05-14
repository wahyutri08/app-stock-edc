<?php
session_start();
include_once("../auth_check.php");
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/* ================= HELPER ================= */

function formatTanggal($value)
{
    if (is_numeric($value)) {
        return date('Y-m-d', Date::excelToTimestamp($value));
    }
    return $value;
}

function normalizeHeader($header)
{
    return strtolower(trim(str_replace([' ', '-'], '_', $header)));
}

/* ================= LOAD MASTER ================= */

$productMap = [];
$res = mysqli_query($db, "SELECT id_product, name_product FROM product_type");
while ($p = mysqli_fetch_assoc($res)) {
    $productMap[normalizeHeader($p['name_product'])] = $p['id_product'];
}

$colorMap = [];
$res = mysqli_query($db, "SELECT id_color, name_color FROM color_type");
while ($c = mysqli_fetch_assoc($res)) {
    $colorMap[normalizeHeader($c['name_color'])] = $c['id_color'];
}

/* ================= PREVIEW ================= */

if (isset($_POST['preview'])) {

    if (!isset($_FILES['file'])) {
        echo json_encode(['status' => 'error', 'message' => 'File tidak ada']);
        exit;
    }

    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet()->toArray();

    // NORMALISASI HEADER
    $headerRaw = $sheet[0];
    $header = array_map('normalizeHeader', $headerRaw);
    $map = array_flip($header);

    $result = [];
    $snTracker = [
        'sn_edc' => [],
        'sn_simcard' => [],
        'sn_samcard1' => [],
        'sn_samcard2' => [],
        'sn_samcard3' => []
    ];
    $snErrors = [];

    for ($i = 1; $i < count($sheet); $i++) {

        $row = $sheet[$i];
        $error = [];

        // ambil data fleksibel
        $get = function ($key) use ($row, $map) {
            return isset($map[$key]) ? $row[$map[$key]] : '';
        };

        $product_name = normalizeHeader($get('product_name'));
        $color_name   = normalizeHeader($get('edc_color'));

        $data = [
            'user_id' => $get('user_id'),
            'sn_edc' => $get('sn_edc'),
            'requirements' => $get('requirements'),
            'product_id' => $productMap[$product_name] ?? null,
            'color_id' => $colorMap[$color_name] ?? null,
            'sn_simcard' => $get('sn_simcard'),
            'sn_samcard1' => $get('sn_samcard1'),
            'sn_samcard2' => $get('sn_samcard2'),
            'sn_samcard3' => $get('sn_samcard3'),
            'date_pickup' => formatTanggal($get('date_pickup')),
            'status_edc' => $get('status_edc'),
            'status_condition' => $get('status_condition'),
        ];

        /* ===== VALIDASI ===== */

        if (!is_numeric($data['user_id'])) {
            $error[] = "User ID tidak valid";
        } else {
            $cek = mysqli_query($db, "SELECT id FROM users WHERE id='{$data['user_id']}'");
            if (mysqli_num_rows($cek) == 0) $error[] = "User tidak ditemukan";
        }

        if (!in_array($data['requirements'], ['STOCK', 'RETURN']))
            $error[] = "Requirements salah";

        if (!in_array($data['status_edc'], ['Not yet used', 'Used', 'None']))
            $error[] = "Status EDC salah";

        if (!$data['product_id']) $error[] = "Produk tidak ditemukan";
        if (!$data['color_id']) $error[] = "Warna tidak ditemukan";

        // VALIDASI SN
        $snFields = ['sn_edc', 'sn_simcard', 'sn_samcard1', 'sn_samcard2', 'sn_samcard3'];

        foreach ($snFields as $field) {

            $value = trim($data[$field]);

            if ($field == 'sn_edc' && empty($value)) {
                $error[] = "SN EDC kosong";
                continue;
            }

            if ($field != 'sn_edc' && empty($value)) continue;

            // duplicate file
            if (in_array($value, $snTracker[$field])) {
                $error[] = strtoupper($field) . " duplikat di file";
                $snErrors[$field][] = $value;
            } else {
                $snTracker[$field][] = $value;
            }

            // duplicate DB
            $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE $field='$value'");
            if (mysqli_num_rows($cek) > 0) {
                $error[] = strtoupper($field) . " sudah ada di database";
                $snErrors[$field][] = $value;
            }
        }

        $result[] = [
            'data' => $data,
            'error' => $error,
            'status' => empty($error)
        ];
    }

    $_SESSION['preview'] = $result;
    $_SESSION['sn_errors'] = $snErrors;

    /* ================= BUILD HTML PREVIEW ================= */

    $html = '<div class="table-responsive">';
    $html .= '<table class="table table-bordered table-sm">';
    $html .= '<thead class="thead-dark"><tr>';

    // HEADER DINAMIS (SEMUA KOLOM)
    foreach ($headerRaw as $h) {
        $html .= '<th>' . $h . '</th>';
    }
    $html .= '<th>Status</th><th>Error</th></tr></thead><tbody>';

    // DATA
    foreach ($result as $i => $r) {

        $row = $sheet[$i + 1];

        $html .= '<tr>';

        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }

        $status = $r['status'] ? '<span class="text-success">OK</span>' : '<span class="text-danger">ERROR</span>';

        $html .= '<td>' . $status . '</td>';
        $html .= '<td>' . implode('<br>', $r['error']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';

    echo json_encode([
        'status' => 'success',
        'html' => $html
    ]);
    exit;
}

/* ================= IMPORT ================= */

if (isset($_POST['import'])) {

    $data = $_SESSION['preview'] ?? [];
    $snErrors = $_SESSION['sn_errors'] ?? [];

    $invalid = 0;
    foreach ($data as $row) {
        if (!$row['status']) $invalid++;
    }

    if ($invalid > 0) {
        echo json_encode([
            'status' => 'failed',
            'sn_errors' => $snErrors
        ]);
        exit;
    }

    mysqli_begin_transaction($db);

    try {

        foreach ($data as $row) {

            $d = $row['data'];

            mysqli_query($db, "INSERT INTO stock (
                user_id, sn_edc, requirements, id_product_name, id_edc_color,
                sn_simcard, sn_samcard1, sn_samcard2, sn_samcard3,
                date_pickup, status_edc, status_condition, created_at
            ) VALUES (
                '{$d['user_id']}',
                '{$d['sn_edc']}',
                '{$d['requirements']}',
                '{$d['product_id']}',
                '{$d['color_id']}',
                '{$d['sn_simcard']}',
                '{$d['sn_samcard1']}',
                '{$d['sn_samcard2']}',
                '{$d['sn_samcard3']}',
                '{$d['date_pickup']}',
                '{$d['status_edc']}',
                '{$d['status_condition']}',
                NOW()
            )");

            if (mysqli_error($db)) {
                throw new Exception(mysqli_error($db));
            }
        }

        mysqli_commit($db);

        unset($_SESSION['preview']);
        unset($_SESSION['sn_errors']);

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {

        mysqli_rollback($db);

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}
