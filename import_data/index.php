<?php
session_start();
include_once("../auth_check.php");
if ($_SESSION['role'] !== 'Admin') {
    http_response_code(404);
    exit;
}

if ($_SESSION["role"] !== 'Admin') {
    http_response_code(404);
    exit;
}


if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

$role     = $_SESSION['role'];
$user_id  = (int) $_SESSION['id'];

$title = "Import Data Excel";
require_once '../partials/header.php';
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">

    <?php include '../partials/overlay.php'; ?>

    <div class="wrapper">

        <?php include '../partials/navbar.php'; ?>
        <?php include '../partials/sidebar.php'; ?>

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
                                <li class="breadcrumb-item"><a href="../dashboard">Home</a></li>
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
                                <i class="fas fa-upload"></i> Upload Data Excel
                            </h3>
                        </div>

                        <!-- FORM -->
                        <form enctype="multipart/form-data" id="uploadForm">

                            <div class="card-body">

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
                                <div id="progressBox" class="progress mt-3" style="display:none;">
                                    <div id="progressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        style="width: 0%">
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-eye"></i> Preview
                                </button>

                                <button type="button" class="btn btn-success btn-sm" onclick="startImport()">
                                    <i class="fas fa-upload"></i> Upload
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

        <?php include '../partials/footer.php'; ?>

    </div>

    <?php require_once '../partials/scripts.php'; ?>

    <!-- INIT -->
    <script>
        $(function() {
            bsCustomFileInput.init();
        });
    </script>

    <!-- PREVIEW -->
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let fileInput = document.getElementById('file');

            if (!fileInput.files.length) {
                Swal.fire('Warning', 'Please Select A File First', 'warning');
                return;
            }

            let formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('preview', true);

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('<?= base_url('import_data/import') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {

                    Swal.close();

                    if (res.status === 'success') {
                        Swal.fire('Preview Ready', 'Please Check The Data', 'success');

                        if (res.html) {
                            document.getElementById('result-table').innerHTML = res.html;
                        }
                    } else {
                        Swal.fire('Error', res.message || 'Preview Failed', 'error');
                    }

                })
                .catch(() => {
                    Swal.fire('Error', 'Failed To Upload File', 'error');
                });
        });
    </script>

    <!-- IMPORT -->
    <script>
        function startImport() {

            let fileInput = document.getElementById('file');

            if (!fileInput.files.length) {
                Swal.fire('Warning', 'Please Select A File First', 'warning');
                return;
            }

            // tampilkan progress
            document.getElementById('progressBox').style.display = 'block';
            document.getElementById('progressBar').style.width = '0%';

            let progress = 0;
            let interval = setInterval(() => {
                progress += 10;
                document.getElementById('progressBar').style.width = progress + '%';

                if (progress >= 100) clearInterval(interval);
            }, 200);

            let formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('import', true);

            fetch('<?= base_url('import_data/import') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {

                    if (res.status === 'failed') {

                        let html = '<b>SN Problems:</b><br><br>';

                        for (let key in res.sn_errors) {
                            let uniqueSN = [...new Set(res.sn_errors[key])];

                            html += `<b>${key.toUpperCase()}</b><br>`;
                            uniqueSN.forEach(sn => {
                                html += `- ${sn}<br>`;
                            });
                            html += '<br>';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            html: html
                        });

                        return;
                    }

                    if (res.status === 'success') {
                        Swal.fire('Berhasil', 'All Data Imported Successfully', 'success')
                            .then(() => {
                                location.reload();
                            });
                    }

                    if (res.status === 'error') {
                        Swal.fire('Error', res.message, 'error');
                    }

                })
                .catch(() => {
                    Swal.fire('Error', 'An Error Occurred While Importing', 'error');
                });
        }
    </script>
    <!-- <script>
        function startImport() {

            let fileInput = document.getElementById('file');

            if (!fileInput.files.length) {
                Swal.fire('Warning', 'Pilih file dulu', 'warning');
                return;
            }

            let formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('import', true);

            let progressBox = document.getElementById('progressBox');
            let progressBar = document.getElementById('progressBar');

            progressBox.style.display = 'block';
            progressBar.style.width = '0%';

            let xhr = new XMLHttpRequest();

            xhr.open('POST', '<?= base_url('import_data/import') ?>', true);

            // 🔥 PROGRESS REAL
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    let percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = percent + '%';
                }
            };

            xhr.onload = function() {
                progressBar.style.width = '100%';
                let res = JSON.parse(xhr.responseText);

                if (res.status === 'success') {
                    Swal.fire('Berhasil', 'Data berhasil diimport', 'success');
                }

                if (res.status === 'failed') {

                    let html = '<b>SN Bermasalah:</b><br><br>';

                    for (let key in res.sn_errors) {
                        let uniqueSN = [...new Set(res.sn_errors[key])];

                        html += `<b>${key.toUpperCase()}</b><br>`;
                        uniqueSN.forEach(sn => {
                            html += `- ${sn}<br>`;
                        });
                        html += '<br>';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Import Gagal',
                        html: html
                    });
                }

                if (res.status === 'error') {
                    Swal.fire('Error', res.message, 'error');
                }
            };

            xhr.onerror = function() {
                Swal.fire('Error', 'Upload gagal', 'error');
            };

            xhr.send(formData);
        }
    </script> -->

</body>

</html>