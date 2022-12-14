@extends('layouts/contentLayoutMaster')

@section('title', 'Chỉnh sửa vai trò')

@section('vendor-style')
  {{-- Vendor Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/pickers/flatpickr/flatpickr.min.css')) }}">
@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/pickers/form-flat-pickr.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-user.css')) }}">
@endsection

@section('content')
<!-- users edit start -->
<section class="app-user-edit">
  <div class="card">
    <div class="card-body">
      <div class="tab-content">
        <!-- Account Tab starts -->
        <div class="tab-pane active" id="account" aria-labelledby="account-tab" role="tabpanel">
          <!-- users edit media object start -->
          <div class="media mb-2">
            <div class="media-body mt-50">
              <h4>{{$role->name}}</h4>
            </div>
          </div>
          <!-- users edit account form start -->
          <form class="form-role" id="editRoleForm">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group" hidden>
                  <label class="form-label" for="basic-icon-default-fullname">ID</label>
                  <input
                          type="number"
                          class="form-control dt-full-name"
                          value="{{$role->id}}"
                          id="id"
                          name="id"
                  />
                </div>
                <div class="form-group">
                  <label for="username">Tên vài trò</label>
                  <input
                    type="text"
                    class="form-control"
                    value="{{$role->name}}"
                    name="name"
                    id="name"
                  />
                  <div class="help-block"></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name">Mô tả vai trò</label>
                  <input
                    type="text"
                    class="form-control"
                    value="{{$role->display_name}}"
                    name="display_name"
                    id="display_name"
                  />
                </div>
              </div>
              <div class="col-12">
                <div class="table-responsive border rounded mt-1">
                  <h6 class="py-1 mx-1 mb-0 font-medium-2">
                    <i data-feather="lock" class="font-medium-3 mr-25"></i>
                    <span class="align-middle">Permission</span>
                  </h6>
                  <table class="table table-striped table-borderless">
                    <thead class="thead-light">
                      <tr>
                        <th>Module</th>
                        <th>Index </th>
                        <th>Show </th>
                        <th>Add </th>
                        <th>Edit  </th>
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                    @foreach($permissionsParent as $permission)
                      <tr>
                        <td>{{$permission->name}}</td>
                        @foreach($permission->permissionChild as $permissionChildItem)
                        <td>
                          <div class="custom-control custom-checkbox">
                            <label>
                              <input type="checkbox" name="permissionId[]"
                                     {{$permissionsChecked->contains('id',$permissionChildItem->id) ? 'checked' : ''}}

                                     value="{{$permissionChildItem->id}}">
                            </label>
                          </div>
                        </td>
                        @endforeach

                      </tr>
                    @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                <button type="submit" class="btn btn-primary mb-1 mb-sm-0 mr-0 mr-sm-1">Save Changes</button>
              </div>
            </div>
          </form>
          <!-- users edit account form ends -->
        </div>
        <!-- Account Tab ends -->
      </div>
    </div>
  </div>
</section>
<!-- users edit ends -->
@endsection

@section('vendor-script')

@endsection

@section('page-script')
  <script src="{{ asset(('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
  <script type="text/javascript">
    $(function () {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $("#editRoleForm button").click(function (ev) {
        ev.preventDefault() // cancel form submission
          $.ajax({
            data: $('#editRoleForm').serialize(),
            url: "/role/update",
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
              }
              if (data.success) {
                $('#id').val(data.id);
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
                $('input[name=name]')
                        .parents('.form-group')
                        .find('.help-block')
                        .html('')
                        .css('display','none');
              }
            },
          });
      });

    });

  </script>

@endsection
