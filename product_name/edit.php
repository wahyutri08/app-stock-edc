<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    header("HTTP/1.1 403 Not Found");
    include("../errors/403.html");
    exit;
}

if (isset($_GET["id_product"]) && is_numeric($_GET["id_product"])) {
    $id_product = $_GET["id_product"];
} else {
    header("HTTP/1.1 404 Not Found");
    include("../error/error-404.html");
    exit;
}

$product = query("SELECT * FROM product_type WHERE id_product = $id_product");

if (empty($product)) {
    header("HTTP/1.1 404 Not Found");
    include("../errors/404.html");
    exit;
}

$product = $product[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = editProductName($_POST);
    if ($result > 0) {
        echo json_encode(["status" => "success", "message" => "Data Successfully Changed"]);
    } elseif ($result == -1) {
        echo json_encode(["status" => "error", "message" => "Product Name Already Exists"]);
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

$title = "Edit - {$product['name_product']}";
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
                                <li class="breadcrumb-item">Settings</li>
                                <li class="breadcrumb-item">Type Settings</li>
                                <li class="breadcrumb-item">Product Name</li>
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
                                    <h3 class="card-title"><?= $product["name_product"];  ?></h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" id="quickForm">
                                    <input type="hidden" id="id_product" name="id_product" value="<?= htmlspecialchars($product["id_product"]); ?>">
                                    <div class="card-body">
                                        <div class="form-group col-md-5">
                                            <label for="name_product">Product Name:</label>
                                            <input type="text" name="name_product" class="form-control" id="name_product" placeholder="Product Name" value="<?= htmlspecialchars($product['name_product']); ?>">
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label>Status:</label>
                                            <select class="custom-select form-control" id="status" name="status">
                                                <option value="" disabled selected>--Selected One--</option>
                                                <option value="Active" <?= ($product["status"] == "Active") ? "selected" : "" ?>>Active</option>
                                                <option value="Not Active" <?= ($product["status"] == "Not Active") ? "selected" : "" ?>>Not Active</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-solid fa-check"></i> Submit</button>
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
            bsCustomFileInput.init();
        });
    </script>
    <script>
        $(function() {
            // Inisialisasi validasi jQuery
            $('#quickForm').validate({
                rules: {
                    name_product: {
                        required: true
                    },
                    status: {
                        required: true
                    }
                },
                messages: {
                    name_product: {
                        required: "Please enter an Name Product"
                    },
                    status: {
                        required: "Please enter an Status"
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
                                window.location.href = '../product_name';
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