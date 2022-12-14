
@extends('layouts/contentLayoutMaster')

@section('title', 'AdMod')

@section('vendor-style')
  {{-- vendor css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/pickers/flatpickr/flatpickr.min.css')) }}">
  <link rel="stylesheet" href="{{asset('css/base/pages/ui-feather.css')}}">
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
              <th>Pub Id</th>
              <th>Yêu cầu mạng</th>
              <th>CLICKS</th>
              <th>eCPM</th>
              <th>MATCHED REQUESTS</th>
              <th>Earning This Month</th>
              <th>Note</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!-- Modal to add new record -->
  <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
    <div class="modal-dialog modal-lg">
      <form class="add-new-ga modal-content pt-0" id="addGaForm">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
        <div class="modal-header mb-1">
          <h5 class="modal-title" id="exampleModalLabel">New Ga</h5>
        </div>

        <div class="modal-body flex-grow-1">
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">ID</label>
            <input
                    type="number"
                    class="form-control dt-full-name"
                    id="id"
                    name="id"
            />
            <div class="help-block"></div>
          </div>
          <button type="submit" class="btn btn-primary getIdButton" disabled value="getId" >Get</button>

          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">AdMod Pub Id</label>
            <input
                    type="text"
                    class="form-control dt-full-name"
                    id="admod_pub_id"
                    name="admod_pub_id"
                    disabled
            />
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">AdMod Name</label>
            <input
                    type="text"
                    class="form-control dt-full-name"
                    id="admod_name"
                    name="admod_name"
                    disabled
            />
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-fullname">Dev Key</label>
            <input
                    type="text"
                    class="form-control dt-full-name"
                    id="g_dev_key"
                    name="g_dev_key"
            />
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-uname">Note</label>
            <input
                    type="text"
                    id="note"
                    class="form-control dt-uname"
                    name="note"
            />
          </div>
          <div class="form-group">
            <label class="form-label" for="basic-icon-default-email">Google client id</label>
                <textarea
                    class="form-control"
                    id="g_client_id"
                    name="g_client_id"
                    rows="4"
                    placeholder="244702961277-btftnbmbnvh7acjm3k51uc8tp8i4kgf7.apps.googleusercontent.com"
            ></textarea>
            <div class="help-block"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Google secret</label>
            <input
                    type="text"
                    id="g_secret"
                    class="form-control"
                    name="g_secret"
                    placeholder="Pbb_HQTw9Z_rkEJ3Rfa1i_ik"
            />
            <div class="help-block"></div>

          </div>
          <div class="form-group">
            <a id="getTokenInput" target="_blank" class="hidden" href="#">Lấy Token</a>
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
  <script src="{{ asset('js/scripts/api/admod.js') }}"></script>
  <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
  <script>
    $(document).ready(function() {
      $('.create-new').on('click',function (){
        $('#createButton').text('Create');
        $('#createButton').val('create');
        $('#createButton').prop('class','btn btn-primary');
        $('#addGaForm').trigger("reset");
        $('textarea[name=g_client_id]')
                .parents('.form-group')
                .find('.help-block')
                .html('')
                .css('display','none');
        $('input[name=g_secret]')
                .parents('.form-group')
                .find('.help-block')
                .html('')
                .css('display','none');

      });
    });

    $(document).ready(function() {
      $('#id').on('input change', function() {
        var id = $(this).val();
        if($(this).val() != '') {
          $('.getIdButton').prop('disabled', false);
          $('#getTokenInput').prop('class','show');
          $('#getTokenInput').prop("href", "adsense/get-ga-token?id="+id)
          $('#createButton').prop('class','btn btn-success');
          $('#createButton').text('Update');
          $('#createButton').val('update');
        } else {
          $('.getIdButton').prop('disabled', true);
          $('#getTokenInput').prop('class','hidden');
          $('#createButton').prop('class','btn btn-primary');
          $('#createButton').text('Create');
          $('#createButton').val('create');
        }
      });
    });
    function editGa(id) {
      $('#modals-slide-in').modal('show');
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
          $('#createButton').prop('class','btn btn-success');
          $('#getTokenInput').prop('class','show');
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
  </script>
@endsection
