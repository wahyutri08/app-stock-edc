<?php
session_start();
include_once("../../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../../login");
    exit;
}

$id = $_SESSION["id"];
$role = $_SESSION['role'];
$user = query("SELECT * FROM users WHERE id = $id")[0];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = addListFkm($_POST);
    if ($result > 0) {
        echo json_encode(["status" => "success", "message" => "Data Added Successfully"]);
    } elseif ($result == -1) {
        echo json_encode(["status" => "error", "message" => "TID Already Exists"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Data Failed to Submit"]);
    }
    exit;
}

$title = "Add Data";
require_once '../../partials/header.php';

?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <?php include '../../partials/overlay.php'; ?>
    <div class="wrapper">

        <!-- Navbar -->
        <?php include '../../partials/navbar.php'; ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php include '../../partials/sidebar.php'; ?>

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
                                <li class="breadcrumb-item"><a href="../../dashboard">Home</a></li>
                                <li class="breadcrumb-item">FKM Thermal</li>
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
                                    <h3 class="card-title"> <i class="nav-icon fas fa-edit"></i> <?= $title;  ?></h3>
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
                                                            <label for="tid">TID:</label>
                                                            <input type="text" name="tid[]" id="tid" class="form-control" placeholder="TID">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="mid">MID:</label>
                                                            <input type="text" name="mid[]" id="mid" class="form-control" placeholder="MID">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="nama_merchant">Nama Merchant:</label>
                                                            <input type="text" name="nama_merchant[]" id="nama_merchant" class="form-control" placeholder="Nama Merchant">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="alamat">Alamat:</label>
                                                            <textarea class="form-control" id="alamat" name="alamat[]" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Status:</label>
                                                            <select class="custom-select form-control" name="status_merchant[]">
                                                                <option value="Active" selected>Active</option>
                                                                <option value="Not Active">Not Active</option>
                                                            </select>
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
        <?php include '../../partials/footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <?php require_once '../../partials/scripts.php'; ?>

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

                // reset semua input
                newForm.find('input[type="text"]').val("");
                newForm.find('textarea').val(""); // 🔥 ini yang penting
                newForm.find("select").prop("selectedIndex", 0);

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

                $('.form-item').each(function(index) {

                    let tid = $(this).find('input[name="tid[]"]').val().trim();
                    let mid = $(this).find('input[name="mid[]"]').val().trim();
                    let alamat = $(this).find('textarea[name="alamat[]"]').val().trim();
                    let status_merchant = $(this).find('select[name="status_merchant[]"]').val();

                    // validasi wajib isi
                    if (tid === "" || mid === "" || alamat === "" || !status_merchant) {

                        valid = false;

                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning',
                            text: 'TID, MID, Alamat, dan Status wajib diisi pada semua form!'
                        });

                        return false; // stop looping
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
                                window.location.href = '<?= base_url('fkm_thermal') ?>';
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