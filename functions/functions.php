<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 404 Forbidden');
    include("errors/404.html");
    exit();
}
$db = mysqli_connect("localhost", "root", "", "dev-stock-edc");
date_default_timezone_set('Asia/Jakarta');

function query($query)
{
    global $db;
    $result = mysqli_query($db, $query);
    $rows = [];

    // Periksa apakah query berhasil dieksekusi
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            // Loop melalui hasil query
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row; // Menambahkan baris hasil ke dalam array $rows
            }
        }
    } else {
        echo "Error: " . mysqli_error($db);
    }

    return $rows;
}

function addStock($data)
{
    global $db;

    $user_id = $_SESSION["id"];
    $date = date('Y-m-d');
    $created_at = date('Y-m-d');

    // TRIM + ESCAPE (BENAR)
    $sn_edc      = trim(mysqli_real_escape_string($db, $data["sn_edc"]));
    $sn_simcard  = trim(mysqli_real_escape_string($db, $data["sn_simcard"]));
    $sn_samcard1 = trim(mysqli_real_escape_string($db, $data["sn_samcard1"]));
    $sn_samcard2 = trim(mysqli_real_escape_string($db, $data["sn_samcard2"]));
    $sn_samcard3 = trim(mysqli_real_escape_string($db, $data["sn_samcard3"]));
    $status_edc  = trim(mysqli_real_escape_string($db, $data["status_edc"]));

    // 1️⃣ SN EDC
    if ($sn_edc !== '') {
        $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_edc = '$sn_edc' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -1;
        }
    }

    // 2️⃣ SN SIMCARD (OPSIONAL)
    if ($sn_simcard !== '') {
        $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_simcard = '$sn_simcard' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -2;
        }
    }

    // 3️⃣ SN SAMCARD 1
    if ($sn_samcard1 !== '') {
        $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_samcard1 = '$sn_samcard1' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -3;
        }
    }

    // 4️⃣ SN SAMCARD 2
    if ($sn_samcard2 !== '') {
        $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_samcard2 = '$sn_samcard2' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -4;
        }
    }

    // 5️⃣ SN SAMCARD 3
    if ($sn_samcard3 !== '') {
        $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_samcard3 = '$sn_samcard3' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -5;
        }
    }

    /* =======================
       INSERT DATA
       ======================= */

    $query = "
        INSERT INTO stock
        (user_id, sn_edc, sn_simcard, sn_samcard1, sn_samcard2, sn_samcard3, date, status_edc, created_at)
        VALUES (
            '$user_id',
            " . ($sn_edc  === '' ? "NULL" : "'$sn_edc'") . ",
            " . ($sn_simcard  === '' ? "NULL" : "'$sn_simcard'") . ",
            " . ($sn_samcard1 === '' ? "NULL" : "'$sn_samcard1'") . ",
            " . ($sn_samcard2 === '' ? "NULL" : "'$sn_samcard2'") . ",
            " . ($sn_samcard3 === '' ? "NULL" : "'$sn_samcard3'") . ",
            '$date',
            '$status_edc',
            '$created_at'
        )
    ";

    $insert = mysqli_query($db, $query);

    if (!$insert) {
        return 0; // insert gagal
    }

    return mysqli_affected_rows($db);
}

function editStock($data)
{
    global $db;

    $id_stock = (int)$data['id_stock'];
    $user_id  = (int)$data['user_id'];
    $status_edc   = mysqli_real_escape_string($db, $data['status_edc']);
    $updated_at = date('Y-m-d H:i:s');

    $sn_edc      = trim(mysqli_real_escape_string($db, $data['sn_edc']));
    $sn_simcard  = trim(mysqli_real_escape_string($db, $data['sn_simcard']));
    $sn_samcard1 = trim(mysqli_real_escape_string($db, $data['sn_samcard1']));
    $sn_samcard2 = trim(mysqli_real_escape_string($db, $data['sn_samcard2']));
    $sn_samcard3 = trim(mysqli_real_escape_string($db, $data['sn_samcard3']));

    /* ================= CEK DUPLIKASI ================= */

    // SN EDC
    if ($sn_edc !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_stock FROM stock 
             WHERE sn_edc = '$sn_edc' AND id_stock != $id_stock
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -1;
    }

    // SN SIMCARD
    if ($sn_simcard !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_stock FROM stock 
             WHERE sn_simcard = '$sn_simcard' AND id_stock != $id_stock
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -2;
    }

    // SN SAMCARD 1
    if ($sn_samcard1 !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_stock FROM stock 
             WHERE sn_samcard1 = '$sn_samcard1' AND id_stock != $id_stock
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -3;
    }

    // SN SAMCARD 2
    if ($sn_samcard2 !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_stock FROM stock 
             WHERE sn_samcard2 = '$sn_samcard2' AND id_stock != $id_stock
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -4;
    }

    // SN SAMCARD 3
    if ($sn_samcard3 !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_stock FROM stock 
             WHERE sn_samcard3 = '$sn_samcard3' AND id_stock != $id_stock
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -5;
    }

    /* ================= UPDATE DINAMIS ================= */

    $update = [];
    $update[] = "user_id = '$user_id'";
    $update[] = "status_edc = '$status_edc'";
    $update[] = "updated_at = '$updated_at'";

    if ($sn_edc !== '')      $update[] = "sn_edc = '$sn_edc'";
    if ($sn_simcard !== '')  $update[] = "sn_simcard = '$sn_simcard'";
    if ($sn_samcard1 !== '') $update[] = "sn_samcard1 = '$sn_samcard1'";
    if ($sn_samcard2 !== '') $update[] = "sn_samcard2 = '$sn_samcard2'";
    if ($sn_samcard3 !== '') $update[] = "sn_samcard3 = '$sn_samcard3'";

    $query = "UPDATE stock SET " . implode(', ', $update) . "
              WHERE id_stock = $id_stock";

    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function deleteStock($id_stock)
{
    global $db;
    mysqli_query($db, "DELETE FROM stock WHERE id_stock = $id_stock");
    return mysqli_affected_rows($db);
}

function editDetail($data)
{
    global $db;

    $stock_id = (int)$data['stock_id'];
    $now      = date('Y-m-d H:i:s');
    $date = date('Y-m-d');

    /* ================= TABLE STOCK ================= */
    $sn_edc      = mysqli_real_escape_string($db, trim($data['sn_edc']));
    $sn_simcard  = mysqli_real_escape_string($db, trim($data['sn_simcard']));
    $sn_samcard1 = mysqli_real_escape_string($db, trim($data['sn_samcard1']));
    $sn_samcard2 = mysqli_real_escape_string($db, trim($data['sn_samcard2']));
    $sn_samcard3 = mysqli_real_escape_string($db, trim($data['sn_samcard3']));
    $status_edc  = mysqli_real_escape_string($db, $data['status_edc']);
    $user_id     = (int)$data['user_id'];

    /* ================= DETAIL TABLE ================= */
    $tid           = mysqli_real_escape_string($db, trim($data['tid']));
    $mid           = mysqli_real_escape_string($db, trim($data['mid']));
    $merchant_name = mysqli_real_escape_string($db, trim($data['merchant_name']));
    $addres_name   = mysqli_real_escape_string($db, trim($data['addres_name']));
    $date       = mysqli_real_escape_string($db, $data['date']); // tanggal transaksi
    $note          = mysqli_real_escape_string($db, trim($data['note']));

    mysqli_begin_transaction($db);

    try {

        /* ========= UPDATE STOCK ========= */
        $updateStock = mysqli_query($db, "
            UPDATE stock SET
                sn_edc = '$sn_edc',
                sn_simcard = '$sn_simcard',
                sn_samcard1 = '$sn_samcard1',
                sn_samcard2 = '$sn_samcard2',
                sn_samcard3 = '$sn_samcard3',
                status_edc = '$status_edc',
                user_id = $user_id,
                updated_at = '$now'
            WHERE id_stock = $stock_id
        ");

        if (!$updateStock) {
            throw new Exception('Update stock gagal');
        }

        /* ========= CEK DETAIL ========= */
        $cek = mysqli_query($db, "
            SELECT id_detail FROM detail_list_stock
            WHERE stock_id = $stock_id
        ");

        if (mysqli_num_rows($cek) > 0) {

            /* ===== UPDATE DETAIL ===== */
            $updateDetail = mysqli_query($db, "
                UPDATE detail_list_stock SET
                    tid = '$tid',
                    mid = '$mid',
                    merchant_name = '$merchant_name',
                    addres_name = '$addres_name',
                    date = '$date',
                    note = '$note',
                    updated_at = '$now'
                WHERE stock_id = $stock_id
            ");

            if (!$updateDetail) {
                throw new Exception('Update detail gagal');
            }
        } else {

            /* ===== INSERT DETAIL ===== */
            $insertDetail = mysqli_query($db, "
                INSERT INTO detail_list_stock
                    (stock_id, tid, mid, merchant_name, addres_name, date, note, updated_at)
                VALUES
                    ($stock_id, '$tid', '$mid', '$merchant_name', '$addres_name', '$date', '$note', '$now')
            ");

            if (!$insertDetail) {
                throw new Exception('Insert detail gagal');
            }
        }

        mysqli_commit($db);
        return 1;
    } catch (Exception $e) {
        mysqli_rollback($db);
        return 0;
    }
}


function deleteDetail($stock_id)
{
    global $db;
    mysqli_query($db, "DELETE FROM detail_list_stock WHERE stock_id = $stock_id");
    return mysqli_affected_rows($db);
}

function addListReturn($data)
{
    global $db;

    $user_id = $_SESSION["id"];
    $created_at = date('Y-m-d H:i:s');

    // TRIM + ESCAPE (BENAR)
    $sn_edc      = trim(mysqli_real_escape_string($db, $data["sn_edc"]));
    $sn_simcard  = trim(mysqli_real_escape_string($db, $data["sn_simcard"]));
    $sn_samcard1 = trim(mysqli_real_escape_string($db, $data["sn_samcard1"]));
    $sn_samcard2 = trim(mysqli_real_escape_string($db, $data["sn_samcard2"]));
    $sn_samcard3 = trim(mysqli_real_escape_string($db, $data["sn_samcard3"]));
    $status1  = trim(mysqli_real_escape_string($db, $data["status1"]));
    $status2  = trim(mysqli_real_escape_string($db, $data["status2"]));
    $date       = mysqli_real_escape_string($db, $data['date']);
    $note  = trim(mysqli_real_escape_string($db, $data["note"]));

    // 1️⃣ SN EDC
    if ($sn_edc !== '') {
        $cek = mysqli_query($db, "SELECT id_return FROM return_edc WHERE sn_edc = '$sn_edc' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -1;
        }
    }

    // 2️⃣ SN SIMCARD (OPSIONAL)
    if ($sn_simcard !== '') {
        $cek = mysqli_query($db, "SELECT id_return FROM return_edc WHERE sn_simcard = '$sn_simcard' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -2;
        }
    }

    // 3️⃣ SN SAMCARD 1
    if ($sn_samcard1 !== '') {
        $cek = mysqli_query($db, "SELECT id_return FROM return_edc WHERE sn_samcard1 = '$sn_samcard1' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -3;
        }
    }

    // 4️⃣ SN SAMCARD 2
    if ($sn_samcard2 !== '') {
        $cek = mysqli_query($db, "SELECT id_return FROM return_edc WHERE sn_samcard2 = '$sn_samcard2' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -4;
        }
    }

    // 5️⃣ SN SAMCARD 3
    if ($sn_samcard3 !== '') {
        $cek = mysqli_query($db, "SELECT id_return FROM return_edc WHERE sn_samcard3 = '$sn_samcard3' LIMIT 1");
        if (mysqli_fetch_assoc($cek)) {
            return -5;
        }
    }

    /* =======================
       INSERT DATA
       ======================= */

    $query = "
        INSERT INTO return_edc
        (user_id, sn_edc, sn_simcard, sn_samcard1, sn_samcard2, sn_samcard3, status1, status2, date, note, created_at)
        VALUES (
            '$user_id',
            " . ($sn_edc  === '' ? "NULL" : "'$sn_edc'") . ",
            " . ($sn_simcard  === '' ? "NULL" : "'$sn_simcard'") . ",
            " . ($sn_samcard1 === '' ? "NULL" : "'$sn_samcard1'") . ",
            " . ($sn_samcard2 === '' ? "NULL" : "'$sn_samcard2'") . ",
            " . ($sn_samcard3 === '' ? "NULL" : "'$sn_samcard3'") . ",
            '$status1',
            '$status2',
            " . ($date === '' ? "NULL" : "'$date'") . ",
            '$note',
            '$created_at'
        )
    ";

    $insert = mysqli_query($db, $query);

    if (!$insert) {
        return 0; // insert gagal
    }

    return mysqli_affected_rows($db);
}

function editListReturn($data)
{
    global $db;

    $id_return = (int)$data['id_return'];
    $user_id  = (int)$data['user_id'];
    $status1   = mysqli_real_escape_string($db, $data['status1']);
    $status2   = mysqli_real_escape_string($db, $data['status2']);
    $date       = mysqli_real_escape_string($db, $data['date']);
    $note  = trim(mysqli_real_escape_string($db, $data["note"]));
    $updated_at = date('Y-m-d H:i:s');

    $sn_edc      = trim(mysqli_real_escape_string($db, $data['sn_edc']));
    $sn_simcard  = trim(mysqli_real_escape_string($db, $data['sn_simcard']));
    $sn_samcard1 = trim(mysqli_real_escape_string($db, $data['sn_samcard1']));
    $sn_samcard2 = trim(mysqli_real_escape_string($db, $data['sn_samcard2']));
    $sn_samcard3 = trim(mysqli_real_escape_string($db, $data['sn_samcard3']));

    /* ================= CEK DUPLIKASI ================= */

    // SN EDC
    if ($sn_edc !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_return FROM return_edc 
             WHERE sn_edc = '$sn_edc' AND id_return != $id_return
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -1;
    }

    // SN SIMCARD
    if ($sn_simcard !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_return FROM return_edc 
             WHERE sn_simcard = '$sn_simcard' AND id_return != $id_return
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -2;
    }

    // SN SAMCARD 1
    if ($sn_samcard1 !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_return FROM return_edc 
             WHERE sn_samcard1 = '$sn_samcard1' AND id_return != $id_return
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -3;
    }

    // SN SAMCARD 2
    if ($sn_samcard2 !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_return FROM return_edc 
             WHERE sn_samcard2 = '$sn_samcard2' AND id_return != $id_return
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -4;
    }

    // SN SAMCARD 3
    if ($sn_samcard3 !== '') {
        $cek = mysqli_query(
            $db,
            "SELECT id_return FROM return_edc 
             WHERE sn_samcard3 = '$sn_samcard3' AND id_return != $id_return
             LIMIT 1"
        );
        if (mysqli_fetch_assoc($cek)) return -5;
    }

    /* ================= UPDATE DINAMIS ================= */

    $update = [];
    $update[] = "user_id = '$user_id'";
    $update[] = "status1 = '$status1'";
    $update[] = "status2 = '$status2'";
    $update[] = "date = '$date'";
    $update[] = "note = '$note'";
    $update[] = "updated_at = '$updated_at'";

    if ($sn_edc !== '')      $update[] = "sn_edc = '$sn_edc'";
    if ($sn_simcard !== '')  $update[] = "sn_simcard = '$sn_simcard'";
    if ($sn_samcard1 !== '') $update[] = "sn_samcard1 = '$sn_samcard1'";
    if ($sn_samcard2 !== '') $update[] = "sn_samcard2 = '$sn_samcard2'";
    if ($sn_samcard3 !== '') $update[] = "sn_samcard3 = '$sn_samcard3'";

    $query = "UPDATE return_edc SET " . implode(', ', $update) . "
              WHERE id_return = $id_return";

    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function deleteList($id_return)
{
    global $db;
    mysqli_query($db, "DELETE FROM return_edc WHERE id_return = $id_return");
    return mysqli_affected_rows($db);
}
function editProfile($data) {}

function is_user_active($id)
{
    global $db;

    // Cek status pengguna berdasarkan ID
    $result = mysqli_query($db, "SELECT status FROM users WHERE id = '$id'");
    $row = mysqli_fetch_assoc($result);

    // Jika data ditemukan
    if ($row) {
        if ($row['status'] === 'Active') {
            return true;
        }
    }

    // Jika tidak aktif atau tidak ditemukan
    return false;
}
function logout()
{
    // Hapus semua data sesi
    $_SESSION = array();

    // Hapus cookie sesi jika ada
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Hancurkan sesi
    session_destroy();

    // Alihkan ke halaman login
    header("Location: ../login"); // Sesuaikan dengan halaman login Anda
    exit;
}
