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
                url: assetPath + 'role/getIndex',
                type: 'get'
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'id', name: 'id'},
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
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
            order: [[2, 'desc']],
            dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 5,
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
                            exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                            className: 'dropdown-item',
                            exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                            className: 'dropdown-item',
                            exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                            extend: 'pdf',
                            text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                            className: 'dropdown-item',
                            exportOptions: { columns: [3, 4, 5, 6, 7] }
                        },
                        {
                            extend: 'copy',
                            text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                            className: 'dropdown-item',
                            exportOptions: { columns: [3, 4, 5, 6, 7] }
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
                    text: feather.icons['plus'].toSvg({ class: 'mr-50 font-small-4' }) + 'Th??m T??i kho???n',
                    className: 'create-new btn btn-primary',



                    attr: {
                        'data-toggle': 'modal',
                        'data-target': '#modals-slide-in',
                    },
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                    }
                }
            ],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Chi ti???t c???a ' + data['adsense_name'];
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
        $("#UserForm button").click(function (ev) {
            ev.preventDefault() // cancel form submission
            if ($(this).attr("value") == "create") {
                $.ajax({
                    data: $('#UserForm').serialize(),
                    url: "/user/post_add_user",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        if (data.message) {
                            if(data.message.name){
                                $('input[name=name]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.name)
                                    .css('display','block');
                            }else{
                                $('input[name=name]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                            if(data.message.email){
                                $('input[name=email]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.email)
                                    .css('display','block');
                            }else{
                                $('input[name=email]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                        }
                        if (data.success) {
                            $('#id').val(data.id);
                            Swal.fire({
                                title: 'Th??nh c??ng!',
                                text: ' Th??m m???i th??nh c??ng',
                                icon: 'success',
                                timer: 2000,
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                            table.ajax.reload();
                            $('#UserForm').trigger("reset");
                            $('#modals-slide-in').modal('hide');
                        }
                    },
                });
            }
            if ($(this).attr("value") == "update") {
                $.ajax({
                    data: $('#UserForm').serialize(),
                    url: "/user/post_add_user",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        if (data.message) {
                            if(data.message.email){
                                $('input[name=email]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.email)
                                    .css('display','block');
                            }else{
                                $('input[name=email]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                            if(data.message.name){
                                $('input[name=name]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html(data.message.name)
                                    .css('display','block');
                            }else{
                                $('input[name=name]')
                                    .parents('.form-group')
                                    .find('.help-block')
                                    .html('')
                                    .css('display','none');
                            }
                        }
                        if (data.success) {
                            $('#id').val(data.id);

                            Swal.fire({
                                title: 'Th??nh c??ng!',
                                text: ' C???p nh???t th??nh c??ng',
                                icon: 'success',
                                timer: 2000,
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                            table.ajax.reload();
                            $('#UserForm').trigger("reset");
                            $('#modals-slide-in').modal('hide');

                        }
                    },
                });
            }
            // if ($(this).attr("value") == "getId") {
            //     var id= $('#id').val();
            //     $.get("/api/admod/get_add_ga/"+id,function (data) {
            //         if(data.errors){
            //             $('input[name=id]')
            //                 .parents('.form-group')
            //                 .find('.help-block')
            //                 .html(data.errors)
            //                 .css('display','block');
            //         }else{
            //             $('input[name=id]')
            //                 .parents('.form-group')
            //                 .find('.help-block')
            //                 .html('')
            //                 .css('display','none');
            //         }
            //         if(data.success){
            //             $('#createButton').text('Update');
            //             $('#createButton').val('update');
            //             $('#getTokenInput').prop("href", "admod/get-ga-token?id="+data[0].id);
            //             $('.alert-error').prop('class','alert-error hidden');
            //             $('#id').val(data[0].id);
            //             $('#g_dev_key').val(data[0].g_dev_key);
            //             $('#g_secret').val(data[0].g_secret)
            //             $('#note').val(data[0].note)
            //             $('#g_client_id').val(data[0].g_client_id)
            //             $('#admod_name').val(data[0].admod_name)
            //             $('#admod_pub_id').val(data[0].admod_pub_id)
            //         }
            //     })
            // }
        });
        $(document).on('click','.deleteUser', function (data){

            var id = $(this).data("id");
            alert(id)
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
                        url: "user/delete/" + id,
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
