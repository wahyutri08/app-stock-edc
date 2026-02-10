<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}


$id = $_SESSION["id"];
$users = query("SELECT * FROM users WHERE id = $id")[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = editProfile($_POST);
    if ($result > 0) {
        echo json_encode(["status" => "success", "message" => "Data Successfully Changed"]);
    } elseif ($result == -1) {
        echo json_encode(["status" => "error", "message" => "File is not an image format"]);
    } elseif ($result == -2) {
        echo json_encode(["status" => "error", "message" => "Image Size Too Large"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Data Failed to Change"]);
    }
    exit;
}

$title = "Profile - {$users['name']}";

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
                            <h1 class="m-0">Dashboard</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
                                <li class="breadcrumb-item">Settings</li>
                                <li class="breadcrumb-item">Account</li>
                                <li class="breadcrumb-item">Profile</li>
                                <li class="breadcrumb-item active"><?= $users["name"]; ?></li>
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
                        <div class="col-md-3">

                            <!-- Profile Image -->
                            <div class="card card-success card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center">
                                        <img class="profile-user-img img-fluid img-circle"
                                            src="../assets/dist/img/profile/<?= $users["avatar"]; ?>"
                                            style="width: 160px; height: 160px;">
                                    </div>
                                    <h3 class="profile-username text-center"><?= $users["name"]; ?></h3>
                                    <hr>
                                    <p class="text-muted text-center"><?= $users["role"]; ?></p>
                                </div>
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                        <div class="col-md-9">
                            <div class="card card-success card-outline">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item">
                                            <button class="btn btn-success" data-toggle="tab">Settings</button>
                                        </li>
                                    </ul>
                                </div><!-- /.card-header -->
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="active tab-pane" id="settings">
                                            <form class="form-horizontal" action="" method="POST" enctype="multipart/form-data" id="myForm">
                                                <input type="hidden" name="id" value="<?= $users["id"]; ?>">
                                                <input type="hidden" name="avatarLama" value="<?= $users["avatar"]; ?>">
                                                <div class="form-group row">
                                                    <label for="username" class="col-sm-2 col-form-label">Username <span class="text-danger">*</span></label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" placeholder="Username" value="<?= $users["username"]; ?>" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="<?= $users["name"]; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="email" class="col-sm-2 col-form-label">Email <span class="text-danger">*</span></label>
                                                    <div class="col-sm-10">
                                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= $users["email"]; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="no_telfon" class="col-sm-2 col-form-label">No Telefon <span class="text-danger">*</span></label>
                                                    <div class="col-sm-10">
                                                        <input type="number" class="form-control" id="no_telfon" name="no_telfon" placeholder="Number Phone " value="<?= $users["no_telfon"]; ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="avatar" class="col-sm-2 col-form-label">Photo Profile</label>
                                                    <div class="input-group col-sm-10">
                                                        <div class="custom-file">
                                                            <input type="file" class="form-control" name="avatar" id="avatar">
                                                            <label class="custom-file-label" for="avatar">Choose file</label>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">Upload</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="offset-sm-2 col-sm-10">
                                                        <button type="submit" name="submit" class="btn btn-success float-right"><i class="fas fa-solid fa-check"></i> Save Changes</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- /.tab-pane -->
                                    </div>
                                    <!-- /.tab-content -->
                                </div><!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
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
            $('#myForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire({
                                title: "Success",
                                text: res.message,
                                icon: "success"
                            }).then(() => {
                                window.location.href = '../profile';
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>