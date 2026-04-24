<?php
session_start();
require_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['id'];
$role    = $_SESSION['role'];

$product_type = query("SELECT * FROM product_type WHERE status = 'Active'");
$color_type = query("SELECT * FROM color_type WHERE status = 'Active'");
$member_bank = query("SELECT * FROM member_bank WHERE status = 'Active'");

/* 🔥 FIX: USERS */
$users = ($role === 'Admin')
    ? query("SELECT * FROM users")
    : query("SELECT * FROM users WHERE id = $user_id");

$id_stock = (int)($_GET['id_stock'] ?? 0);
if ($id_stock <= 0) {
    http_response_code(404);
    exit;
}

/* ================= LOAD DATA ================= */
$whereUser = ($role === 'Admin') ? "" : "AND stock.user_id = $user_id";

$stock = query("
    SELECT stock.*,
           product_type.name_product,
           color_type.name_color,
           IF(users.name IS NULL, 'Deleted User', users.name) AS name,
           detail_list_stock.*
    FROM stock
    LEFT JOIN users ON stock.user_id = users.id
    LEFT JOIN detail_list_stock ON stock.id_stock = detail_list_stock.stock_id
    LEFT JOIN product_type ON stock.id_product_name = product_type.id_product
    LEFT JOIN color_type ON stock.id_edc_color = color_type.id_color
    WHERE stock.id_stock = $id_stock $whereUser
");

if (!$stock) {
    http_response_code(404);
    exit;
}

$stock = $stock[0];

/* ================= SN STATUS ================= */
$snEdcFilled = !empty($stock['sn_edc']);
$snSimFilled = !empty($stock['sn_simcard']);
$samcardFilled =
    !empty($stock['sn_samcard1']) ||
    !empty($stock['sn_samcard2']) ||
    !empty($stock['sn_samcard3']);

$isAdmin = ($role === 'Admin');

/* 🔥 FLEXIBLE RULE */
$readonlyEdc  = !$isAdmin && $snEdcFilled;
$readonlySim  = !$isAdmin && $snSimFilled;
$readonlySam1 = !$isAdmin && !empty($stock['sn_samcard1']);
$readonlySam2 = !$isAdmin && !empty($stock['sn_samcard2']);
$readonlySam3 = !$isAdmin && !empty($stock['sn_samcard3']);

/* ================= AJAX POST ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(
        editDetail($_POST)
            ? ['status' => 'success', 'message' => 'Data Successfully Changed']
            : ['status' => 'error', 'message' => 'Data Failed to Change']
    );
    exit;
}

$title = "Edit Detail";
include '../partials/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <?php include '../partials/overlay.php'; ?>
    <div class="wrapper">

        <!-- Navbar -->
        <?php include '../partials/navbar.php'; ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php include '../partials/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?= $title; ?></h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
                                <li class="breadcrumb-item">My Assets</li>
                                <li class="breadcrumb-item">All Data</li>
                                <li class="breadcrumb-item"><?= $title;  ?></li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- left column -->
                        <div class="col-md-12">
                            <!-- jquery validation -->
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-edit"></i> <?= $title; ?></h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <input type="hidden" name="stock_id" id="stock_id" value="<?= $stock["id_stock"]; ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php if ($role === 'Admin') : ?>
                                                    <div class="form-group">
                                                        <label for="user_id">Name User:</label>
                                                        <select class="form-control select2 select2-danger" id="user_id" name="user_id" style="width: 100%;">
                                                            <option value="" disabled>--Selected One--</option>
                                                            <?php foreach ($users as $user) : ?>
                                                                <option value="<?= $user["id"]; ?>"
                                                                    <?= ($stock["user_id"] == $user["id"]) ? "selected" : "" ?>>
                                                                    <?= $user["name"]; ?> (<?= $user["role"]; ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="form-group">
                                                        <label for="user_id">Name User:</label>
                                                        <!-- SELECT (DISPLAY ONLY) -->
                                                        <select class="form-control custom-select" disabled>
                                                            <?php foreach ($users as $user) : ?>
                                                                <option value="<?= $user["id"]; ?>"
                                                                    <?= ($stock["user_id"] == $user["id"]) ? "selected" : "" ?>>
                                                                    <?= $user["name"]; ?> (<?= $user["role"]; ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <!-- HIDDEN (DIKIRIM KE SERVER) -->
                                                        <input type="hidden" name="user_id" value="<?= $stock["user_id"]; ?>">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="form-group">
                                                    <label for="requirements">Requirements:</label>
                                                    <select class="custom-select form-control" name="requirements" id="requirements">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="STOCK" <?= ($stock["requirements"] == "STOCK") ? "selected" : "" ?>>STOCK</option>
                                                        <option value="RETURN" <?= ($stock["requirements"] == "RETURN") ? "selected" : "" ?>>RETURN</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="id_product_name">Product Type:</label>
                                                    <select class="form-control select2 select2-danger" id="id_product_name" name="id_product_name" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <?php foreach ($product_type as $product) : ?>
                                                            <option value="<?= $product["id_product"]; ?>"
                                                                <?= ($stock["id_product_name"] == $product["id_product"]) ? "selected" : "" ?>>
                                                                <?= $product["name_product"]; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="id_edc_color">Color Type:</label>
                                                    <select class="form-control select2 select2-danger" id="id_edc_color" name="id_edc_color" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <?php foreach ($color_type as $color) : ?>
                                                            <option value="<?= $color["id_color"]; ?>"
                                                                <?= ($stock["id_edc_color"] == $color["id_color"]) ? "selected" : "" ?>>
                                                                <?= $color["name_color"]; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_edc">SN EDC:</label>
                                                    <input type="text"
                                                        name="sn_edc"
                                                        id="sn_edc"
                                                        class="form-control"
                                                        placeholder="SN EDC"
                                                        value="<?= e($stock['sn_edc']); ?>"
                                                        <?= $readonlyEdc ? 'readonly' : '' ?>>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_simcard">SN Simcard:</label>
                                                    <input type="text"
                                                        name="sn_simcard"
                                                        id="sn_simcard"
                                                        placeholder="SN Simcard"
                                                        class="form-control"
                                                        value="<?= e($stock['sn_simcard']); ?>"
                                                        <?= $readonlySim ? 'readonly' : '' ?>>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard1">SN Samcard (MANDIRI):</label>
                                                    <input type="text"
                                                        name="sn_samcard1"
                                                        id="sn_samcard1"
                                                        class="form-control"
                                                        placeholder="SN Samcard (MANDIRI)"
                                                        value="<?= e($stock['sn_samcard1']); ?>"
                                                        <?= $readonlySam1 ? 'readonly' : '' ?>>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard2">SN Samcard (BRI):</label>
                                                    <input type="text"
                                                        name="sn_samcard2"
                                                        id="sn_samcard2"
                                                        placeholder="SN Samcard (BRI)"
                                                        class="form-control"
                                                        value="<?= e($stock['sn_samcard2']); ?>"
                                                        <?= $readonlySam2 ? 'readonly' : '' ?>>
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard3">SN Samcard (BNI):</label>
                                                    <input type="text"
                                                        name="sn_samcard3"
                                                        id="sn_samcard3"
                                                        placeholder="SN Samcard (BNI)"
                                                        class="form-control"
                                                        value="<?= e($stock['sn_samcard3']); ?>"
                                                        <?= $readonlySam3 ? 'readonly' : '' ?>>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status_condition">Status Condition:</label>
                                                    <select class="custom-select form-control" name="status_condition" id="status_condition">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="GOOD COMPLETE (EDC baik & lengkap)" <?= ($stock["status_condition"] == "GOOD COMPLETE (EDC baik & lengkap)") ? "selected" : "" ?>>GOOD COMPLETE (EDC baik & lengkap)</option>
                                                        <option value="GOOD INCOMPLETE (EDC baik tapi tidak lengkap)" <?= ($stock["status_condition"] == "GOOD INCOMPLETE (EDC baik tapi tidak lengkap)") ? "selected" : "" ?>>GOOD INCOMPLETE (EDC baik tapi tidak lengkap)</option>
                                                        <option value="DAMAGE COMPLETE (EDC rusak tapi lengkap)" <?= ($stock["status_condition"] == "DAMAGE COMPLETE (EDC rusak tapi lengkap)") ? "selected" : "" ?>>DAMAGE COMPLETE (EDC rusak tapi lengkap)</option>
                                                        <option value="DAMAGE INCOMPLETE (EDC rusak & tidak lengkap)" <?= ($stock["status_condition"] == "DAMAGE INCOMPLETE (EDC rusak & tidak lengkap)") ? "selected" : "" ?>>DAMAGE INCOMPLETE (EDC rusak & tidak lengkap)</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="id_member_bank">Member Bank:</label>
                                                    <select class="form-control select2 select2-danger" id="id_member_bank" name="id_member_bank" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <?php foreach ($member_bank as $bank) : ?>
                                                            <option value="<?= $bank["id_member"]; ?>"
                                                                <?= ($stock["id_member_bank"] == $bank["id_member"]) ? "selected" : "" ?>>
                                                                <?= $bank["name_member"]; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="work_type">Work Type:</label>
                                                    <select class="form-control select2 select2-danger" id="work_type" name="work_type" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="INSTAL" <?= ($stock["work_type"] == "INSTAL") ? "selected" : "" ?>>INSTAL</option>
                                                        <option value="REPLACEMENT EDC" <?= ($stock["work_type"] == "REPLACEMENT EDC") ? "selected" : "" ?>>REPLACEMENT EDC</option>
                                                        <option value="REPLACEMENT PART" <?= ($stock["work_type"] == "REPLACEMENT PART") ? "selected" : "" ?>>REPLACEMENT PART</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tid">TID:</label>
                                                    <input type="text" name="tid" class="form-control" id="tid" placeholder="TID" value="<?= e($stock["tid"]); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="mid">MID:</label>
                                                    <input type="text" name="mid" class="form-control" id="mid" placeholder="MID" value="<?= e($stock["mid"]); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="merchant_name">Merchant Name:</label>
                                                    <input type="text" name="merchant_name" class="form-control" id="merchant_name" placeholder="Merchant Name" value="<?= e($stock["merchant_name"]); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="addres_name">Address:</label>
                                                    <textarea class="form-control" id="addres_name" name="addres_name" rows="3"><?= htmlspecialchars($stock["addres_name"] ?? '') ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="date_pickup">Date Pickup:</label>
                                                    <input type="date" class="form-control"
                                                        value="<?= !empty($stock['date_pickup']) ? e(date('Y-m-d', strtotime($stock['date_pickup']))) : '' ?>"
                                                        readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label for="date_used">Date Used:</label>
                                                    <input type="date"
                                                        name="date_used"
                                                        id="date_used"
                                                        class="form-control"
                                                        value="<?= !empty($stock['date_used']) ? e(date('Y-m-d', strtotime($stock['date_used']))) : '' ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="date_sendto_ho">Date Send To HO:</label>
                                                    <input type="date"
                                                        name="date_sendto_ho"
                                                        id="date_sendto_ho"
                                                        class="form-control"
                                                        value="<?= !empty($stock['date_sendto_ho']) ? e(date('Y-m-d', strtotime($stock['date_sendto_ho']))) : '' ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="status_edc">Status:</label>
                                                    <select class="custom-select form-control" id="status_edc" name="status_edc">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="Not yet used" <?= ($stock['status_edc'] == 'Not yet used') ? 'selected' : '' ?>>
                                                            Not yet used
                                                        </option>
                                                        <option value="Used" <?= ($stock['status_edc'] == 'Used') ? 'selected' : '' ?>>
                                                            Used
                                                        </option>
                                                        <option value="None" <?= ($stock['status_edc'] == 'None') ? 'selected' : '' ?>>
                                                            None
                                                        </option>
                                                        <option value="Terlink" <?= ($stock['status_edc'] == 'Terlink') ? 'selected' : '' ?>>
                                                            Terlink
                                                        </option>
                                                        <option value="HO Santana" <?= ($stock['status_edc'] == 'HO Santana') ? 'selected' : '' ?>>
                                                            HO Santana
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="note">Note:</label>
                                                    <textarea class="form-control" id="note" name="note" rows="3"><?= htmlspecialchars($stock["note"] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" name="submit" class="btn btn-success mr-1"><i class="fas fa-solid fa-check"></i> Save Change</button>
                                        <button type="reset" class="btn btn-dark">Reset</button>
                                    </div>
                                </form>
                            </div>
                            <!-- /.card -->
                        </div>
                        <!--/.col (left) -->
                        <!-- right column -->
                        <div class="col-md-6">

                        </div>
                        <!--/.col (right) -->
                    </div>
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Main Footer -->
        <?php include '../partials/footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <?php require_once '../partials/scripts.php'; ?>

    <script>
        $(function() {
            // Initialize Select2 Elements
            $('.select2').select2();

            // Initialize Select2 Bootstrap 4
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
    <script>
        $(function() {
            bsCustomFileInput.init();
        });
    </script>
    <script>
        $(function() {

            // ================= VALIDATION =================
            $('#quickForm').validate({
                rules: {
                    date: {
                        required: true
                    }
                },
                messages: {
                    date: {
                        required: "Please enter an Date"
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });

            // ================= SUBMIT =================
            $('#quickForm').on('submit', function(e) {
                e.preventDefault();

                if (!$(this).valid()) return;

                let formData = new FormData(this);

                // 🔥 CEK DUPLICATE SN DULU
                $.ajax({
                    url: '<?= base_url('all_data/check_sn') ?>', // pastikan file ini ada
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {

                        if (res.duplicates && res.duplicates.length > 0) {

                            let text = "The Following SN Will Be Moved:\n\n";

                            res.duplicates.forEach(d => {
                                text += `- ${d.sn} (From Stock ${d.stock_id})\n`;
                            });

                            Swal.fire({
                                title: 'SN Already Used!',
                                text: text,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Continue',
                                cancelButtonText: 'Cancelled'
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    submitForm(formData);
                                }

                            });

                        } else {
                            // 🔥 TIDAK ADA DUPLICATE → LANGSUNG SUBMIT
                            submitForm(formData);
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed To Check SN', 'error');
                    }
                });
            });

        });

        // ================= FUNCTION SUBMIT =================
        function submitForm(formData) {

            $('#pageLoader').show();
            $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    $('#pageLoader').hide();
                    $('button[type="submit"]').prop('disabled', false);

                    if (res.status === 'success') {
                        Swal.fire('Success', res.message, 'success')
                            .then(() => window.location.href = '<?= base_url('all_data') ?>');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    $('#pageLoader').hide();
                    $('button[type="submit"]').prop('disabled', false);
                    console.log(xhr.responseText);
                    Swal.fire('Error', 'Server Error', 'error');
                }
            });
        }
    </script>
</body>

</html>