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
                   WHERE stock.status_edc = 'Used'");
} elseif ($role == 'User') {
    $stck = query("SELECT stock.*, users.name, detail_list_stock.*
                   FROM stock
                   JOIN users 
                   ON stock.user_id = users.id
                   LEFT JOIN detail_list_stock 
                   ON stock.id_stock = detail_list_stock.stock_id
                   WHERE stock.status_edc = 'Used' AND user_id = $user_id");
}

$title = "Already Used";
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
                                <?php if ($role === 'Admin') : ?>
                                    <div class="card-header text-left">
                                        <a href="#" id="btnNotyetused" data-status="Not yet used"
                                            class="btn btn-action btn-sm bg-gradient-primary disabled">
                                            <i class="nav-icon fas fa-sign-out-alt"></i> Rollback
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
                                                <th class="text-center">TID</th>
                                                <th class="text-center">MID</th>
                                                <th class="text-center">Merchant</th>
                                                <th class="text-center">Date Pickup</th>
                                                <th class="text-center">Date Used</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Note</th>
                                                <?php if ($role === 'Admin') : ?>
                                                    <th class="text-center">Action</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stck as $i => $row) : ?>
                                                <tr>
                                                    <?php if ($role === 'Admin') : ?>
                                                        <td class="text-center" style="width: 8px;">
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
                                                    <?php endif; ?>
                                                    <td class="text-center"><?= $row["name"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_edc"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_simcard"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard1"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard2"]; ?></td>
                                                    <td class="text-center"><?= $row["sn_samcard3"]; ?></td>
                                                    <td class="text-center"><?= $row["tid"]; ?></td>
                                                    <td class="text-center"><?= $row["mid"]; ?></td>
                                                    <td class="text-center"><?= $row["merchant_name"]; ?>
                                                        <h6 style="font-size:smaller;"><?= $row["addres_name"]; ?></h6>
                                                    </td>
                                                    <td class="text-center"><?= $row["date_pickup"]; ?></td>
                                                    <td class="text-center"><?= $row["date_used"]; ?></td>
                                                    <td class="text-center"><span class="badge bg-success"><?= $row["status_edc"]; ?></span></td>
                                                    <td class="text-center"><?= $row["note"]; ?></td>
                                                    <?php if ($role === 'Admin') : ?>
                                                        <?php if (is_null($row["id_detail"])) : ?>
                                                            <td class="text-center">
                                                                <a href="edit_detail_merchant.php?id_stock=<?= $row["id_stock"]; ?>"><button class="btn btn-sm btn-success"><i class="fas fa-edit"></i></button></a>
                                                            </td>
                                                        <?php else: ?>
                                                            <td></td>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
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
                    $('#btnNotyetused').removeClass('disabled');
                } else {
                    $('#btnNotyetused').addClass('disabled');
                }
            }
            $('.btn-action').on('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled')) return;

                let status = $(this).data('status');
                let idStocks = [];

                $('.checkbox-item:checked').each(function() {
                    idStocks.push($(this).data('idstock')); // ⬅️ PENTING
                });

                Swal.fire({
                    title: 'Are You Sure?',
                    text: idStocks.length + ' Data Will Be Changed',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Change!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'edit_stock_bulk.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                idStocks: idStocks,
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
</body>

</html>