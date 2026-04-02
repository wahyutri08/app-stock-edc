<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

// ================= AMBIL ID =================
$ids = $_POST['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    http_response_code(400);
    exit;
}

// amankan + jadikan string untuk query
$idList = implode(',', array_map('intval', $ids));

// ================= QUERY =================
$query = "SELECT 
            stock.*,
            users.name,
            users.no_telfon,
            product_type.name_product,
            member_bank.name_member
          FROM stock
          LEFT JOIN users ON stock.user_id = users.id
          LEFT JOIN product_type ON stock.id_product_name = product_type.id_product
          LEFT JOIN detail_list_stock ON stock.id_stock = detail_list_stock.stock_id
          LEFT JOIN member_bank ON detail_list_stock.id_member_bank = member_bank.id_member
          WHERE stock.id_stock IN ($idList)";

$result = mysqli_query($db, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (empty($data)) {
    http_response_code(404);
    // echo "Data tidak ditemukan";
    exit;
}


// ================= HEADER =================
$first = $data[0];
$hub = "SANTANA";
$no_telfon = $first['no_telfon'] ?? '-';
$tanggal = date('d-m-Y');
$teknisi = $first['name'] ?? '-';
$jenis_transaksi = $first['requirements'] == 'RETURN' ? 'Pengembalian' : 'Pengambilan';

$check_pengambilan = ($jenis_transaksi == 'Pengambilan') ? '☑' : '☐';
$check_pengembalian = ($jenis_transaksi == 'Pengembalian') ? '☑' : '☐';

// ================= BUILD ROW =================
$rows = [];

foreach ($data as $row) {

    if (!empty($row['sn_edc'])) {
        $rows[] = ["EDC", $row['name_product'], $row['sn_edc'], "1", $row['status_condition']];
    }

    if (!empty($row['sn_simcard'])) {
        $rows[] = ["SIM", "TELKOMSEL", $row['sn_simcard'], "1", ""];
    }

    // ================= SAM FIX =================
    $sam_mapping = [
        1 => "MANDIRI",
        2 => "BRI",
        3 => "BNI"
    ];

    for ($i = 1; $i <= 3; $i++) {

        $sam_sn = $row['sn_samcard' . $i];

        if (!empty($sam_sn)) {

            $nama_bank = $sam_mapping[$i] ?? '-';

            $rows[] = [
                "SAM",
                $nama_bank,
                $sam_sn,
                "1",
                ""
            ];
        }
    }
}

// ================= PAGINATION =================
$chunks = array_chunk($rows, 20);

// ================= HTML =================
ob_start();

foreach ($chunks as $index => $chunk) :
?>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid black;
            padding: 4px;
            font-size: 11px;
        }

        .no-border td {
            border: none;
        }

        .center {
            text-align: center;
        }
    </style>

    <div class="title">
        BAST PENGAMBILAN/PENGEMBALIAN BARANG MTI IV<br>
        (NO BAST: BAST / SNT / OUT / IN / 2026)
    </div>

    <br>

    <table class="no-border">
        <tr>
            <td width="30%">Nama HUB/ADO</td>
            <td width="40%">: <?= $hub ?></td>
            <td width="30%" style="text-align:right;">NO HP : <?= $no_telfon; ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: <?= $tanggal ?></td>
            <td></td>
        </tr>
        <tr>
            <td>Jenis Transaksi</td>
            <td colspan="2">: <?= $check_pengambilan ?> Pengambilan &nbsp;&nbsp;&nbsp; <?= $check_pengembalian ?> Pengembalian</td>
        </tr>
        <tr>
            <td>Nama Teknisi</td>
            <td>: <?= $teknisi ?></td>
            <td></td>
        </tr>
        <tr>
            <td>ID Teknisi / Kawasan</td>
            <td>: Mall Jakbar</td>
            <td></td>
        </tr>
    </table>

    <br>

    <table>
        <tr class="center">
            <th>No</th>
            <th>Jenis Barang (EDC / Sticker / SIM / SAM / Thermal)</th>
            <th>Tipe / Keterangan</th>
            <th>SN EDC/SIM/SAM</th>
            <th>Qty</th>
            <th>Notes / Remarks</th>
            <th>Paraf</th>
        </tr>

        <?php
        $no = 1;
        foreach ($chunk as $r) :
        ?>
            <tr>
                <td class="center"><?= $no++ ?></td>
                <td><?= $r[0] ?></td>
                <td><?= $r[1] ?></td>
                <td><?= $r[2] ?></td>
                <td class="center"><?= $r[3] ?></td>
                <td><?= $r[4] ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>

        <?php for ($i = $no; $i <= 20; $i++) : ?>
            <tr>
                <td class="center"><?= $i ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php endfor; ?>

    </table>

    <br><br>

    <table width="100%" class="no-border">
        <tr>
            <td><b>TANDA TANGAN</b></td>
        </tr>
        <tr>
            <td width="50%">Admin HUB/ADO</td>
            <td width="50%" style="text-align:right;">Teknisi</td>
        </tr>
        <tr>
            <td>Nama :</td>
            <td style="text-align:right;">Nama : <?= $teknisi ?></td>
        </tr>
        <tr>
            <td style="height:60px;"></td>
            <td></td>
        </tr>
        <tr>
            <td>TTD :</td>
            <td style="text-align:right;">TTD :</td>
        </tr>
        <tr>
            <td>(___________________)</td>
            <td style="text-align:right;">( <?= $teknisi ?> )</td>
        </tr>
    </table>

    <?php if ($index < count($chunks) - 1): ?>
        <pagebreak />
    <?php endif; ?>

<?php endforeach;

$html = ob_get_clean();

// ================= MPDF =================
$mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
$mpdf->WriteHTML($html);
$mpdf->Output("BAST.pdf", "I");
