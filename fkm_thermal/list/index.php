<?php
session_start();
include_once("../../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../../login");
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

$title = "FKM Thermal";
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
                                <li class="breadcrumb-item"><?= $title;  ?></li>
                                <li class="breadcrumb-item">List</li>
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
                                                <label>Status Merchant:</label>
                                                <select class="custom-select form-control" name="status_merchant" id="status_merchant">
                                                    <option value="all">-All Status-</option>
                                                    <option value="Active">Active</option>
                                                    <option value="Not Active">Not Active</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="search">Search:</label>
                                                <input type="text" name="search" class="form-control" id="search" placeholder="TID, MID, Merchant, SN">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-search"></i> Search
                                                </button>
                                                <button type="button" id="btn-cetak-terpilih" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-file-pdf"></i> Export FKM Thermal PDF
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
        <?php include '../../partials/footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <?php require_once '../../partials/scripts.php'; ?>

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
        // CHECK ALL
        $(document).on('click', '#checkAll', function() {
            $('.check-item').prop('checked', this.checked);
        });

        // CETAK PDF
        $(document).on('click', '#btn-cetak-terpilih', function(e) {
            e.preventDefault();

            let selected = [];

            $('.check-item:checked').each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                Swal.fire('Warning', 'Select At Least 1 Data', 'warning');
                return;
            }

            // 🔥 LOADING DISINI
            Swal.fire({
                title: 'Generating PDF...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            let form = $('<form>', {
                action: "<?= base_url('fkm_thermal/cetak') ?>",
                method: 'POST'
            });

            selected.forEach(function(id) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'ids[]',
                    value: id
                }));
            });

            $('body').append(form);
            form.hide();
            form.submit();

            // 🔥 TUTUP LOADING SETELAH KIRIM
            setTimeout(() => {
                Swal.close();
            }, 1000);

            form.remove();
        });
    </script>
    <script>
        $(document).ready(function() {

            $('.select2').select2();
            $('form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?= base_url('ajax/ajax_filter_fkm') ?>',
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
                                pageLength: 25,
                                lengthMenu: [
                                    [10, 25, 50, 100, -1],
                                    [10, 25, 50, 100, "All"]
                                ],
                                searching: true,
                                ordering: true,
                                info: true,
                                autoWidth: true,
                                responsive: false
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