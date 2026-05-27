<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 403 Forbidden');
    include("../errors/403.html");
    exit();
}

function currentPath()
{
    return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
}

function isModule($module)
{
    return strpos(currentPath(), $module) !== false;
}

function isRoute($routes = [])
{
    $path = currentPath();

    foreach ($routes as $route) {
        if (strpos($path, trim($route, '/')) !== false) {
            return true;
        }
    }

    return false;
}

// function currentPath()
// {
//     return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
// }

// function segment($index = 0)
// {
//     $path = currentPath();
//     $segments = explode('/', $path);
//     return $segments[$index] ?? '';
// }

// function isModule($module)
// {
//     return segment(1) === $module; // myassets ada di segment ke-2
// }

// function isRoute($routes = [])
// {
//     $path = currentPath();

//     foreach ($routes as $r) {
//         if (strpos($path, $r) !== false) {
//             return true;
//         }
//     }

//     return false;
// }
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
    <a href="<?= base_url('dashboard') ?>" class="brand-link">
        <img src="<?= base_url('assets/dist/img/Yokke.png') ?>" alt="Yokke" sty class="brand-image" style="opacity: .9">
        <span class="brand-text font-weight-bold ml-2"> PT. MTI (Yokke)</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image mt-2">
                <img src="<?= base_url('assets/dist/img/profile/' . htmlspecialchars($user['avatar'])) ?>" class="brand-image img-circle elevation-2" alt="User Image" style="width: 40px; height: 40px;">
            </div>
            <div class="info">
                <a href="<?= base_url('dashboard') ?>" class="d-block ">
                    <span style="font-size: 14px;"><?= htmlspecialchars($user["name"]); ?></span>
                    <h6><span style="font-size: 14px;"><?= $role; ?></span></h6>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-header">MENU</li>
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= isModule('dashboard') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview <?= isModule('myassets') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= isModule('myassets') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-edit"></i>
                        <p>
                            My Assets
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('myassets/add_data') ?>"
                                class="nav-link <?= isRoute(['add_data']) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Data</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('myassets/allData') ?>"
                                class="nav-link <?= isRoute(['allData', 'edit', 'history']) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Data</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if ($role === 'Admin') : ?>
                    <li class="nav-item has-treeview <?= isModule('export_import') ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= isModule('export_import') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-copy"></i>
                            <p>
                                Export & Import Data
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('export_import/import') ?>" class="nav-link <?= isRoute(['export_import/import']) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Import Data Excel</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('export_import/export') ?>" class="nav-link <?= isRoute(['export_import/export']) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Export Data Excel</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item has-treeview <?= isModule('fkm_thermal') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= isModule('fkm_thermal') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-book"></i>
                        <p>
                            FKM Thermal
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('fkm_thermal/tambah_data') ?>" class="nav-link <?= isRoute(['fkm_thermal/tambah_data']) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Data</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('fkm_thermal/list') ?>" class="nav-link <?= isRoute(['fkm_thermal/list']) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>List</p>
                            </a>
                        </li>
                        <?php if ($role === 'Admin') : ?>
                            <li class="nav-item">
                                <a href="<?= base_url('fkm_thermal/import') ?>" class="nav-link <?= isRoute(['fkm_thermal/import']) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Import Data</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-header">SETTINGS</li>
                <li class="nav-item has-treeview <?= isModule('account') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= isModule('account') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Account
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('account/profile') ?>" class="nav-link <?= isRoute(['account/profile']) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Profile</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('account/change_password') ?>" class="nav-link <?= isRoute(['account/change_password']) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Change Password</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if ($role === 'Admin') : ?>
                    <li class="nav-item has-treeview <?= isModule('type_settings') ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= isModule('type_settings') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                Type Setting
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('type_settings/product_name') ?>" class="nav-link <?= isRoute(['type_settings/product_name']) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Product Name</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('type_settings/color_type') ?>" class="nav-link <?= isRoute(['type_settings/color_type']) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Color Type</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('type_settings/member_bank') ?>" class="nav-link <?= isRoute(['type_settings/member_bank']) ? 'active' : '' ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Member Bank</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($role === 'Admin') : ?>
                    <li class="nav-item">
                        <a href="<?= base_url('user_management') ?>" class="nav-link <?= isModule('user_management') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>User Management</p>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="<?= base_url('logout') ?>" class="nav-link" id="btnLogout">
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