<?php
session_start();
include_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$id = $_SESSION["id"];
$role = $_SESSION['role'];
$user_id = query("SELECT * FROM users WHERE id = $id")[0];

$user_id = (int) $_SESSION['id'];
if ($role == 'Admin') {
    $query = query("SELECT 
              (SELECT COUNT(*) FROM stock) AS total_stock,
              (SELECT COUNT(*) FROM stock WHERE status_edc = 'Not yet used') AS total_not_used,
              (SELECT COUNT(*) FROM stock WHERE status_edc = 'Used') AS total_used,
              (SELECT COUNT(*) FROM return_edc) AS total_return");
} else {
    $query = query("SELECT 
                  (SELECT COUNT(*) 
                  FROM stock 
                  WHERE user_id = $user_id) AS total_stock,
                  
                  (SELECT COUNT(*) 
                  FROM stock 
                  WHERE status_edc = 'Not yet used' 
                  AND user_id = $user_id) AS total_not_used,
                  
                  (SELECT COUNT(*) 
                  FROM stock 
                  WHERE status_edc = 'Used' 
                  AND user_id = $user_id) AS total_used,
                  
                  (SELECT COUNT(*) 
                  FROM return_edc 
                  WHERE user_id = $user_id) AS total_return,
                  
                  (SELECT COUNT(*) 
                  FROM return_edc 
                  WHERE status1 = 'Technician' 
                  AND user_id = $user_id) AS total_return_technician,

                  (SELECT COUNT(*) 
                  FROM return_edc 
                  WHERE status1 = 'HO' 
                  AND user_id = $user_id) AS total_return_ho");
}

$totalStock     = $query[0]['total_stock'];
$totalNotUsed   = $query[0]['total_not_used'];
$totalUsed      = $query[0]['total_used'];
$totalReturn    = $query[0]['total_return'];


$title = "Dashboard";
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
                                <li class="breadcrumb-item active">Dashboard</li>
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
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= $totalStock; ?></h3>

                                    <p>TOTAL EDC</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-laptop"></i>
                                </div>
                                <a href="#" class="small-box-footer">&nbsp;</a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $totalNotUsed; ?></h3>

                                    <p>READY TO USE</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-stats-bars"></i>
                                </div>
                                <a href="#" class="small-box-footer">&nbsp;</a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= $totalUsed; ?></h3>

                                    <p>ALREADY USED</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer">&nbsp;</a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?= $totalReturn; ?></h3>

                                    <p>RETURN EDC</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="#" class="small-box-footer">&nbsp;</a>
                            </div>
                        </div>
                        <!-- ./col -->
                    </div>
                </div><!-- /.container-fluid -->
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 py-2">
                                    <div class="card shadow-sm" style="height : 19rem; background-color: #FFFFFF; background-position: calc(100% + 1rem) bottom; background-size: 30% auto; background-repeat: no-repeat; background-image: url(../assets/dist/img/rhone.svg);">
                                        <div class=" px-4 mt-4">
                                            <h4 class="text-primary"> <b>Welcome, <?= $user["name"]; ?></b> </h4>
                                            <h4 class="text-black-50 mb-0">ASSETS MANAGEMENT STOCK EDC</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
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
</body>

</html>