<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

if ($_SESSION["role"] !== 'Admin') {
    http_response_code(404);
    exit;
}


$role     = $_SESSION['role'];
$user_id  = (int) $_SESSION['id'];

$users = query("SELECT id, name, role FROM users ORDER BY name ASC");

$product_type = query("SELECT * FROM product_type WHERE status = 'Active'");
$member_bank = query("SELECT * FROM member_bank WHERE status = 'Active'");

$title = "Export Data";
require_once '../partials/header.php';

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
                            <h1 class="m-0"><?= $title;  ?></h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
                                <li class="breadcrumb-item">Export & Import Data</li>
                                <li class="breadcrumb-item"><?= $title;  ?></li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-search"></i> Filter Search</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="POST" action="">
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for="date_pickup">Date Pick Up:</label>
                                                <input type="date" class="form-control" name="date_pickup" id="date_pickup">
                                            </div>
                                            <div class=" form-group col-md-3">
                                                <label for="date_used">Date Used:</label>
                                                <input type="date" class="form-control" name="date_used" id="date_used">
                                            </div>
                                            <div class=" form-group col-md-3">
                                                <label for="date_sendto_ho">Date Send To HO:</label>
                                                <input type="date" class="form-control" name="date_sendto_ho" id="date_sendto_ho">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="user_id">User:</label>
                                                <select class="select2 form-control" name="user_id" id="user_id" style="width:100%;">
                                                    <option value="all">- All Users -</option>
                                                    <?php foreach ($users as $user) : ?>
                                                        <option value="<?= $user["id"]; ?>">
                                                            <?= htmlspecialchars($user["name"]); ?>
                                                            (<?= htmlspecialchars($user["role"]); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Requirements:</label>
                                                <select class="custom-select form-control" name="requirements" id="requirements">
                                                    <option value="all">-All Requirements-</option>
                                                    <option value="STOCK">STOCK</option>
                                                    <option value="RETURN">RETURN</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="id_product_name">Product Type:</label>
                                                <select class="select2" name="id_product_name[]" id="id_product_name" multiple="multiple" data-placeholder="Select a Product Type" data-dropdown-css-class="select2-purple" style="width: 100%;">
                                                    <!-- <option value="all">-All Product Type-</option> -->
                                                    <?php foreach ($product_type as $type) : ?>
                                                        <option value="<?= $type["id_product"]; ?>"><?= $type["name_product"]; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="id_member_bank">Member Bank:</label>
                                                <select class="select2" name="id_member_bank" id="id_member_bank" style="width: 100%;">
                                                    <option value="all">-All Member Bank-</option>
                                                    <?php foreach ($member_bank as $bank) : ?>
                                                        <option value="<?= $bank["id_member"]; ?>"><?= $bank["name_member"]; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="work_type">Work Type:</label>
                                                <select class="form-control" name="work_type" id="work_type">
                                                    <option value="all" selected>-All Work Type-</option>
                                                    <option value="INSTAL">INSTAL</option>
                                                    <option value="REPLACEMENT EDC">REPLACEMENT EDC</option>
                                                    <option value="REPLACEMENT PART">REPLACEMENT PART</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="status_edc">Status:</label>
                                                <select class="select2" name="status_edc[]" id="status_edc" multiple="multiple" data-placeholder="Select a Status" data-dropdown-css-class="select2-purple" style="width: 100%;">
                                                    <option value="None">None</option>
                                                    <option value="Not yet used">Not yet used</option>
                                                    <option value="Used">Used</option>
                                                    <option value="Terlink">Terlink</option>
                                                    <option value="HO Santana">HO Santana</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="status_condition">Status Condition:</label>
                                                <select class="select2" name="status_condition" id="status_condition" style="width: 100%;">
                                                    <option value="all">-All Status Condition-</option>
                                                    <option value="GOOD COMPLETE (EDC baik & lengkap)">GOOD COMPLETE (EDC baik & lengkap)</option>
                                                    <option value="GOOD INCOMPLETE (EDC baik tapi tidak lengkap)">GOOD INCOMPLETE (EDC baik tapi tidak lengkap)</option>
                                                    <option value="DAMAGE COMPLETE (EDC rusak tapi lengkap)">DAMAGE COMPLETE (EDC rusak tapi lengkap)</option>
                                                    <option value="DAMAGE INCOMPLETE (EDC rusak & tidak lengkap)">DAMAGE INCOMPLETE (EDC rusak & tidak lengkap)</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="search">Search:</label>
                                                <input type="text" name="search" class="form-control" id="search" placeholder="TID, MID, Merchant, SN">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <button type="button" id="btn-export-excel" class="btn btn-success btn-sm">
                                                    <i class="fas fa-file-excel"></i> Export Excel
                                                </button>
                                                <button type="reset" class="btn btn-sm btn-dark">
                                                    Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </div>

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
        $('#btn-export-excel').on('click', function() {

            let form = $('form');

            let data = form.serialize();

            let url = "<?= base_url('export_data/export_excel') ?>";

            Swal.fire({
                title: 'Exporting...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            let exportForm = $('<form>', {
                method: 'POST',
                action: url
            });

            data.split('&').forEach(function(item) {
                let pair = item.split('=');
                exportForm.append(`<input type="hidden" name="${pair[0]}" value="${pair[1]}">`);
            });

            $('body').append(exportForm);
            exportForm.submit();

            setTimeout(() => {
                Swal.close();
            }, 1000);
        });
    </script>
</body>

</html>