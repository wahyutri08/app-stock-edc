<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

if ($_SESSION["role"] !== 'Admin') {
    header("HTTP/1.1 404 Not Found");
    include("../errors/403.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = addUser($_POST);
    if ($result > 0) {
        echo json_encode(["status" => "success", "message" => "Data Added Successfully"]);
    } elseif ($result == -1) {
        echo json_encode(["status" => "error", "message" => "Username Already Exists"]);
    } elseif ($result == -2) {
        echo json_encode(["status" => "error", "message" => "Confirm Password Invalid"]);
    } elseif ($result == -3) {
        echo json_encode(["status" => "error", "message" => "Your File Not Image"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Data Failed to Change"]);
    }
    exit;
}

$title = "Add User";
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
                                <li class="breadcrumb-item">Settings</li>
                                <li class="breadcrumb-item">User Management</li>
                                <li class="breadcrumb-item active"><?= $title; ?></li>
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
                            <div class="card card-warning">
                                <div class="card-header">
                                    <span class="nav-icon fas fa-user"></span> DATA USER
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <form method="POST" action="" enctype="multipart/form-data" id="quickForm">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="username">Username:</label>
                                                    <input type="text" name="username" class="form-control" id="username" placeholder="Username">
                                                </div>
                                                <div class="form-group">
                                                    <label for="name">Name:</label>
                                                    <input type="text" name="name" class="form-control" id="name" placeholder="name">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email:</label>
                                                    <input type="email" name="email" class="form-control" id="email" placeholder="Email">
                                                </div>
                                                <div class="form-group">
                                                    <label for="no_telfon">Number Phone:</label>
                                                    <input type="number" name="no_telfon" class="form-control" id="no_telfon" placeholder="Number Phone">
                                                </div>
                                                <div class="form-group">
                                                    <label for="avatar">Photo Profile</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" class="form-control" name="avatar" id="avatar">
                                                            <label class="custom-file-label" for="avatar">Choose file</label>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">Upload</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Password:</label>
                                                    <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                                                </div>
                                                <div class="form-group">
                                                    <label for="password2">Confirm Password:</label>
                                                    <input type="password" name="password2" class="form-control" id="password2" placeholder="Confirm Password">
                                                </div>
                                                <div class="form-group">
                                                    <label>Role:</label>
                                                    <select class="custom-select form-control" id="role" name="role">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="Admin">Admin</option>
                                                        <option value="User">User</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Status:</label>
                                                    <select class="custom-select form-control" id="status" name="status">
                                                        <option value="" disabled selected>--Selected One--</option>
                                                        <option value="Active">Active</option>
                                                        <option value="Not Active">Not Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer">
                                        <button type="submit" name="submit" class="btn btn-warning"><i class="fas fa-solid fa-check"></i> Submit</button>
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
                    username: {
                        required: true
                    },
                    name: {
                        required: true
                    },
                    email: {
                        required: true
                    },
                    no_telfon: {
                        required: true
                    },
                    password: {
                        required: true
                    },
                    password2: {
                        required: true
                    },
                    role: {
                        required: true
                    },
                    status: {
                        required: true
                    }
                },
                messages: {
                    username: {
                        required: "Please enter an Username"
                    },
                    name: {
                        required: "Please enter an Name"
                    },
                    email: {
                        required: "Please enter an Email"
                    },
                    no_telfon: {
                        required: "Please enter an Number Phone"
                    },
                    password: {
                        required: "Please enter an Password"
                    },
                    password2: {
                        required: "Please enter an Confirm Password"
                    },
                    role: {
                        required: "Please enter an Role"
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
                                window.location.href = '../user_management';
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