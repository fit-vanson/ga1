
@extends('layouts/contentLayoutMaster')

@section('title', 'Phân quyền')

@section('vendor-style')
  {{-- vendor css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/pickers/flatpickr/flatpickr.min.css')) }}">
@endsection

@section('content')
<!-- Basic table -->
<section id="basic-datatable">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <table class="datatables-basic table permission-table">
          <thead>
            <tr>
              <th>Id</th>
              <th>Tên quyền</th>
              <th>Mô tả</th>
{{--              <th>Action</th>--}}
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!-- Modal to add new record -->
  <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
    <div class="modal-dialog">
      <form class="add-new-ga modal-content pt-0" id="PermissionForm">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">Phân quyền</h5>
        </div>

        <div class="modal-body flex-grow-1">
          <div class="form-group" hidden>
            <label class="form-label" for="basic-icon-default-fullname">ID</label>
            <input
                    type="number"
                    class="form-control dt-full-name"
                    id="id"
                    name="id"
            />
          </div>

          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">Tên Module</label>
            <select class="custom-select" name="module_parent" id="module_parent">
              <option value="">Chọn tên Modul</option>
              @foreach(config('permissions.table-module') as $table)
                <option value="{{$table}}">{{$table}}</option>
              @endforeach
            </select>

            <div class="help-block"></div>
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">Mô tả phân quyền Module</label>
            <div class="col-sm-12">
              @foreach(config('permissions.module-child') as $item)

                <div class="custom-control custom-control-primary custom-checkbox">
                  <label for="">
                    <input name="module_child[]" type="checkbox" checked="checked" value="{{$item}}"> {{$item}}
                  </label>
                </div>
              @endforeach

            </div>
            <div class="help-block"></div>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary" id="createButton" value="create" >Create</button>
            <input type="reset" class="btn btn-outline-secondary" data-dismiss="modal" value="Close"/>
          </div>

        </div>
      </form>
    </div>
  </div>
</section>
<!--/ Basic table -->

<!--/ Multilingual -->
@endsection


@section('vendor-script')
  {{-- vendor files --}}
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/pickers/flatpickr/flatpickr.min.js')) }}"></script>
@endsection
@section('page-script')
  {{-- Page js files --}}
{{--  <script src="{{ asset('js/scripts/user/user.js') }}"></script>--}}
  <script src="{{ asset(('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
  <script type="text/javascript">
    $(function () {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      var table = $('.permission-table').DataTable({
        searching: true,
        serverSide: true,
        processing: true,
        ajax: '{{ route('permission-getPer') }}',
        columns: [
          {data: 'id', name: 'id'},
          {data: 'name', name: 'name'},
          {data: 'display_name', name: 'display_name'},
          // {data: 'action', name: 'action'},

        ],
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

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
            text: feather.icons['plus'].toSvg({ class: 'mr-50 font-small-4' }) + 'Thêm Phân quyền',
            className: 'create-new addPermission btn btn-primary',



            attr: {
              'data-toggle': 'modal',
              'data-target': '#modals-slide-in',
            },
            init: function (api, node, config) {
              $(node).removeClass('btn-secondary');
            }
          }
        ],

      });
      $('div.head-label').html('<h6 class="mb-0">Vai trò</h6>');
      $("#PermissionForm button").click(function (ev) {
        ev.preventDefault() // cancel form submission
        if ($(this).attr("value") == "create") {
          $.ajax({
            data: $('#PermissionForm').serialize(),
            url: "/permission/create",
            type: "POST",
            dataType: 'json',
            success: function (data) {
              if (data.success) {
                $('#id').val(data.id);
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
                $('#PermissionForm').trigger("reset");
                $('#modals-slide-in').modal('hide');
              }
            },
          });
        }
      });
    });
  </script>
@endsection
