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

$product_type = query("SELECT * FROM product_type WHERE status = 'Active'");
$color_type = query("SELECT * FROM color_type WHERE status = 'Active'");


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
        echo json_encode(["status" => "error", "message" => "Data Failed to Submit"]);
    }
    exit;
}

$title = "Add Data Stock";
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
                                <li class="breadcrumb-item">My Assets</li>
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
                                    <h3 class="card-title"> <i class="nav-icon fas fa-edit"></i> Add Data Stock</h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <div class="card-body">
                                        <div id="formContainer">
                                            <div class="form-item border rounded p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Product Type:</label>
                                                            <select class="form-control select2" name="id_product_name[]" style="width:100%">
                                                                <option value="" disabled selected>--Selected One--</option>
                                                                <?php foreach ($product_type as $product) : ?>
                                                                    <option value="<?= $product["id_product"]; ?>">
                                                                        <?= $product["name_product"]; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Color Type:</label>
                                                            <select class="form-control select2" name="id_edc_color[]" style="width:100%">
                                                                <option value="" disabled selected>--Selected One--</option>
                                                                <?php foreach ($color_type as $color) : ?>
                                                                    <option value="<?= $color["id_color"]; ?>">
                                                                        <?= $color["name_color"]; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>SN EDC:</label>
                                                            <input type="text" name="sn_edc[]" class="form-control" placeholder="SN EDC">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>SN Simcard:</label>
                                                            <input type="text" name="sn_simcard[]" class="form-control" placeholder="SN Simcard">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>SN Samcard 1:</label>
                                                            <input type="text" name="sn_samcard1[]" class="form-control" placeholder="SN Samcard 1">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>SN Samcard 2:</label>
                                                            <input type="text" name="sn_samcard2[]" class="form-control" placeholder="SN Samcard 2">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>SN Samcard 3:</label>
                                                            <input type="text" name="sn_samcard3[]" class="form-control" placeholder="SN Samcard 3">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Status:</label>
                                                            <select class="custom-select form-control" name="status_edc[]">
                                                                <option value="" disabled selected>--Selected One--</option>
                                                                <option value="Not yet used">Not yet used</option>
                                                                <option value="Used">Used</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Date Pickup:</label>
                                                            <input type="date" name="date_pickup[]" class="form-control" value="<?= date('Y-m-d'); ?>">
                                                        </div>

                                                        <button type="button" class="btn btn-danger removeForm ml-1"> <i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-solid fa-check"></i> Submit</button>
                                        <button type="button" id="addForm" class="btn btn-warning ml-1"><i class="fas fa-solid fa-plus"></i> Add Data</button>
                                        <button type="reset" class="btn btn-dark ml-1"> Reset</button>
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
        $(document).ready(function() {

            $("#addForm").click(function() {

                $('.select2').each(function() {
                    if ($(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2('destroy');
                    }
                });

                let newForm = $(".form-item:first").clone();

                // kosongkan input text
                newForm.find('input[type="text"]').val("");

                // reset select
                newForm.find("select").prop("selectedIndex", 0);

                // set tanggal hari ini (LOCAL TIME)
                let today = new Date();
                let yyyy = today.getFullYear();
                let mm = String(today.getMonth() + 1).padStart(2, '0');
                let dd = String(today.getDate()).padStart(2, '0');

                let currentDate = yyyy + '-' + mm + '-' + dd;

                newForm.find('input[name="date_pickup[]"]').val(currentDate);

                $("#formContainer").append(newForm);

                $('.select2').select2();
            });
            // remove form
            $(document).on("click", ".removeForm", function() {

                if ($(".form-item").length > 1) {
                    $(this).closest(".form-item").remove();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'At Least One Form Must Exist'
                    });
                }

            });

        });
    </script>
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

            $('#quickForm').on('submit', function(e) {

                e.preventDefault();

                let valid = true;

                $('.form-item').each(function() {

                    let sn_edc = $(this).find('input[name="sn_edc[]"]').val().trim();
                    let sn_simcard = $(this).find('input[name="sn_simcard[]"]').val().trim();
                    let sn_samcard1 = $(this).find('input[name="sn_samcard1[]"]').val().trim();
                    let sn_samcard2 = $(this).find('input[name="sn_samcard2[]"]').val().trim();
                    let sn_samcard3 = $(this).find('input[name="sn_samcard3[]"]').val().trim();

                    let status = $(this).find('select[name="status_edc[]"]').val();
                    let date = $(this).find('input[name="date_pickup[]"]').val();

                    // cek apakah ada SN yang diisi
                    if (
                        sn_edc !== "" ||
                        sn_simcard !== "" ||
                        sn_samcard1 !== "" ||
                        sn_samcard2 !== "" ||
                        sn_samcard3 !== ""
                    ) {

                        // jika SN diisi tapi status atau date kosong
                        if (!status || !date) {

                            valid = false;

                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: 'Status and Date Pickup must be filled if SN is entered!'
                            });

                            return false;
                        }

                    }

                });

                if (!valid) return;

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
                                window.location.href = '<?= base_url('all_data') ?>';
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