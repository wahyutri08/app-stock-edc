 <?php
    if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
        header('HTTP/1.1 403 Forbidden');
        include("../errors/404.html");
        exit();
    }
    ?>

 <nav class="main-header navbar navbar-expand navbar-white navbar-light">
     <!-- Left navbar links -->
     <ul class="navbar-nav">
         <li class="nav-item">
             <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
         </li>
         <li class="nav-item d-none d-sm-inline-block">
             <a href="../dashboard" class="nav-link">Home</a>
         </li>
     </ul>

     <!-- Right navbar links -->
     <ul class="navbar-nav ml-auto">
         <li class="nav-item">
             <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                 <i class="fas fa-expand-arrows-alt"></i>
             </a>
         </li>
         <li class="nav-item dropdown user-menu">
             <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                 <span><?php echo $_SESSION['username'] ?></span>
             </a>
             <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                 <li class="user-footer"><a class="btn btn-default btn-flat float-right  btn-block" id="btnLogout" href="../logout"><i class="fa fa-fw fa-power-off"></i> Logout</a></li>
             </ul>
         </li>
     </ul>
 </nav>