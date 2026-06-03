<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['id'];
$role    = $_SESSION['role'];

/* 🔥 FIX: USERS */
$users = ($role === 'Admin')
    ? query("SELECT * FROM users")
    : query("SELECT * FROM users WHERE id = $user_id");

$id_stock = (int)($_GET['id_stock'] ?? 0);
if ($id_stock <= 0) {
    http_response_code(404);
    exit;
}

/* ================= LOAD DATA ================= */
$whereUser = ($role === 'Admin') ? "" : "AND stock.user_id = $user_id";

$stock = query("
    SELECT stock.*,
           product_type.name_product,
           color_type.name_color,
           IF(users.name IS NULL, 'Deleted User', users.name) AS name,
           detail_list_stock.*
    FROM stock
    LEFT JOIN users ON stock.user_id = users.id
    LEFT JOIN detail_list_stock ON stock.id_stock = detail_list_stock.stock_id
    LEFT JOIN product_type ON stock.id_product_name = product_type.id_product
    LEFT JOIN color_type ON stock.id_edc_color = color_type.id_color
    WHERE stock.id_stock = $id_stock $whereUser
");

if (!$stock) {
    http_response_code(404);
    exit;
}

$stock = $stock[0];

$history = query("
    SELECT 
        stock_history.*,
        users.name AS user_name,
        product_type.name_product,
        member_bank.name_member,
        color_type.name_color
    FROM stock_history
    LEFT JOIN users ON stock_history.user_id = users.id
    LEFT JOIN stock ON stock_history.stock_id = stock.id_stock
    LEFT JOIN product_type ON stock_history.id_product_name = product_type.id_product
    LEFT JOIN member_bank ON stock_history.id_member_bank = member_bank.id_member
    LEFT JOIN color_type ON stock_history.id_edc_color = color_type.id_color
    WHERE stock_history.stock_id = $id_stock
    ORDER BY stock_history.created_at DESC
");

$title = "History Data";
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
                                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                                <li class="breadcrumb-item">My Assets</li>
                                <li class="breadcrumb-item">All Data</li>
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
                            <div class="card card-warning">
                                <div class="card-header">
                                    <i class="fas fa-history"></i>&nbsp; History Data
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table id="example1" class="table table-bordered table-hover">
                                        <thead class="text-center">
                                            <tr>
                                                <!-- <th style="width: 8px;">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input custom-control-input-danger"
                                                            type="checkbox" id="checkAll">
                                                        <label for="checkAll" class="custom-control-label"></label>
                                                    </div>
                                                </th> -->
                                                <th>Updated At</th>
                                                <th>User</th>
                                                <th>SN EDC</th>
                                                <th>Simcard</th>
                                                <th>Samcard (MANDIRI)</th>
                                                <th>Samcard (BRI)</th>
                                                <th>Samcard (BNI)</th>
                                                <th>TID</th>
                                                <th>MID</th>
                                                <th>Merchant</th>
                                                <th>Member Bank</th>
                                                <th>Work Type</th>
                                                <th>Date Used</th>
                                                <th>Date Send To HO</th>
                                                <th>Note</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <?php foreach ($history as $h): ?>
                                                <tr>
                                                    <td><?= e($h['created_at']) ?></td>
                                                    <td><?= e($h['user_name']) ?></td>
                                                    <td><?= e($h['sn_edc']) ?></td>
                                                    <td><?= e($h['sn_simcard']) ?></td>
                                                    <td><?= e($h['sn_samcard1']) ?></td>
                                                    <td><?= e($h['sn_samcard2']) ?></td>
                                                    <td><?= e($h['sn_samcard3']) ?></td>
                                                    <td><?= e($h['tid']) ?></td>
                                                    <td><?= e($h['mid']) ?></td>
                                                    <td><?= e($h['merchant_name']) ?>
                                                        <h6 style="font-size:smaller;"><?= e($h['addres_name']) ?></h6>
                                                    </td>
                                                    <td><?= e($h['name_member']) ?></td>
                                                    <td><?= e($h['work_type']) ?></td>
                                                    <td><?= e($h['date_used']) ?></td>
                                                    <td><?= e($h['date_sendto_ho']) ?></td>
                                                    <td><?= e($h['note']) ?></td>
                                                    <td>
                                                        <?php if (($h["status_edc"] ?? '') === 'Used'): ?>
                                                            <span class="badge bg-success">
                                                                <?= htmlspecialchars((string)$h["status_edc"]) ?>
                                                            </span>
                                                        <?php elseif (($h["status_edc"] ?? '') === 'None'): ?>
                                                            <span class="badge bg-danger">
                                                                <?= htmlspecialchars((string)$h["status_edc"]) ?>
                                                            </span>
                                                        <?php elseif (($h["status_edc"] ?? '') === 'Not yet used'): ?>
                                                            <span class="badge bg-warning">
                                                                <?= htmlspecialchars((string)$h["status_edc"]) ?>
                                                            </span>
                                                        <?php elseif (($h["status_edc"] ?? '') === 'Terlink'): ?>
                                                            <span class="badge bg-primary">
                                                                <?= htmlspecialchars((string)$h["status_edc"]) ?>
                                                            </span>
                                                        <?php elseif (($h["status_edc"] ?? '') === 'Send To HO'): ?>
                                                            <span class="badge bg-indigo">
                                                                <?= htmlspecialchars((string)$h["status_edc"]) ?>
                                                            </span>
                                                        <?php elseif (($h["status_edc"] ?? '') === 'HO Santana'): ?>
                                                            <span class="badge bg-info">
                                                                <?= htmlspecialchars((string)$h["status_edc"]) ?>
                                                            </span>
                                                        <?php endif; ?>
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
            }).container().appendTo('#example1_wrapper .col-md-6:eq(0)');
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
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= base_url('type_settings/color_type/delete_color_bulk') ?>',
                            type: 'POST',
                            data: {
                                ids: ids
                            },

                            beforeSend: function() {
                                $('#pageLoader').show(); // 🔥 MUNCULKAN OVERLAY
                                $('#btnDelete').addClass('disabled');
                            },

                            complete: function() {
                                $('#pageLoader').hide(); // 🔥 SEMBUNYIKAN OVERLAY
                                $('#btnDelete').removeClass('disabled');
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

                        beforeSend: function() {
                            $('#pageLoader').show(); // 🔥 OVERLAY LANGSUNG MUNCUL
                        },

                        complete: function() {
                            $('#pageLoader').hide(); // 🔥 HILANGKAN SETELAH SELESAI
                        },

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