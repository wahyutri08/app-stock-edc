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
    $stck = query("SELECT stock.*, users.name, detail_list_stock.*
                   FROM stock
                   JOIN users 
                   ON stock.user_id = users.id
                   LEFT JOIN detail_list_stock 
                   ON stock.id_stock = detail_list_stock.stock_id
                   WHERE stock.status_edc = 'Not yet used'");
} elseif ($role == 'User') {
    $stck = query("SELECT stock.*, users.name, detail_list_stock.*
                   FROM stock
                   JOIN users 
                   ON stock.user_id = users.id
                   LEFT JOIN detail_list_stock 
                   ON stock.id_stock = detail_list_stock.stock_id
                   WHERE stock.status_edc = 'Not yet used' AND user_id = $user_id");
}

$title = "Ready To Use";
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
                                <li class="breadcrumb-item">Status EDC</li>
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
                                <div class="card-header text-left">
                                    <a href="#" id="btnDelete" class="btn btn-sm bg-gradient-warning disabled mr-2">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                    <a href="#" id="btnNotyetused" data-status="Not yet used"
                                        class="btn btn-action btn-sm bg-gradient-primary disabled mr-2">
                                        <i class="fas fa-times"></i> Not yet Used
                                    </a>
                                    <a href="#" id="btnUsed" data-status="Used"
                                        class="btn btn-action btn-sm bg-gradient-success disabled">
                                        <i class="fas fa-check"></i> Used
                                    </a>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table id="example1" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input custom-control-input-danger"
                                                            type="checkbox" id="checkAll">
                                                        <label for="checkAll" class="custom-control-label"></label>
                                                    </div>
                                                </th>
                                                <th class="text-center">No</th>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">SN EDC</th>
                                                <th class="text-center">Simcard</th>
                                                <th class="text-center">Samcard1</th>
                                                <th class="text-center">Samcard2</th>
                                                <th class="text-center">Samcard3</th>
                                                <th class="text-center">TID</th>
                                                <th class="text-center">MID</th>
                                                <th class="text-center">Merchant</th>
                                                <th class="text-center">Address</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Note</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stck as $i => $row) : ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input
                                                                type="checkbox"
                                                                class="custom-control-input custom-control-input-danger checkbox-item"
                                                                id="check<?= $row['stock_id']; ?>"
                                                                value="<?= $row['stock_id']; ?>"
                                                                data-idstock="<?= $row['id_stock']; ?>">
                                                            <label for="check<?= $row['stock_id']; ?>" class="custom-control-label"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><?= $i + 1; ?></td>
                                                    <td class="text-center"><?= $row["name"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_edc"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_simcard"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard1"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard2"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard3"]; ?></td>
                                                    <td class="text-center"><?= $row["tid"]; ?></td>
                                                    <td class="text-center"><?= $row["mid"]; ?></td>
                                                    <td class="text-center"><?= $row["merchant_name"]; ?></td>
                                                    <td class="text-center"><?= $row["addres_name"]; ?></td>
                                                    <td class="text-center"><?= $row["date"]; ?></td>
                                                    <?php if ($row["status_edc"] == "Not yet used") : ?>
                                                        <td class="text-center"><span class="badge bg-primary"><?= $row["status_edc"]; ?></span></td>
                                                    <?php else : ?>
                                                        <td class="text-center"><span class="badge bg-success"><?= $row["status_edc"]; ?></span></td>
                                                    <?php endif; ?>
                                                    <td class="text-center"><?= $row["note"]; ?></td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
                                                                Action
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="dropdown-item" href="edit_detail.php?id_stock=<?= $row["id_stock"]; ?>"><i class="fas fa-edit"></i> Edit</a></li>
                                                                <li><a class="dropdown-item tombol-hapus" href="delete_detail.php?stock_id=<?= $row["stock_id"]; ?>"><i class="far fa-trash-alt"></i> Delete</a></li>
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