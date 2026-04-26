<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once("../auth_check.php");

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ids'])) {
    die("Invalid Request");
}

$ids = array_map('intval', $_POST['ids']);
$idList = implode(',', $ids);

// ambil data
$query = mysqli_query($db, "
    SELECT f.*, u.name 
    FROM fkm f
    LEFT JOIN users u ON u.id = f.user_id
    WHERE f.id_fkm IN ($idList)
    ORDER BY f.id_fkm ASC
");

$data = mysqli_fetch_all($query, MYSQLI_ASSOC);

if (!$data) die("Data tidak ditemukan");

// split per 2
$chunks = array_chunk($data, 2);

// 🔥 INIT MPDF
$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'margin_top' => 5,
    'margin_bottom' => 5,
    'margin_left' => 5,
    'margin_right' => 5,
]);

// 🔥 STYLE (cukup sekali)
$style = '
<style>
body { font-family: Arial; font-size: 11px; margin:0; }
.page { width:100%; page-break-inside: avoid; }
.wrapper { width:100%; margin-bottom:5px; page-break-inside: avoid; }
.company { text-align:center; font-weight:bold; font-size:20px; }
.address { text-align:center; font-size:13px; line-height:1.4; }
.double-line { border-top:2px solid #000; border-bottom:1px solid #000; margin:5px 0 10px 0; }
.title { text-align:center; font-weight:bold; font-size:20px; margin-bottom:10px; }
.field { margin-bottom:6px; }
.field-big { font-size:13px; font-weight:bold; }
.label { display:inline-block; width:120px; }
.line { border-bottom:1px solid #000; display:inline-block; width:70%; height:14px; }
.table { width:100%; border-collapse:collapse; margin-top:8px; font-size:13px; }
.table th, .table td { border:1px solid #000; padding:5px; text-align:center; }
.footer { margin-top:15px; width:100%; }
.footer td { width:33%; font-weight:bold; text-align:left; font-size:13px; }
.ttd { height:70px; }
.dashed { border-top:2px dotted #000; margin:10px 0; }
</style>
';

$mpdf->WriteHTML($style);

// 🔥 LOOP PER CHUNK (INI KUNCI FIX)
foreach ($chunks as $chunkIndex => $pair) {

    ob_start();
?>

    <div class="page">

        <?php foreach ($pair as $index => $row): ?>

            <div class="wrapper">

                <!-- HEADER -->
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <td style="width:1%; white-space:nowrap; vertical-align: middle;">
                            <img src="<?= base_url('assets/dist/img/Yokke.png') ?>" style="width:150px;">
                        </td>
                        <td style="text-align:center; vertical-align: middle; padding-left:5px;">
                            <div class="company">PT. MITRA TRANSAKSI INDONESIA (MTI)</div><br>
                            <div class="address">
                                Gd. Wisma Nugra Santana Lt. 6 Jl. Jendral Sudirman Kav. 7-8, RT.10/RW.11<br>
                                Karet Tengsin, Kecamatan Tanah Abang, Kota Jakarta Pusat, DKI Jakarta 10520
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="double-line"></div>

                <div class="title">Tanda Terima Supply Thermal Roll</div>
                <br><br>

                <!-- FIELD -->
                <div class="field field-big">
                    <span class="label">Nama Merchant</span> :
                    <span class="line"><?= $row['nama_merchant']; ?></span>
                </div>

                <div class="field field-big">
                    <span class="label">Alamat</span> :
                    <span class="line"><?= $row['alamat']; ?></span>
                </div>

                <!-- TABLE -->
                <table class="table">
                    <tr>
                        <th width="25%">MID</th>
                        <th width="25%">TID</th>
                        <th>Jumlah Thermal Roll Yang Diberikan</th>
                    </tr>
                    <tr>
                        <td><?= $row['mid']; ?></td>
                        <td><?= $row['tid']; ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td height="25"></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>

                <!-- FOOTER -->
                <table class="footer">
                    <tr>
                        <td>Pihak Merchant</td>
                        <td>Teknisi Yokke</td>
                        <td>Tanggal :</td>
                    </tr>
                    <tr>
                        <td class="ttd"></td>
                        <td class="ttd"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Nama :</td>
                        <td>Nama : <?= $row['name']; ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>No Telepon :</td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>

            </div>
            <br>

            <!-- garis putus kalau ada 2 -->
            <?php if ($index == 0 && count($pair) > 1): ?>
                <div class="dashed"></div>
            <?php endif; ?>

        <?php endforeach; ?>

    </div>

<?php
    $html = ob_get_clean();

    // 🔥 kirim per chunk (bukan sekaligus)
    $mpdf->WriteHTML($html);

    // page break kalau bukan terakhir
    if ($chunkIndex < count($chunks) - 1) {
        $mpdf->AddPage();
    }
}

$mpdf->Output('BAST_Thermal.pdf', 'I');
