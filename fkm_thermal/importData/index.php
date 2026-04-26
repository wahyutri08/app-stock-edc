<?php
session_start();
include_once("../../auth_check.php");

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../../login");
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    http_response_code(404);
    exit;
}

$role     = $_SESSION['role'];
$user_id  = (int) $_SESSION['id'];

$title = "Import Data FKM";
require_once '../../partials/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">

    <?php include '../../partials/overlay.php'; ?>

    <div class="wrapper">

        <?php include '../../partials/navbar.php'; ?>
        <?php include '../../partials/sidebar.php'; ?>

        <div class="content-wrapper">

            <!-- HEADER -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?= $title; ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../../dashboard">Home</a></li>
                                <li class="breadcrumb-item"><?= $title; ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="content">
                <div class="container-fluid">

                    <div class="card card-warning">

                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-upload"></i> Upload Data Excel FKM
                            </h3>
                        </div>

                        <form enctype="multipart/form-data" id="uploadForm">

                            <div class="card-body">

                                <!-- INFO FORMAT -->
                                <!-- <div class="alert alert-info">
                                    <b>Format Excel:</b><br>
                                    user_id | tid | mid | nama_merchant | alamat | status_merchant<br><br>

                                    <b>Contoh:</b><br>
                                    1 | 77779559 | 70411259683 | DRESSUP LAUNDRY | Jakarta | Active<br><br>

                                    <b>Status Merchant:</b> Active / Not Active
                                </div> -->

                                <!-- FILE INPUT -->
                                <div class="form-group">
                                    <label>Pilih File Excel</label>

                                    <div class="custom-file">
                                        <input type="file"
                                            class="custom-file-input"
                                            name="file"
                                            id="file"
                                            accept=".xls,.xlsx">

                                        <label class="custom-file-label" for="file">
                                            Choose file
                                        </label>
                                    </div>
                                </div>

                                <!-- PROGRESS -->
                                <div id="progressBox" class="progress mt-3">
                                    <div id="progressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        style="width: 100%">
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-eye"></i> Preview
                                </button>

                                <button type="button" id="btnImport" class="btn btn-success btn-sm" onclick="startImport()" disabled>
                                    <i class="fas fa-upload"></i> Import
                                </button>

                                <button type="reset" class="btn btn-dark btn-sm">
                                    Reset
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- RESULT -->
                    <div id="result-table" class="p-3"></div>

                </div>
            </div>

        </div>

        <?php include '../../partials/footer.php'; ?>

    </div>

    <?php require_once '../../partials/scripts.php'; ?>

    <!-- INIT -->
    <script>
        $(function() {
            bsCustomFileInput.init();
            document.getElementById('progressBox').style.display = 'none';
        });
    </script>

    <!-- PREVIEW -->
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let fileInput = document.getElementById('file');

            if (!fileInput.files.length) {
                Swal.fire('Warning', 'Select The File First', 'warning');
                return;
            }

            let file = fileInput.files[0];

            // VALIDASI EXTENSION
            if (!file.name.match(/\.(xls|xlsx)$/)) {
                Swal.fire('Error', 'Files Must Be Excel (.xls / .xlsx)', 'error');
                return;
            }

            // reset table
            document.getElementById('result-table').innerHTML = '';

            let formData = new FormData();
            formData.append('file', file);
            formData.append('preview', true);

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('<?= base_url('fkm_thermal/importData/import') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {

                    Swal.close();

                    if (res.status === 'success') {

                        Swal.fire('Preview Ready', 'Check Data Before Import', 'success');

                        document.getElementById('result-table').innerHTML = res.html;

                        // aktifkan tombol import
                        document.getElementById('btnImport').disabled = false;

                    } else {
                        Swal.fire('Error', res.message || 'Preview gagal', 'error');
                    }

                })
                .catch(() => {
                    Swal.fire('Error', 'Gagal upload file', 'error');
                });
        });
    </script>

    <!-- IMPORT -->
    <script>
        function startImport() {

            let fileInput = document.getElementById('file');

            if (!fileInput.files.length) {
                Swal.fire('Warning', 'Select The File First', 'warning');
                return;
            }

            let formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('import', true);

            let progressBox = document.getElementById('progressBox');
            let progressBar = document.getElementById('progressBar');

            progressBox.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.innerHTML = '0%';

            let xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('fkm_thermal/importData/import') ?>', true);

            // 🔥 FAKE PROGRESS (biar keliatan jalan)
            let fake = 0;
            let fakeInterval = setInterval(() => {
                if (fake < 90) {
                    fake += 5;
                    progressBar.style.width = fake + '%';
                    progressBar.innerHTML = fake + '%';
                }
            }, 100);

            // 🔥 REAL (kalau sempat ke-trigger)
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.innerHTML = percent + '%';
                }
            };

            xhr.onload = function() {

                clearInterval(fakeInterval);

                progressBar.style.width = '100%';
                progressBar.innerHTML = '100%';

                let res = JSON.parse(xhr.responseText);

                setTimeout(() => {

                    if (res.status === 'failed') {
                        Swal.fire('Error', res.message || 'There Is Still Error Data', 'error');
                        return;
                    }

                    if (res.status === 'duplicate') {
                        Swal.fire('Warning', 'Data Already Exists', 'warning');
                        return;
                    }

                    if (res.status === 'success') {
                        Swal.fire('Success', 'Data Imported Successfully', 'success')
                            .then(() => location.reload());
                    }

                    if (res.status === 'error') {
                        Swal.fire('Error', res.message, 'error');
                    }

                }, 800);
            };

            xhr.onerror = function() {
                clearInterval(fakeInterval);
                Swal.fire('Error', 'Upload Failed', 'error');
            };

            xhr.send(formData);
        }
    </script>

</body>

</html>