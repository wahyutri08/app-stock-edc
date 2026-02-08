<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$id = $_SESSION["id"];
$role = $_SESSION['role'];
$user = query("SELECT * FROM users WHERE id = $id")[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = addStock($_POST);
    if ($result > 0) {
        echo json_encode(["status" => "success", "message" => "Data Added Successfully"]);
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

$title = "Add Stock";
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
                            <h1 class="m-0">Add Stock</h1>
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
                                    <h3 class="card-title">Add Stock EDC</h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <div class="card-body">
                                        <div class="form-group col-md-5">
                                            <label for="sn_edc">SN EDC:</label>
                                            <input type="text" name="sn_edc" class="form-control" id="sn_edc" placeholder="SN EDC">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="sn_simcard">SN Simcard:</label>
                                            <input type="text" name="sn_simcard" class="form-control" id="sn_simcard" placeholder="SN Simcard">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="sn_samcard1">SN Samcard 1:</label>
                                            <input type="text" name="sn_samcard1" class="form-control" id="sn_samcard1" placeholder="SN Samcard 1">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="sn_samcard2">SN Samcard 2:</label>
                                            <input type="text" name="sn_samcard2" class="form-control" id="sn_samcard2" placeholder="SN Samcard 2">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="sn_samcard3">SN Samcard 3:</label>
                                            <input type="text" name="sn_samcard3" class="form-control" id="sn_samcard3" placeholder="SN Samcard 3">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label>Status:</label>
                                            <select class="custom-select form-control" id="status_edc" name="status_edc">
                                                <option value="Not yet used">Not yet used</option>
                                                <option value="Used">Used</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="nama_atribut">Date:</label>
                                            <input type="date" name="date" class="form-control" id="date" value="<?= date('Y-m-d', strtotime('now')); ?>" placeholder="Date">
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-solid fa-check"></i> Submit</button>
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
            bsCustomFileInput.init();
        });
    </script>
    <script>
        $(function() {
            // Inisialisasi validasi jQuery
            $('#quickForm').validate({
                rules: {
                    status: {
                        required: true
                    },
                    date: {
                        required: true
                    }
                },
                messages: {
                    status: {
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

                if (!$(this).valid()) return; // Stop jika form tidak valid

                $.ajax({
                    url: '', // Ganti dengan URL aksi jika perlu
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
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
                        Swal.fire('Error', 'An Error Occurred on the Server', 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>