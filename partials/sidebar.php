<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 403 Forbidden');
    include("../errors/404.html");
    exit();
}
// sidebar.php (partial) — tidak perlu logic aktif di PHP, aktif handle by JS!
$id = $_SESSION["id"];
$role = $_SESSION["role"];
$user = query("SELECT * FROM users WHERE id = $id")[0];

$user_id = (int) $_SESSION['id'];
if ($role == 'Admin') {
    $query = query("SELECT 
                (SELECT COUNT(*) FROM stock) AS total_stock,
                (SELECT COUNT(*) FROM stock WHERE status_edc = 'Not yet used') AS total_not_used,
                (SELECT COUNT(*) FROM stock WHERE status_edc = 'Used') AS total_used,
                (SELECT COUNT(*) FROM return_edc) AS total_return,
                (SELECT COUNT(*)FROM return_edc WHERE status1 = 'Technician') AS total_return_technician,
                (SELECT COUNT(*)FROM return_edc WHERE status1 = 'HO') AS total_return_ho");
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
$totalReturnTechnician    = $query[0]['total_return_technician'];
$totalReturnHo    = $query[0]['total_return_ho'];

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="../dashboard" class="brand-link">
        <img src="../assets/dist/img/Yokke.png" alt="Yokke" sty class="brand-image" style="opacity: .9">
        <span class="brand-text font-weight-bold ml-2"> PT. MTI (Yokke)</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image mt-2">
                <img src="../assets/dist/img/profile/<?= $user['avatar']; ?>" class="brand-image img-circle elevation-2" alt="User Image" style="width: 40px; height: 40px;">
            </div>
            <div class="info">
                <a href="../dashboard" class="d-block ">
                    <span style="font-size: 14px;"><?= $user["name"]; ?></span>
                    <h6><span style="font-size: 14px;"><?= $role; ?></span></h6>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="../dashboard" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-edit"></i>
                        <p>
                            My Assets
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../listStockEdc" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>List Stock EDC <span class="right badge badge-danger"><?= $totalStock; ?></span></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-vote-yea"></i>
                        <p>
                            Status EDC
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../ReadyToUse" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Ready To Use <span class="right badge badge-danger"><?= $totalNotUsed; ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../AlreadyToUse" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Already Used <span class="right badge badge-danger"><?= $totalUsed; ?></span></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            Return EDC
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../return_edc" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>List Return EDC <span class="right badge badge-danger"><?= $totalReturn; ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../technician" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Technician <span class="right badge badge-danger"><?= $totalReturnTechnician; ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../ho" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>HO <span class="right badge badge-danger"><?= $totalReturnHo; ?></span></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-header">SETTINGS</li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Account
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="../profile" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Profile</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../change_password" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Change Password</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if ($role === 'Admin') : ?>
                    <li class="nav-item">
                        <a href="../user_management" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>User Management</p>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="../logout" class="nav-link" id="btnLogout">
                        <i class="nav-icon fas fa-power-off"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>