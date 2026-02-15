<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];


if (isset($_GET["id_stock"]) && is_numeric($_GET["id_stock"])) {
    $id_stock = $_GET["id_stock"];
} else {
    header("HTTP/1.1 404 Not Found");
    include("../error/error-404.html");
    exit;
}

if ($role == 'Admin') {
    $stock = query("SELECT stock.*,
                    IF(users.name IS NULL, 'Deleted User', users.name) AS name
                    FROM stock
                    LEFT JOIN users
                    ON stock.user_id = users.id
                    WHERE stock.id_stock = $id_stock");
} else {
    $stock = query("SELECT * FROM stock 
                    JOIN users
                    ON stock.user_id = users.id 
                    WHERE id_stock = $id_stock 
                    AND user_id = $user_id");
}

if (empty($stock)) {
    header("HTTP/1.1 404 Not Found");
    include("../errors/404.html");
    exit;
}

$stock = $stock[0];
$users = query("SELECT * FROM users");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = editStock($_POST);
    if ($result > 0) {
        echo json_encode(["status" => "success", "message" => "Data Successfully Changed"]);
    } elseif ($result == -1) {
        echo json_encode(["status" => "error", "message" => "SN EDC Already Exists"]);
    } elseif ($result == -2) {
        echo json_encode(["status" => "error", "message" => "SN SIMCARD Already Exists"]);
    } elseif ($result == -3) {
        echo json_encode(["status" => "error", "message" => "SN SAMCARD 1 Already Exists"]);
    } elseif ($result == -4) {
        echo json_encode(["status" => "error", "message" => "SN SAMCARD 2 Already Exists"]);
    } elseif ($result == -5) {
        echo json_encode(["status" => "error", "message" => "SN SAMCARD 3 Already Exists"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Data Failed to Change"]);
    }
    exit;
}

$title = "Edit Data";
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
                            <h1 class="m-0">Edit Stock</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
                                <li class="breadcrumb-item">My Assets</li>
                                <li class="breadcrumb-item">List Stock EDC</li>
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
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">Edit Stock EDC</h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <input type="hidden" name="id_stock" id="id_stock" value="<?= $stock["id_stock"]; ?>">
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
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status:</label>
                                                    <select class="custom-select form-control" id="status_edc" name="status_edc">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="Not yet used" <?= ($stock['status_edc'] == 'Not yet used') ? 'selected' : '' ?>>
                                                            Not yet used
                                                        </option>
                                                        <option value="Used" <?= ($stock['status_edc'] == 'Used') ? 'selected' : '' ?>>
                                                            Used
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>User:</label>
                                                    <select class="form-control select2 select2-danger" id="user_id" name="user_id" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <?php foreach ($users as $user) : ?>
                                                            <option value="<?= $user["id"]; ?>"
                                                                <?= ($stock["user_id"] == $user["id"]) ? "selected" : "" ?>>
                                                                <?= $user["name"]; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="date_pickup">Date Pickup:</label>
                                                    <input type="date" name="date_pickup" class="form-control" id="date_pickup" placeholder="Date" value="<?= $stock["date_pickup"]; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-solid fa-check"></i> Submit</button>
                                        <button type="reset" class="btn btn-dark"> Reset</button>
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
                    status_edc: {
                        required: true
                    },
                    date: {
                        required: true
                    }
                },
                messages: {
                    status_edc: {
                        required: "Please enter an Status"
                    },
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

                // Ambil semua input text
                let sn_edc = $('#sn_edc').val().trim();
                let sn_simcard = $('#sn_simcard').val().trim();
                let sn_samcard1 = $('#sn_samcard1').val().trim();
                let sn_samcard2 = $('#sn_samcard2').val().trim();
                let sn_samcard3 = $('#sn_samcard3').val().trim();

                // 🔥 CEK JIKA SEMUA KOSONG
                if (
                    sn_edc === "" &&
                    sn_simcard === "" &&
                    sn_samcard1 === "" &&
                    sn_samcard2 === "" &&
                    sn_samcard3 === ""
                ) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'At least one field must be filled!'
                    });
                    return;
                }

                if (!$(this).valid()) return;

                // 🔥 MUNCULKAN OVERLAY LANGSUNG
                $('#pageLoader').show();
                $('button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,

                    success: function(response) {

                        $('#pageLoader').hide();
                        $('button[type="submit"]').prop('disabled', false);

                        let res;
                        try {
                            res = JSON.parse(response);
                        } catch (e) {
                            Swal.fire('Error', 'Invalid Server Response', 'error');
                            return;
                        }

                        if (res.status === 'success') {
                            Swal.fire({
                                title: "Success",
                                text: res.message,
                                icon: "success"
                            }).then(() => {
                                window.location.href = '../listStockEdc';
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },

                    error: function() {
                        $('#pageLoader').hide();
                        $('button[type="submit"]').prop('disabled', false);

                        Swal.fire('Error', 'An Error Occurred on the Server', 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>