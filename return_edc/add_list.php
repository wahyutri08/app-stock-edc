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

$title = "Add List Return";
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
                            <h1 class="m-0">Add List Return</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
                                <li class="breadcrumb-item">Return EDC</li>
                                <li class="breadcrumb-item">List Return EDC</li>
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
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Add List Return EDC</h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sn_edc">SN EDC:</label>
                                                    <input type="text" name="sn_edc" class="form-control" id="sn_edc" placeholder="SN EDC">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_simcard">SN Simcard:</label>
                                                    <input type="text" name="sn_simcard" class="form-control" id="sn_simcard" placeholder="SN Simcard">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard1">SN Samcard 1:</label>
                                                    <input type="text" name="sn_samcard1" class="form-control" id="sn_samcard1" placeholder="SN Samcard 1">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard2">SN Samcard 2:</label>
                                                    <input type="text" name="sn_samcard2" class="form-control" id="sn_samcard2" placeholder="SN Samcard 2">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sn_samcard3">SN Samcard 3:</label>
                                                    <input type="text" name="sn_samcard3" class="form-control" id="sn_samcard3" placeholder="SN Samcard 3">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status:</label>
                                                    <select class="custom-select form-control" id="status1" name="status1">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="Technician">Technician</option>
                                                        <option value="HO">HO</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Status Condition:</label>
                                                    <select class="form-control select2 select2-primary" id="status2" name="status2" data-dropdown-css-class="select2-primary" style="width: 100%;">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="EDC Normal / Lengkap">EDC Normal / Lengkap</option>
                                                        <option value="EDC Normal / TIdak Lengkap">EDC Normal / TIdak Lengkap</option>
                                                        <option value="EDC Rusak / Lengkap">EDC Rusak / Lengkap</option>
                                                        <option value="EDC Rusak / TIdak Lengkap">EDC Rusak / TIdak Lengkap</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="date">Date:
                                                        <span class="text-danger small font-italic">(*Opsional: Jika Ingin Langsung Dikembalikan Ke HO)</span>
                                                    </label>
                                                    <input type="date" name="date" class="form-control" id="date" placeholder="Date" value="<?= $stock["date"]; ?>">
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
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-solid fa-check"></i> Submit</button>
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
                    status1: {
                        required: true
                    },
                    status2: {
                        required: true
                    },
                    date: {
                        required: true
                    }
                },
                messages: {
                    status2: {
                        required: "Please enter an Status"
                    },
                    status2: {
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