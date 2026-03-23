<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$role     = $_SESSION['role'];
$user_id  = (int) $_SESSION['id'];

if ($role === 'Admin') {
    $users = query("SELECT id, name, role FROM users ORDER BY name ASC");
} else {
    $users = query("SELECT id, name, role FROM users WHERE id = $user_id");
}
$product_type = query("SELECT * FROM product_type WHERE status = 'Active'");
$member_bank = query("SELECT * FROM member_bank WHERE status = 'Active'");

$title = "All Data";
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
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-search"></i> Filter Search</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="POST" action="">
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for="date_pickup">Date Pick Up:</label>
                                                <input type="date" class="form-control" name="date_pickup" id="date_pickup">
                                            </div>
                                            <div class=" form-group col-md-3">
                                                <label for="date_used">Date Used:</label>
                                                <input type="date" class="form-control" name="date_used" id="date_used">
                                            </div>
                                            <?php if ($role === 'Admin') : ?>
                                                <div class="form-group col-md-3">
                                                    <label for="user_id">User:</label>
                                                    <select class="select2 form-control" name="user_id" id="user_id" style="width:100%;">
                                                        <option value="all">- All Users -</option>
                                                        <?php foreach ($users as $user) : ?>
                                                            <option value="<?= $user["id"]; ?>">
                                                                <?= htmlspecialchars($user["name"]); ?>
                                                                (<?= htmlspecialchars($user["role"]); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($users as $user): ?>
                                                    <input type="hidden" name="user_id" id="user_id" value="<?= $user["id"]; ?>">
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <div class="form-group col-md-3">
                                                <label for="id_product_name">Product Type:</label>
                                                <select class="select2" name="id_product_name" id="id_product_name" style="width: 100%;">
                                                    <option value="all">-All Product Type-</option>
                                                    <?php foreach ($product_type as $type) : ?>
                                                        <option value="<?= $type["id_product"]; ?>"><?= $type["name_product"]; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="id_member_bank">Member Bank:</label>
                                                <select class="select2" name="id_member_bank" id="id_member_bank" style="width: 100%;">
                                                    <option value="all">-All Member Bank-</option>
                                                    <?php foreach ($member_bank as $bank) : ?>
                                                        <option value="<?= $bank["id_member"]; ?>"><?= $bank["name_member"]; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="work_type">Work Type:</label>
                                                <select class="form-control" name="work_type" id="work_type">
                                                    <option value="all" selected>-All Work Type-</option>
                                                    <option value="INSTAL">INSTAL</option>
                                                    <option value="REPLACEMENT EDC">REPLACEMENT EDC</option>
                                                    <option value="REPLACEMENT PART">REPLACEMENT PART</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="status_edc">Status:</label>
                                                <select class="form-control" name="status_edc" id="status_edc">
                                                    <option value="all" selected>-All Status-</option>
                                                    <option value="Not yet used">Not yet used</option>
                                                    <option value="Used">Used</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="search">Search:</label>
                                                <input type="text" name="search" class="form-control" id="search" placeholder="TID, MID, Merchant, SN">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-search"></i> Search
                                                </button>
                                                <button type="reset" class="btn btn-sm btn-dark">
                                                    Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </div>
            <div class="content" id="result-table">
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
            // Initialize Select2 Elements
            $('.select2').select2();

            // Initialize Select2 Bootstrap 4
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            $('.select2').select2();
            $('form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?= base_url('ajax/ajax_filter_stock') ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',

                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    success: function(res) {
                        Swal.close();

                        if (res.status === 'empty') {

                            $('#result-table').hide().html("");

                            Swal.fire({
                                icon: 'warning',
                                title: 'Data Not Found',
                                text: 'Please Change The Search Filter Or Keyword.'
                            });

                        } else {

                            $('#result-table').html(res.html).fadeIn();

                            // Destroy dulu kalau sudah pernah di-init
                            if ($.fn.DataTable.isDataTable('#example1')) {
                                $('#example1').DataTable().destroy();
                            }

                            // Init ulang setelah html masuk
                            $("#example1").DataTable({
                                paging: true,
                                lengthChange: true,
                                pageLength: 10,
                                lengthMenu: [
                                    [10, 25, 50, 100, -1],
                                    [10, 25, 50, 100, "All"]
                                ],
                                searching: true,
                                ordering: true,
                                info: true,
                                autoWidth: true,
                                responsive: false,
                                buttons: ["excel", "print", "colvis"]
                            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'An error occurred on the server'
                        });
                    }
                });
            });
            // RESET SELECT2
            $('form').on('reset', function() {
                setTimeout(function() {
                    $('.select2').val('all').trigger('change');
                    $('#result-table').html('').hide(); // optional
                }, 0);
            });
        });
    </script>
    <script>
        $(document).on('click', '.tombol-hapus', function(e) {
            e.preventDefault();
            const id_stock = $(this).data('id');
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
                        url: "<?= base_url('all_data/delete') ?>",
                        type: "POST",
                        data: {
                            id_stock: id_stock
                        },
                        dataType: "json", // 🔥 penting
                        beforeSend: function() {
                            $('#pageLoader').show();
                        },
                        success: function(res) {
                            if (res.status === 'success') {

                                Swal.fire(
                                    'Deleted!',
                                    'Data Successfully Deleted',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        complete: function() {
                            $('#pageLoader').hide(); // 🔥 pasti hilang
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            Swal.fire(
                                'Server Error',
                                'Check console for error',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>