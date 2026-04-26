<?php
session_start();
include_once("../../auth_check.php");

if ($_SESSION['role'] !== 'Admin') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/* ================= HELPER ================= */

function normalizeHeader($header)
{
    return strtolower(trim(str_replace([' ', '-'], '_', $header)));
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

    // HEADER
    $headerRaw = $sheet[0];
    $header = array_map('normalizeHeader', $headerRaw);
    $map = array_flip($header);

    $result = [];

    for ($i = 1; $i < count($sheet); $i++) {

        $row = $sheet[$i];
        $error = [];

        $get = function ($key) use ($row, $map) {
            return isset($map[$key]) ? trim($row[$map[$key]]) : '';
        };

        $data = [
            'user_id' => $get('user_id'),
            'tid' => $get('tid'),
            'mid' => $get('mid'),
            'nama_merchant' => $get('nama_merchant'),
            'alamat' => $get('alamat'),
            'status_merchant' => $get('status_merchant'),
        ];

        /* ===== VALIDASI ===== */

        // user_id
        if (!is_numeric($data['user_id'])) {
            $error[] = "User ID tidak valid";
        } else {
            $cek = mysqli_query($db, "SELECT id FROM users WHERE id='{$data['user_id']}'");
            if (mysqli_num_rows($cek) == 0) {
                $error[] = "User tidak ditemukan";
            }
        }

        // wajib isi
        if (empty($data['tid'])) $error[] = "TID is required";
        if (empty($data['mid'])) $error[] = "MID is required";
        if (empty($data['nama_merchant'])) $error[] = "Merchant Name is required";
        if (empty($data['alamat'])) $error[] = "Alamat is required";

        // status merchant
        if (!in_array($data['status_merchant'], ['Active', 'Not Active'])) {
            $error[] = "Merchant status must be Active / Not Active";
        }

        $result[] = [
            'data' => $data,
            'error' => $error,
            'status' => empty($error)
        ];
    }

    $_SESSION['preview'] = $result;

    /* ================= HTML PREVIEW ================= */

    $html = '<div class="table-responsive">';
    $html .= '<table class="table table-bordered table-sm">';
    $html .= '<thead class="thead-dark"><tr>';

    foreach ($headerRaw as $h) {
        $html .= '<th>' . $h . '</th>';
    }

    $html .= '<th>Status</th><th>Error</th></tr></thead><tbody>';

    foreach ($result as $i => $r) {

        $row = $sheet[$i + 1];

        $html .= '<tr>';

        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }

        $status = $r['status']
            ? '<span class="text-success">OK</span>'
            : '<span class="text-danger">ERROR</span>';

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

    if (!$data) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No Preview Data'
        ]);
        exit;
    }

    mysqli_begin_transaction($db);

    try {

        $inserted = 0;
        $duplicate = 0;

        foreach ($data as $row) {

            if (!$row['status']) continue;

            $d = $row['data'];

            // ✅ ESCAPE
            $user_id = mysqli_real_escape_string($db, $d['user_id']);
            $tid     = mysqli_real_escape_string($db, $d['tid']);
            $mid     = mysqli_real_escape_string($db, $d['mid']);
            $nama    = mysqli_real_escape_string($db, $d['nama_merchant']);
            $alamat  = mysqli_real_escape_string($db, $d['alamat']);
            $status  = mysqli_real_escape_string($db, $d['status_merchant']);

            // 🔥 CEK DUPLIKAT (PAKAI YANG SUDAH ESCAPE)
            $cek = mysqli_query($db, "
                SELECT id_fkm FROM fkm 
                WHERE tid = '$tid' AND mid = '$mid'
            ");

            if (mysqli_num_rows($cek) > 0) {
                $duplicate++;
                continue;
            }

            // 🔥 INSERT (PAKAI YANG SUDAH ESCAPE)
            mysqli_query($db, "INSERT INTO fkm (
                user_id, tid, mid, nama_merchant, alamat, status_merchant
            ) VALUES (
                '$user_id',
                '$tid',
                '$mid',
                '$nama',
                '$alamat',
                '$status'
            )");

            if (mysqli_error($db)) {
                throw new Exception(mysqli_error($db));
            }

            $inserted++;
        }

        mysqli_commit($db);

        unset($_SESSION['preview']);

        // 🔥 RESPONSE BERDASARKAN HASIL
        if ($inserted == 0 && $duplicate > 0) {
            echo json_encode([
                'status' => 'duplicate'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'inserted' => $inserted,
                'duplicate' => $duplicate
            ]);
        }
    } catch (Exception $e) {

        mysqli_rollback($db);

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}
