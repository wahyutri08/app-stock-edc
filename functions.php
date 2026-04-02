<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 404 Forbidden');
    include("errors/404.html");
    exit();
}
$db = mysqli_connect("localhost", "root", "", "final-stock-edc");
date_default_timezone_set('Asia/Jakarta');


// Base URL aplikasi
define("BASEURL", "http://localhost/app-stock-edc/");

// Fungsi helper base_url
function base_url($path = "")
{
    return BASEURL . $path;
}

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

function addUser($data)
{
    global $db;

    $username = strtolower(stripcslashes($data["username"]));
    $email = strtolower(stripslashes($data["email"]));
    $name = ucfirst(stripslashes($data["name"]));
    $no_telfon = htmlspecialchars($data["no_telfon"]);
    $password = mysqli_real_escape_string($db, $data["password"]);
    $password2 = mysqli_real_escape_string($db, $data["password2"]);
    $role = htmlspecialchars($data["role"]);
    $status = htmlspecialchars($data["status"]);
    $created_at = date('Y-m-d H:i:s');


    //  Upload Gambar
    $avatar = upload();
    if ($avatar === -1) {
        return -3;
    }

    $result = mysqli_query($db, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_fetch_assoc($result)) {
        // Jika Nama Username Sudah Ada
        return -1;
    }

    if ($password !== $password2) {
        // Password 1 tidak sesuai dengan password 2
        return -2;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    mysqli_query($db, "INSERT INTO users 
    (username, name, email, no_telfon, password, avatar, status, role, created_at) 
    VALUES 
    ('$username','$name', '$email', '$no_telfon', '$password', '$avatar', '$status', '$role', '$created_at')");
    return mysqli_affected_rows($db);
}

function editUsers($data)
{
    global $db;
    $id = ($data["id"]);
    $username = strtolower(stripslashes($data["username"]));
    $name = ucfirst(stripcslashes($data["name"]));
    $email = strtolower(stripslashes($data["email"]));
    $password = mysqli_real_escape_string($db, $data["password"]);
    $password2 = mysqli_real_escape_string($db, $data["password2"]);
    $avatarLama = htmlspecialchars($data["avatarLama"]);
    $role = htmlspecialchars($data["role"]);
    $no_telfon = htmlspecialchars($data["no_telfon"]);
    $status = htmlspecialchars($data["status"]);
    $updated_at = date('Y-m-d H:i:s');
    // $usernameLama = htmlspecialchars($data["username"]);

    // Cek apakah user pilih avatar baru atau tidak
    if ($_FILES['avatar']['error'] === 4) {
        $avatar = $avatarLama;
    } else {
        $avatar = upload();
        if ($avatar === -1) {
            // Kesalahan Jika Bukan Gambar
            return -1;
        } elseif ($avatar === -2) {
            // Kesalahan Ukuran Terlalu Besar
            return -2;
        }
    }

    if ($password !== $password2) {
        // Password 1 tidak sesuai dengan password 2
        return -3;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET 
        username = '$username', 
        name = '$name', 
        email = '$email',
        no_telfon = '$no_telfon',
        avatar = '$avatar',
        status = '$status',
        role = '$role',
        updated_at = '$updated_at' WHERE id = $id";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function deleteUsers($id)
{
    global $db;
    mysqli_query($db, "DELETE FROM users WHERE id = $id");
    return mysqli_affected_rows($db);
}


function addStock($data)
{
    global $db;

    $user_id = (int) $_SESSION["id"];
    $created_at = date('Y-m-d H:i:s');

    $totalInsert = 0;

    if (!isset($data['sn_edc'])) {
        return 0;
    }

    foreach ($data['sn_edc'] as $i => $value) {

        /* ===============================
           AMBIL DATA DENGAN SAFE INDEX
        =============================== */

        $sn_edc      = trim(mysqli_real_escape_string($db, $data['sn_edc'][$i] ?? ''));
        $requirements = trim(mysqli_real_escape_string($db, $data['requirements'][$i] ?? ''));
        $sn_simcard  = trim(mysqli_real_escape_string($db, $data['sn_simcard'][$i] ?? ''));
        $sn_samcard1 = trim(mysqli_real_escape_string($db, $data['sn_samcard1'][$i] ?? ''));
        $sn_samcard2 = trim(mysqli_real_escape_string($db, $data['sn_samcard2'][$i] ?? ''));
        $sn_samcard3 = trim(mysqli_real_escape_string($db, $data['sn_samcard3'][$i] ?? ''));

        $id_product_name = !empty($data['id_product_name'][$i] ?? '')
            ? (int)$data['id_product_name'][$i]
            : "NULL";

        $id_edc_color = !empty($data['id_edc_color'][$i] ?? '')
            ? (int)$data['id_edc_color'][$i]
            : "NULL";

        $status_edc = trim(mysqli_real_escape_string($db, $data['status_edc'][$i] ?? ''));
        $status_condition = trim(mysqli_real_escape_string($db, $data['status_condition'][$i] ?? ''));
        $date       = trim(mysqli_real_escape_string($db, $data['date_pickup'][$i] ?? ''));

        /* ===============================
           SKIP FORM JIKA SEMUA KOSONG
        =============================== */

        if (
            $sn_edc === '' &&
            $sn_simcard === '' &&
            $sn_samcard1 === '' &&
            $sn_samcard2 === '' &&
            $sn_samcard3 === ''
        ) {
            continue;
        }

        /* ===============================
           VALIDASI DUPLICATE
        =============================== */

        if ($sn_edc !== '') {
            $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_edc = '$sn_edc' LIMIT 1");
            if (mysqli_fetch_assoc($cek)) {
                return -1;
            }
        }

        if ($sn_simcard !== '') {
            $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_simcard = '$sn_simcard' LIMIT 1");
            if (mysqli_fetch_assoc($cek)) {
                return -2;
            }
        }

        if ($sn_samcard1 !== '') {
            $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_samcard1 = '$sn_samcard1' LIMIT 1");
            if (mysqli_fetch_assoc($cek)) {
                return -3;
            }
        }

        if ($sn_samcard2 !== '') {
            $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_samcard2 = '$sn_samcard2' LIMIT 1");
            if (mysqli_fetch_assoc($cek)) {
                return -4;
            }
        }

        if ($sn_samcard3 !== '') {
            $cek = mysqli_query($db, "SELECT id_stock FROM stock WHERE sn_samcard3 = '$sn_samcard3' LIMIT 1");
            if (mysqli_fetch_assoc($cek)) {
                return -5;
            }
        }

        /* ===============================
           INSERT DATA
        =============================== */

        $query = "
        INSERT INTO stock
        (
            user_id,
            sn_edc,
            requirements,
            id_product_name,
            id_edc_color,
            sn_simcard,
            sn_samcard1,
            sn_samcard2,
            sn_samcard3,
            date_pickup,
            status_edc,
            status_condition,
            created_at
        )
        VALUES
        (
            '$user_id',
            " . ($sn_edc === '' ? "NULL" : "'$sn_edc'") . ",
            '$requirements',
            $id_product_name,
            $id_edc_color,
            " . ($sn_simcard === '' ? "NULL" : "'$sn_simcard'") . ",
            " . ($sn_samcard1 === '' ? "NULL" : "'$sn_samcard1'") . ",
            " . ($sn_samcard2 === '' ? "NULL" : "'$sn_samcard2'") . ",
            " . ($sn_samcard3 === '' ? "NULL" : "'$sn_samcard3'") . ",
            '$date',
            '$status_edc',
            '$status_condition',
            '$created_at'
        )
        ";

        $insert = mysqli_query($db, $query);

        if ($insert) {
            $totalInsert++;
        }
    }

    return $totalInsert;
}

function editStock($data)
{
    global $db;

    $id_stock = (int)$data['id_stock'];
    $user_id  = (int)$data['user_id'];
    $status_edc   = mysqli_real_escape_string($db, $data['status_edc']);
    $updated_at = date('Y-m-d H:i:s');
    $date = trim(mysqli_real_escape_string($db, $data['date_pickup']));

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
    $update[] = "date_pickup = '$date'";

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

    /* ================= TABLE STOCK ================= */
    $requirements = mysqli_real_escape_string($db, $data['requirements']);
    $sn_edc = mysqli_real_escape_string($db, trim($data['sn_edc']));
    $id_product_name = !empty($data['id_product_name']) ? (int)$data['id_product_name'] : "NULL";
    $id_edc_color    = !empty($data['id_edc_color']) ? (int)$data['id_edc_color'] : "NULL";
    $sn_simcard  = mysqli_real_escape_string($db, trim($data['sn_simcard']));
    $sn_samcard1 = mysqli_real_escape_string($db, trim($data['sn_samcard1']));
    $sn_samcard2 = mysqli_real_escape_string($db, trim($data['sn_samcard2']));
    $sn_samcard3 = mysqli_real_escape_string($db, trim($data['sn_samcard3']));
    $status_edc = mysqli_real_escape_string($db, $data['status_edc']);
    $status_condition = mysqli_real_escape_string($db, $data['status_condition']);
    $user_id    = (int)$data['user_id'];

    /* ================= DETAIL TABLE ================= */
    $tid           = mysqli_real_escape_string($db, trim($data['tid']));
    $mid           = mysqli_real_escape_string($db, trim($data['mid']));
    $merchant_name = mysqli_real_escape_string($db, trim($data['merchant_name']));
    $addres_name   = mysqli_real_escape_string($db, trim($data['addres_name']));
    $note          = mysqli_real_escape_string($db, trim($data['note']));

    $id_member_bank = !empty($data['id_member_bank']) ? (int)$data['id_member_bank'] : "NULL";

    // work_type bisa NULL / string
    $work_type = !empty($data['work_type'])
        ? "'" . mysqli_real_escape_string($db, $data['work_type']) . "'"
        : "NULL";

    // date_used bisa NULL
    $date = !empty($data['date_used'])
        ? "'" . mysqli_real_escape_string($db, $data['date_used']) . "'"
        : "NULL";

    // date_sendto_ho bisa NULL
    $date_sendto_ho = !empty($data['date_sendto_ho'])
        ? "'" . mysqli_real_escape_string($db, $data['date_sendto_ho']) . "'"
        : "NULL";

    mysqli_begin_transaction($db);

    try {

        /* ========= UPDATE STOCK ========= */
        $updateStock = mysqli_query($db, "
            UPDATE stock SET
                sn_edc = '$sn_edc',
                requirements = '$requirements',
                id_product_name = $id_product_name,
                id_edc_color = $id_edc_color,
                sn_simcard = '$sn_simcard',
                sn_samcard1 = '$sn_samcard1',
                sn_samcard2 = '$sn_samcard2',
                sn_samcard3 = '$sn_samcard3',
                date_sendto_ho = $date_sendto_ho,
                status_edc = '$status_edc',
                status_condition = '$status_condition',
                user_id = $user_id,
                updated_at = '$now'
            WHERE id_stock = $stock_id
        ");

        if (!$updateStock) {
            throw new Exception(mysqli_error($db));
        }

        /* ========= CEK DETAIL ========= */
        $cek = mysqli_query($db, "
            SELECT id_detail 
            FROM detail_list_stock
            WHERE stock_id = $stock_id
            LIMIT 1
        ");

        if (!$cek) {
            throw new Exception(mysqli_error($db));
        }

        if (mysqli_num_rows($cek) > 0) {

            /* ===== UPDATE DETAIL ===== */
            $updateDetail = mysqli_query($db, "
                UPDATE detail_list_stock SET
                    tid = '$tid',
                    mid = '$mid',
                    merchant_name = '$merchant_name',
                    addres_name = '$addres_name',
                    id_member_bank = $id_member_bank,
                    work_type = $work_type,
                    date_used = $date,
                    note = '$note',
                    updated_at = '$now'
                WHERE stock_id = $stock_id
            ");

            if (!$updateDetail) {
                throw new Exception(mysqli_error($db));
            }
        } else {

            /* ===== INSERT DETAIL ===== */
            $insertDetail = mysqli_query($db, "
                INSERT INTO detail_list_stock
                    (stock_id, tid, mid, merchant_name, addres_name, id_member_bank, work_type, date_used, note, updated_at)
                VALUES
                    ($stock_id, '$tid', '$mid', '$merchant_name', '$addres_name', $id_member_bank, $work_type, $date, '$note', '$now')
            ");

            if (!$insertDetail) {
                throw new Exception(mysqli_error($db));
            }
        }

        mysqli_commit($db);
        return 1;
    } catch (Exception $e) {

        mysqli_rollback($db);

        // optional debug
        // echo $e->getMessage();

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
    $date       = mysqli_real_escape_string($db, $data['date_tech']);
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
        (user_id, sn_edc, sn_simcard, sn_samcard1, sn_samcard2, sn_samcard3, status1, status2, date_tech, note, created_at)
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
    $date_tech       = mysqli_real_escape_string($db, $data['date_tech']);
    $date_to_ho       = mysqli_real_escape_string($db, $data['date_to_ho']);
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
    $update[] = "date_tech = '$date_tech'";
    $update[] = "date_to_ho = '$date_to_ho'";
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

function editProfile($data)
{
    global $db;
    $id = ($data["id"]);
    $name = ucfirst(stripcslashes($data["name"]));
    $email = strtolower(stripslashes($data["email"]));
    $no_telfon = htmlspecialchars($data["no_telfon"]);
    $avatarLama = htmlspecialchars($data["avatarLama"]);

    // Cek apakah user pilih avatar baru atau tidak
    if ($_FILES['avatar']['error'] === 4) {
        $avatar = $avatarLama;
    } else {
        $avatar = upload();
        if ($avatar === -1) {
            // Kesalahan Jika Bukan Gambar
            return -1;
        } elseif ($avatar === -2) {
            // Kesalahan Jika Ukuran Terlalu Besar
            return -2;
        }
    }

    $query = "UPDATE users SET 
        name = '$name',  
        email = '$email',
        no_telfon = '$no_telfon',
        avatar = '$avatar' WHERE id = $id";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function changePassword($data)
{
    global $db;
    $id = ($data["id"]);
    $password = mysqli_real_escape_string($db, $data["password"]);
    $password2 = mysqli_real_escape_string($db, $data["password2"]);

    if ($password !== $password2) {
        return -1;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET 
    password = '$password' WHERE id = $id";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}


function upload()
{

    $namaFile = $_FILES['avatar']['name'];
    $ukuranFiles = $_FILES['avatar']['size'];
    $error = $_FILES['avatar']['error'];
    $tmpName = $_FILES['avatar']['tmp_name'];

    // Cek apakah yang diupload adalah gambar
    $ekstensiAvatarValid = ['', 'jpg', 'jpeg', 'png'];
    $ekstensiAvatar = explode('.', $namaFile);
    $ekstensiAvatar = strtolower(end($ekstensiAvatar));
    if (!in_array($ekstensiAvatar, $ekstensiAvatarValid)) {
        // Jika Avatar Bukan Gambar
        return -1;
    }

    if ($ukuranFiles > 10000000) {
        // Cek jika ukuran terlalu besar
        return -2;
    }

    // Gambar Siap Upload
    // generate nama gambar baru

    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiAvatar;

    move_uploaded_file($tmpName, '../assets/dist/img/profile/' . $namaFileBaru);

    return $namaFileBaru;
}

function addProductName($data)
{
    global $db;
    $id_product = htmlspecialchars($data["id_product"]);
    $name_product = htmlspecialchars($data["name_product"]);
    $status = htmlspecialchars($data["status"]);
    $created_at = date('Y-m-d H:i:s');

    $query = "SELECT * FROM product_type WHERE name_product = '$name_product' AND id_product != $id_product";
    $result = mysqli_query($db, $query);
    if (mysqli_fetch_assoc($result)) {
        return -1;
    }

    // Periksa apakah id_product sudah ada
    $query_id = "SELECT * FROM product_type WHERE id_product = $id_product";
    $result_id = mysqli_query($db, $query_id);

    if (mysqli_fetch_assoc($result_id)) {
        // id_product tidak ditemukan
        return -2;
    }

    $query = "INSERT INTO product_type VALUES 
    ('$id_product', '$name_product', '$status', '$created_at', NULL)";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function editProductName($data)
{
    global $db;
    $id_product   = (int) $data["id_product"];
    $name_product = mysqli_real_escape_string($db, $_POST["name_product"]);
    $status       = mysqli_real_escape_string($db, $_POST["status"]);
    $updated_at   = date('Y-m-d H:i:s');

    // Cek duplikat nama (kecuali id yang sedang diedit)
    $query = "SELECT id_product 
              FROM product_type 
              WHERE name_product = '$name_product' 
              AND id_product != $id_product";
    $result = mysqli_query($db, $query);

    if (mysqli_fetch_assoc($result)) {
        return -1;
    }

    // Update data
    $query = "UPDATE product_type SET 
                name_product = '$name_product',
                status = '$status',
                updated_at = '$updated_at'
              WHERE id_product = $id_product";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function deleteProductName($id_product)
{
    global $db;
    mysqli_query($db, "DELETE FROM product_type WHERE id_product = $id_product");
    return mysqli_affected_rows($db);
}

function addColorName($data)
{
    global $db;
    $id_color = htmlspecialchars($data["id_color"]);
    $name_color = htmlspecialchars($data["name_color"]);
    $status = htmlspecialchars($data["status"]);
    $created_at = date('Y-m-d H:i:s');

    $query = "SELECT * FROM color_type WHERE name_color = '$name_color' AND id_color != $id_color";
    $result = mysqli_query($db, $query);
    if (mysqli_fetch_assoc($result)) {
        return -1;
    }

    // Periksa apakah id_color sudah ada
    $query_id = "SELECT * FROM color_type WHERE id_color = $id_color";
    $result_id = mysqli_query($db, $query_id);

    if (mysqli_fetch_assoc($result_id)) {
        // id_color tidak ditemukan
        return -2;
    }

    $query = "INSERT INTO color_type VALUES 
    ('$id_color', '$name_color', '$status', '$created_at', NULL)";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function editColorName($data)
{
    global $db;
    $id_color   = (int) $data["id_color"];
    $name_color = mysqli_real_escape_string($db, $_POST["name_color"]);
    $status       = mysqli_real_escape_string($db, $_POST["status"]);
    $updated_at   = date('Y-m-d H:i:s');

    // Cek duplikat nama (kecuali id yang sedang diedit)
    $query = "SELECT id_color 
              FROM color_type 
              WHERE name_color = '$name_color' 
              AND id_color != $id_color";
    $result = mysqli_query($db, $query);

    if (mysqli_fetch_assoc($result)) {
        return -1;
    }

    // Update data
    $query = "UPDATE color_type SET 
                name_color = '$name_color',
                status = '$status',
                updated_at = '$updated_at'
              WHERE id_color = $id_color";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function deleteColorName($id_color)
{
    global $db;
    mysqli_query($db, "DELETE FROM color_type WHERE id_color = $id_color");
    return mysqli_affected_rows($db);
}

function addMemberBank($data)
{
    global $db;
    $id_member = htmlspecialchars($data["id_member"]);
    $name_member = htmlspecialchars($data["name_member"]);
    $status = htmlspecialchars($data["status"]);
    $created_at = date('Y-m-d H:i:s');

    $query = "SELECT * FROM member_bank WHERE name_member = '$name_member' AND id_member != $id_member";
    $result = mysqli_query($db, $query);
    if (mysqli_fetch_assoc($result)) {
        return -1;
    }

    // Periksa apakah id_member sudah ada
    $query_id = "SELECT * FROM member_bank WHERE id_member = $id_member";
    $result_id = mysqli_query($db, $query_id);

    if (mysqli_fetch_assoc($result_id)) {
        // id_member tidak ditemukan
        return -2;
    }

    $query = "INSERT INTO member_bank VALUES 
    ('$id_member', '$name_member', '$status', '$created_at', NULL)";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function editMemberBank($data)
{
    global $db;
    $id_member   = (int) $data["id_member"];
    $name_member = mysqli_real_escape_string($db, trim($data["name_member"]));
    $status = mysqli_real_escape_string($db, trim($data["status"]));
    $updated_at   = date('Y-m-d H:i:s');

    // Cek duplikat nama (kecuali id yang sedang diedit)
    $query = "SELECT id_member 
              FROM member_bank 
              WHERE name_member = '$name_member' 
              AND id_member != $id_member";
    $result = mysqli_query($db, $query);

    if (mysqli_fetch_assoc($result)) {
        return -1;
    }
    // Validasi kosong
    if (empty($name_member)) {
        return -3; // Nama tidak boleh kosong
    }

    // Update data
    $query = "UPDATE member_bank SET 
                name_member = '$name_member',
                status = '$status',
                updated_at = '$updated_at'
              WHERE id_member = $id_member";
    mysqli_query($db, $query);

    return mysqli_affected_rows($db);
}

function deleteMemberBank($id_member)
{
    global $db;
    mysqli_query($db, "DELETE FROM member_bank WHERE id_member = $id_member");
    return mysqli_affected_rows($db);
}

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
