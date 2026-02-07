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
                    $('#btnDeleteReturn, #btnHO, #btnTechnician').removeClass('disabled');
                } else {
                    $('#btnDeleteReturn, #btnHO, #btnTechnician').addClass('disabled');
                }
            }

            // 🗑️ CLICK DELETE (CEGAH JIKA DISABLED)
            $('#btnDeleteReturn').on('click', function(e) {
                if ($(this).hasClass('disabled')) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();

                let listIds = [];
                $('.checkbox-item:checked').each(function() {
                    listIds.push($(this).val());
                });

                Swal.fire({
                    title: 'Are You Sure?',
                    text: listIds.length + ' Data Will be Deleted',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_return_bulk.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                listIds: listIds
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Deleted!', response.message, 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error', 'Server error', 'error');
                            }
                        });
                    }
                });
            });
            $('.btn-action').on('click', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled')) return;

                let status = $(this).data('status');
                let idStatus = [];

                $('.checkbox-item:checked').each(function() {
                    idStatus.push($(this).data('idstatus')); // ⬅️ PENTING
                });

                Swal.fire({
                    title: 'Are You Sure?',
                    text: idStatus.length + ' Data Will Be Changed',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Change!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'edit_status_bulk.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                idStatus: idStatus,
                                status: status
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Success', response.message, 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error', 'Server error', 'error');
                            }
                        });
                    }
                });
            });
        });