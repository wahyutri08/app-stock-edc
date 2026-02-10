<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];

if ($role == 'Admin') {
    $return = query("SELECT * FROM return_edc
               JOIN users
               ON return_edc.user_id = users.id
               WHERE status1 = 'HO'");
} elseif ($role == 'User') {
    $return = query("SELECT * FROM return_edc
               JOIN users
               ON return_edc.user_id = users.id 
               WHERE status1 = 'HO' AND user_id = $user_id");
}

$title = "HO";
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
                                <li class="breadcrumb-item">Return EDC</li>
                                <li class="breadcrumb-item"><?= $title;  ?></li>
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
                            <div class="card card-outline card-success">
                                <?php if ($role === 'Admin') : ?>
                                    <div class="card-header text-left">
                                        <a href="#" id="btnTechnician" data-status="Technician"
                                            class="btn btn-action btn-sm bg-gradient-primary disabled mr-2">
                                            <i class="fas fa-check"></i> Back To Technician
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table id="example1" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <?php if ($role === 'Admin') : ?>
                                                    <th class="text-center" style="width: 8px;">
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input custom-control-input-danger"
                                                                type="checkbox" id="checkAll">
                                                            <label for="checkAll" class="custom-control-label"></label>
                                                        </div>
                                                    </th>
                                                <?php endif; ?>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">SN EDC</th>
                                                <th class="text-center">Simcard</th>
                                                <th class="text-center">Samcard1</th>
                                                <th class="text-center">Samcard2</th>
                                                <th class="text-center">Samcard3</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Status Condition</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Note</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($return as $i => $row) : ?>
                                                <tr>
                                                    <td class="text-center" style="width: 8px;">
                                                        <?php if ($role === 'Admin') : ?>
                                                            <div class="custom-control custom-checkbox">
                                                                <input
                                                                    type="checkbox"
                                                                    class="custom-control-input custom-control-input-danger checkbox-item"
                                                                    id="check<?= $row['id_return']; ?>"
                                                                    value="<?= $row['id_return']; ?>"
                                                                    data-idstatus="<?= $row['id_return']; ?>">
                                                                <label for="check<?= $row['id_return']; ?>" class="custom-control-label"></label>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center"><?= $row["name"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_edc"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_simcard"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard1"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard2"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard3"]; ?></td>
                                                    <td class="text-center"><?= $row["status1"]; ?></td>
                                                    <td class="text-center"><?= $row["status2"]; ?></td>
                                                    <td class="text-center"><?= $row["date"]; ?></td>
                                                    <td class="text-center"><?= $row["note"]; ?></td>
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
                <?php if ($role === 'Admin') : ?> "buttons": ["excel", "print", "colvis"]
                <?php endif; ?>
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
                    $('#btnDeleteReturn, #btnHO, #btnTechnician').removeClass('disabled');
                } else {
                    $('#btnDeleteReturn, #btnHO, #btnTechnician').addClass('disabled');
                }
            }

            // 🗑️ CLICK DELETE (CEGAH JIKA DISABLED)
            $('#btnDeleteReturn').on('click', function(e) {
                if ($(this).hasClass('disabled')) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                let listIds = [];
                $('.checkbox-item:checked').each(function() {
                    listIds.push($(this).val());
                });

                Swal.fire({
                    title: 'Are You Sure?',
                    text: listIds.length + ' Data Will be Deleted',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_return_bulk.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                listIds: listIds
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Deleted!', response.message, 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error', 'Server error', 'error');
                            }
                        });
                    }
                });
            });
            $('.btn-action').on('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled')) return;

                let status = $(this).data('status');
                let idStatus = [];

                $('.checkbox-item:checked').each(function() {
                    idStatus.push($(this).data('idstatus')); // ⬅️ PENTING
                });

                Swal.fire({
                    title: 'Are You Sure?',
                    text: idStatus.length + ' Data Will Be Changed',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Change!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'edit_status_bulk.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                idStatus: idStatus,
                                status: status
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Success', response.message, 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
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