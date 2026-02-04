<?php
session_start();
require_once '../functions.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// AJAX LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usernameOremail = $_POST["username"];
    $password = $_POST["password"];
    $result = mysqli_query($db, "SELECT * FROM users WHERE username = '$usernameOremail' OR email = '$usernameOremail'");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if ($row['status'] === 'Active') {
            if (password_verify($password, $row["password"])) {
                $_SESSION["login"] = true;
                $_SESSION['name'] = $row['name'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['id'] = $row['id'];
                $_SESSION['avatar'] = $row['avatar'];
                $_SESSION['role'] = $row['role'];
                echo json_encode(['status' => 'success', 'redirect' => '../dashboard']);
                exit;
            } else {
                $error = 'Wrong Password.';
            }
        } else {
            $error = 'Your Account is Inactive. Please Contact Admin.';
        }
    } else {
        $error = 'Username or Email Not Found.';
    }
    echo json_encode(['status' => 'error', 'message' => $error]);
    exit;
}

// Cegah akses ke halaman ini jika sudah login
if (isset($_SESSION["login"]) && $_SESSION["login"] === true) {
    header("Location: ../dashboard");
    exit;
}

$title = "Login";
require_once '../partials/header.php';

?>

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="../../index2.html" class="h1"><b>STOCK </b>EDC</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <form action="" method="POST" id="myForm">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" name="login" id="loginBtn" class="btn btn-sm btn-primary btn-block "><i class="fas fa-sign-in-alt"></i> Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->

    <?php require_once '../partials/scripts.php'; ?>

    <script>
        $(function() {
            $('#myForm').on('submit', function(e) {
                e.preventDefault();
                var $btn = $('#loginBtn');
                $btn.html('<span class="spinner-border spinner-border-sm text-light me-2"></span> Loading...');
                $btn.prop('disabled', true);

                $.ajax({
                    url: '', // submit ke halaman yang sama
                    method: 'POST',
                    data: $(this).serialize() + '&login=1',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            window.location.href = res.redirect;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Failed',
                                text: res.message,
                                confirmButtonText: 'OK',
                                heightAuto: false,
                                confirmButtonColor: '#d33'
                            });
                            $btn.html('<i class="fas fa-sign-in-alt"></i> Sign In');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Server error!',
                            heightAuto: false,
                            scrollbarPadding: false
                        });
                        $btn.html('<i class="fas fa-sign-in-alt"></i> Sign In');
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>

</html>