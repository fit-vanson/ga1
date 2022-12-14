/**
 * DataTables Basic
 */

$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    'use strict';

    var dt_basic_table = $('.datatables-basic'),
        assetPath = window.location.href;


    if ($('body').attr('data-framework') === 'laravel') {
        assetPath = $('body').attr('data-asset-path');
    }

    // DataTable with buttons
    // --------------------------------------------------------------------

    if (dt_basic_table.length) {
        var isRtl = $('html').attr('data-textdirection') === 'rtl';

        var table = dt_basic_table.DataTable({

            ajax: {
                url: assetPath + 'api/admod/post-list',
                type: 'post'
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'admod_pub_id', name: 'admod_pub_id'},
                {data: 'admod_pub_id', name: 'admod_pub_id'},
                {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
                {data: 'CLICKS', name: 'CLICKS'},
                {data: 'eCPM', name: 'eCPM'},
                {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
                {data: 'total_month', name: 'total_month'},
                {data: 'note', name: 'note'},
                {data: 'action', name: 'action'},
            ],
            columnDefs: [
                {
                    // For Responsive
                    className: 'control',
                    orderable: false,
                    responsivePriority: 2,
                    targets: 0
                },

                {
                    targets: 2,
                    visible: false
                },

                {
                    responsivePriority: 1,
                    targets: 4
                },

            ],
            order: [[8, 'asc']],
            dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 50,
            lengthMenu: [5, 10, 25, 50, 75, 100],
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle mr-2',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                    buttons: [
                        {
                            extend: 'print',
                            text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
                            className: 'dropdown-item',
                            exportOptions: { columns: [2,3, 4, 5, 6, 7,8] }
                        },
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                            className: 'dropdown-item',
                            exportOptions: { columns: [2,3, 4, 5, 6, 7,8] }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                            className: 'dropdown-item',
                            exportOptions: { columns: [2,3, 4, 5, 6, 7,8] }
                        },
                        {
                            extend: 'pdf',
                            text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                            className: 'dropdown-item',
                            exportOptions: { columns: [2,3, 4, 5, 6, 7,8] }
                        },
                        {
                            extend: 'copy',
                            text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                            className: 'dropdown-item',
                            exportOptions: { columns: [2,3, 4, 5, 6, 7,8] }
                        }
                    ],
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function () {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                },
                {
                    text: feather.icons['plus'].toSvg({ class: 'mr-50 font-small-4' }) + 'Thêm Ga',
                    className: 'create-new btn btn-primary',
                    attr: {
                        'data-toggle': 'modal',
                        'data-target': '#modals-slide-in'
                    },
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                    }
                }
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.status != 1) {
                    $('td', nRow).css('background-color', 'rgb(255 0 0 / 29%)').css('color', 'white');
                }
            },
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Chi tiết của ' + data['note'];
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                                ? '<tr data-dt-row="' +
                                col.rowIndex +
                                '" data-dt-column="' +
                                col.columnIndex +
                                '">' +
                                '<td>' +
                                col.title +
                                ':' +
                                '</td> ' +
                                '<td>' +
                                col.data +
                                '</td>' +
                                '</tr>'
                                : '';
                        }).join('');

                        return data ? $('<table class="table"/>').append(data) : false;
                    }
                }
            },
            language: {
                paginate: {
                    // remove previous & next text from pagination
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });

        $('div.head-label').html('<h6 class="mb-0">Google AdMod</h6>');
        $("#addGaForm button").click(function (ev) {
            ev.preventDefault() // cancel form submission
            if ($(this).attr("value") == "create") {
                $.ajax({
                    data: $('#addGaForm').serialize(),
                    url: "/api/admod/post_add_ga",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {

                        if (data.message) {
                            if(data.message.g_secret){
                                $('input[name=g_secret]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.g_secret)
                                    .css('display','block');
                            }else{
                                $('input[name=g_secret]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                            if(data.message.g_client_id){
                                $('textarea[name=g_client_id]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.g_client_id)
                                    .css('display','block');
                            }else{
                                $('textarea[name=g_client_id]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                        }
                        if (data.success) {
                            $('#id').val(data.id);
                            $('#getTokenInput').prop('class','show');
                            $('#getTokenInput').prop("href", "admod/get-ga-token?id="+data.id)

                            Swal.fire({
                                title: 'Thành công!',
                                text: ' Thêm mới thành công',
                                icon: 'success',
                                timer: 2000,
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                            table.ajax.reload();
                        }
                    },
                });
            }
            if ($(this).attr("value") == "update") {
                $.ajax({
                    data: $('#addGaForm').serialize(),
                    url: "/api/admod/post_add_ga",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        if (data.message) {
                            if(data.message.g_secret){
                                $('input[name=g_secret]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.g_secret)
                                    .css('display','block');
                            }else{
                                $('input[name=g_secret]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                            if(data.message.g_client_id){
                                $('input[name=g_client_id]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.g_client_id)
                                    .css('display','block');
                            }else{
                                $('input[name=g_client_id]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                        }
                        if (data.success) {
                            $('#id').val(data.id);
                            $('#getTokenInput').prop('class','show');
                            $('#getTokenInput').prop("href", "admod/get-ga-token?id="+data.id)

                            Swal.fire({
                                title: 'Thành công!',
                                text: ' Cập nhật thành công',
                                icon: 'success',
                                timer: 2000,
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                            table.ajax.reload();
                        }
                    },
                });
            }
            if ($(this).attr("value") == "getId") {
                var id= $('#id').val();
                $.get("/api/admod/get_add_ga/"+id,function (data) {
                    if(data.errors){
                        $('input[name=id]')
                            .parents('.form-group')
                            .find('.help-block')
                            .html(data.errors)
                            .css('display','block');
                    }else{
                        $('input[name=id]')
                            .parents('.form-group')
                            .find('.help-block')
                            .html('')
                            .css('display','none');
                    }
                    if(data.success){
                        $('#createButton').text('Update');
                        $('#createButton').val('update');
                        $('#getTokenInput').prop("href", "admod/get-ga-token?id="+data[0].id);
                        $('.alert-error').prop('class','alert-error hidden');
                        $('#id').val(data[0].id);
                        $('#g_dev_key').val(data[0].g_dev_key);
                        $('#g_secret').val(data[0].g_secret)
                        $('#note').val(data[0].note)
                        $('#g_client_id').val(data[0].g_client_id)
                        $('#admod_name').val(data[0].admod_name)
                        $('#admod_pub_id').val(data[0].admod_pub_id)
                    }
                })
            }
        });
        $(document).on('click','.deleteAdmod', function (data){
            var id = $(this).data("id");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "get",
                        url: "/api/admod/delete/" + id,
                        success: function (data) {
                            table.ajax.reload();
                        },
                        error: function (data) {

                        }
                    });
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )
                }
            })
        });

    }






});
