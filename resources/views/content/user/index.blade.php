
@extends('layouts/contentLayoutMaster')

@section('title', 'User')

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
        <table class="datatables-basic table">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>Id</th>
              <th>User</th>
              <th>Email</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!-- Modal to add new record -->
  <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
    <div class="modal-dialog">
      <form class="add-new-ga modal-content pt-0" id="UserForm">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">Tài khoản</h5>
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
            <label class="form-label" for="basic-icon-default-fullname">User Name</label>
            <input
                    type="text"
                    class="form-control dt-full-name"
                    id="name"
                    name="name"

            />
            <div class="help-block"></div>
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">Email</label>
            <input
                    type="text"
                    class="form-control dt-full-name"
                    id="email"
                    name="email"
            />
            <div class="help-block"></div>
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">Mật khẩu</label>
            <input
                    type="text"
                    class="form-control dt-full-name"
                    id="password"
                    name="password"
            />
            <div class="help-block"></div>
          </div>

          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">Vai trò</label>
            <select class="custom-select" name="role_id" id="role_id">
              @foreach($roles as $role)
                <option
                        value="{{$role->id}}">{{$role->name}}
                </option>
              @endforeach
            </select>

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
  <script src="{{ asset('js/scripts/user/user.js') }}"></script>
  <script src="{{ asset(('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
  <script>
    $(document).ready(function() {
      $('.create-new').on('click',function (){
        $('#UserForm').trigger("reset");
        $('#createButton').prop('class','btn btn-primary');
        $('#createButton').text('Create');
        $('#createButton').val('create');
      });
    });

    function editUser(id) {
      $.get("/user/get_add_user/"+id,function (data) {
        $('#modals-slide-in').modal('show');
        $('input[name=email]')
                .parents('.form-group')
                .find('.help-block')
                .html('')
                .css('display','none');
        $('input[name=name]')
                .parents('.form-group')
                .find('.help-block')
                .html('')
                .css('display','none');
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
          $('#createButton').prop('class','btn btn-success');
          $('.alert-error').prop('class','alert-error hidden');
          $('#id').val(data[0].id);
          $('#name').val(data[0].name);
          $('#email').val(data[0].email)
          $('#email').val(data[0].email)
          var roles = data[1];
          var role = [];
          $.each(roles, function(idx2,val2) {
            var str =  val2.id;
            role.push(str);
          });
          $('#role_id').val(role).trigger('change')
        }
      })
    }



  </script>
@endsection
