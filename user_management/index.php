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

$users = query("SELECT * FROM users");

$title = "User Management";
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
                            <h1 class="m-0"><?= $title;  ?></h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
                                <li class="breadcrumb-item">Settings</li>
                                <li class="breadcrumb-item active"><?= $title;  ?></li>
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
                            <div class="card card-outline card-warning">
                                <div class="card-header text-left">
                                    <a href="../add_user" class="btn btn-sm bg-warning mr-2">
                                        <i class="fas fa-plus"></i> Add User
                                    </a>
                                    <a href="#" id="btnDelete" class="btn btn-sm bg-danger disabled">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table id="example1" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 8px;">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input custom-control-input-danger"
                                                            type="checkbox" id="checkAll">
                                                        <label for="checkAll" class="custom-control-label"></label>
                                                    </div>
                                                </th>
                                                <th class="text-center">Username</th>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Email</th>
                                                <th class="text-center">No Telefon</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Role</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $i => $row) : ?>
                                                <tr>
                                                    <td class="text-center" style="width: 8px;">
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input custom-control-input-danger checkbox-item"
                                                                type="checkbox"
                                                                id="check<?= $row['id']; ?>"
                                                                value="<?= $row['id']; ?>">
                                                            <label for="check<?= $row['id']; ?>" class="custom-control-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><?= $row["username"]; ?></td>
                                                    <td class="text-center"><?= $row["name"]; ?></td>
                                                    <td class="text-center"><?= $row["email"]; ?></td>
                                                    <td class="text-center"><?= $row["no_telfon"]; ?></td>
                                                    <?php if ($row["status"] == "Active") : ?>
                                                        <td class="text-center"><span class="badge bg-success">Active</span></td>
                                                    <?php else : ?>
                                                        <td class="text-center"><span class="badge bg-danger">Not active</span></td>
                                                    <?php endif; ?>
                                                    <?php if ($row["role"] === "Admin") : ?>
                                                        <td class="text-center"><span class="badge bg-primary">Admin</span></td>
                                                    <?php else : ?>
                                                        <td class="text-center"><span class="badge bg-warning">User</span></td>
                                                    <?php endif; ?>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
                                                                Action
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="dropdown-item" href="edit_user.php?id=<?= $row["id"]; ?>"><i class="fas fa-edit"></i> Edit</a></li>
                                                                <li><a class="dropdown-item tombol-hapus" href="delete_users.php?id=<?= $row["id"]; ?>"><i class="far fa-trash-alt"></i> Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
            $("#example1").DataTable({
                "paging": true,
                "lengthChange": true,
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": false,
                "buttons": ["excel", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
    <script>
        $(document).ready(function() {

            // CHECK ALL
            $('#checkAll').on('change', function() {
                $('.checkbox-item').prop('checked', this.checked);
                toggleDeleteButton();
            });

            // CHECK SATUAN
            $(document).on('change', '.checkbox-item', function() {
                $('#checkAll').prop(
                    'checked',
                    $('.checkbox-item:checked').length === $('.checkbox-item').length
                );
                toggleDeleteButton();
            });

            // 🔥 TOGGLE CLASS DISABLED
            function toggleDeleteButton() {
                if ($('.checkbox-item:checked').length > 0) {
                    $('#btnDelete').removeClass('disabled');
                } else {
                    $('#btnDelete').addClass('disabled');
                }
            }

            // 🗑️ CLICK DELETE (CEGAH JIKA DISABLED)
            $('#btnDelete').on('click', function(e) {
                if ($(this).hasClass('disabled')) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                let ids = [];
                $('.checkbox-item:checked').each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Are You Sure?',
                    text: ids.length + ' Data Will be Deleted',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_user_bulk.php',
                            type: 'POST',
                            data: {
                                ids: ids
                            },
                            success: function(res) {
                                let response = JSON.parse(res);

                                if (response.status === 'success') {
                                    Swal.fire('Deleted!', response.message, 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Server error', 'error');
                            }
                        });
                    }
                });
            });

        });
    </script>
    <script>
        $(document).on('click', '.tombol-hapus', function(e) {
            e.preventDefault();

            const href = $(this).attr('href');

            Swal.fire({
                title: 'Are you sure?',
                text: "Data will be deleted",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: href,
                        type: 'GET',
                        success: function(response) {
                            let res = JSON.parse(response);

                            if (res.status === 'success') {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Data Successfully Deleted',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else if (res.status === 'error') {
                                Swal.fire('Error', 'Data Deletion Failed', 'error');
                            } else if (res.status === 'redirect') {
                                window.location.href = '../login';
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Server Error', 'error');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>