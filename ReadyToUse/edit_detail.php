<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$user_id = $_SESSION['id'];
$role    = $_SESSION['role'];

$id_stock = (int)($_GET['id_stock'] ?? 0);
if ($id_stock <= 0) {
    http_response_code(404);
    exit;
}

/* ================= LOAD DATA ================= */
if ($role === 'Admin') {
    $stock = query("
        SELECT 
            stock.*,
            users.name,
            detail_list_stock.tid,
            detail_list_stock.mid,
            detail_list_stock.merchant_name,
            detail_list_stock.addres_name,
            detail_list_stock.date,
            detail_list_stock.note
        FROM stock
        JOIN users ON stock.user_id = users.id
        LEFT JOIN detail_list_stock 
            ON stock.id_stock = detail_list_stock.stock_id
        WHERE stock.id_stock = $id_stock
    ");
} else {
    $stock = query("
        SELECT stock.*, users.name
        FROM stock
        JOIN users ON stock.user_id = users.id
        WHERE stock.id_stock = $id_stock
        AND stock.user_id = $user_id
    ");
}

if (!$stock) {
    http_response_code(404);
    exit;
}

$stock = $stock[0];
$users = query("SELECT * FROM users");

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
require_once '../partials/header.php';

?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
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
                                <li class="breadcrumb-item">Status EDC</li>
                                <li class="breadcrumb-item">Ready To Use</li>
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
                                    <h3 class="card-title"><?= $title; ?></h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <input type="hidden" name="stock_id" id="stock_id" value="<?= $stock["id_stock"]; ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sn_edc">SN EDC:</label>
                                                    <input type="text" name="sn_edc" class="form-control" id="sn_edc" placeholder="SN EDC" value="<?= $stock["sn_edc"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_simcard">SN Simcard:</label>
                                                    <input type="text" name="sn_simcard" class="form-control" id="sn_simcard" placeholder="SN Simcard" value="<?= $stock["sn_simcard"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard1">SN Samcard 1:</label>
                                                    <input type="text" name="sn_samcard1" class="form-control" id="sn_samcard1" placeholder="SN Samcard 1" value="<?= $stock["sn_samcard1"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard2">SN Samcard 2:</label>
                                                    <input type="text" name="sn_samcard2" class="form-control" id="sn_samcard2" placeholder="SN Samcard 2" value="<?= $stock["sn_samcard2"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard3">SN Samcard 3:</label>
                                                    <input type="text" name="sn_samcard3" class="form-control" id="sn_samcard3" placeholder="SN Samcard 3" value="<?= $stock["sn_samcard3"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="tid">TID:</label>
                                                    <input type="text" name="tid" class="form-control" id="tid" placeholder="TID" value="<?= $stock["tid"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="mid">MID:</label>
                                                    <input type="text" name="mid" class="form-control" id="mid" placeholder="MID" value="<?= $stock["mid"]; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="merchant_name">Merchant Name:</label>
                                                    <input type="text" name="merchant_name" class="form-control" id="merchant_name" placeholder="Merchant Name" value="<?= $stock["merchant_name"]; ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="addres_name">Address:</label>
                                                    <input type="text" name="addres_name" class="form-control" id="addres_name" placeholder="Address Name" value="<?= $stock["addres_name"]; ?>">
                                                </div>
                                                <?php if (!empty($stock['date'])) : ?>
                                                    <div class="form-group">
                                                        <label for="date">Date:</label>
                                                        <input type="date" name="date" class="form-control" id="date" placeholder="Date" value="<?= date('Y-m-d', strtotime($stock['date'])); ?>">
                                                    </div>
                                                <?php else : ?>
                                                    <div class="form-group">
                                                        <label for="date">Date:</label>
                                                        <input type="date" name="date" class="form-control" id="date" placeholder="Date" value="">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="form-group">
                                                    <label for="status_edc">Status:</label>
                                                    <select class="custom-select form-control" id="status_edc" name="status_edc">
                                                        <option value="Not yet used" <?= ($stock['status_edc'] == 'Not yet used') ? 'selected' : '' ?>>
                                                            Not yet used
                                                        </option>
                                                        <option value="Used" <?= ($stock['status_edc'] == 'Used') ? 'selected' : '' ?>>
                                                            Used
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="user_id">User:</label>
                                                    <select class="form-control select2 select2-danger" id="user_id" name="user_id" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                                        <?php foreach ($users as $user) : ?>
                                                            <option value="<?= $user["id"]; ?>"
                                                                <?= ($stock["user_id"] == $user["id"]) ? "selected" : "" ?>>
                                                                <?= $user["name"]; ?>
                                                            </option>
                                                        <?php endforeach; ?>
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
                                        <button type="submit" name="submit" class="btn btn-sm btn-success mr-1"><i class="fas fa-solid fa-check"></i> Save Change</button>
                                        <button type="reset" class="btn btn-sm btn-dark">Reset</button>
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
            // Inisialisasi validasi jQuery
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

            // Submit dengan AJAX hanya jika valid
            $('#quickForm').on('submit', function(e) {
                e.preventDefault();

                if (!$(this).valid()) return; // Stop jika form tidak valid

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    dataType: 'json', // 🔥 PENTING
                    success: function(res) {

                        if (res.status === 'success') {
                            Swal.fire('Success', res.message, 'success')
                                .then(() => window.location.href = '../ReadyToUse');
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText); // 🔥 DEBUG
                        Swal.fire('Error', 'Server Error', 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>