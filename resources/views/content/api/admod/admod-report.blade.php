
@extends('layouts/contentLayoutMaster')

@section('title', 'AdMod Report')

@section('vendor-style')
  {{-- vendor css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/pickers/flatpickr/flatpickr.min.css')) }}">
@endsection

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" type="text/css" href="{{asset('css/base/plugins/forms/pickers/form-flat-pickr.css')}}">
@endsection


@section('content')
  <!-- Ajax Sourced Server-side -->
  <section id="ajax-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Thông tin chi tiết tài khoản</h4>
            <span><i>@if(count($records)>0)Cập nhật lúc: {{$records[count($records)-1]->updated_at->format('H:i:s m-d-Y')}}@else <span style="color: red"> Tài khoản không có dữ liệu</span>@endif</i></span>
            <a class="btn btn-outline btn-info" href="get-list?pub_id={{$user->admod_pub_id}}" >Cập nhật</a>

          </div>
          <div class="card-body">
            <div class="demo-spacing-0">
              <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">
                  Account Pub ID : {{$user->admod_pub_id}}
                </h4>
                <div class="alert-body">
                  Account Name : {{$user->admod_name}}
                </div>
                <div class="alert-body">
                  Ghi chú : {{$user->note}}
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="demo-inline-spacing">
              <button class="btn btn-success waves-effect waves-light" onclick="today()">TO DAY</button>
              <button class="btn btn-success waves-effect waves-light" onclick="date()">DATE</button>
              <button class="btn btn-success waves-effect waves-light" onclick="month()">MONTH</button>
              <button class="btn btn-success waves-effect waves-light" onclick="nagion()">NAGION</button>
              <button class="btn btn-success waves-effect waves-light" onclick="ad_unit()">AD UNIT</button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 form-group month_input">
                <label for="basicSelect">Tháng</label>
                <select class="form-control" id="month_input">
                  @foreach($records as $record)
                    <option value="{{$record->month}}">{{$record->month}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6 form-group date-range">
                <label for="fp-range">Range</label>
                <input type="text" id="date-range" class="form-control flatpickr-range flatpickr-input" placeholder="DD-MM-YYYY to DD-MM-YYYY" readonly="readonly">
              </div>

            </div>
          </div>
          <div class="card-datatable">
            <table class="dt-responsive table data-report">
              <thead>
              <tr>
                <th class="metrics"></th>
                @foreach($metrics as $name)
                  <th>{!!$name!!}</th>
                @endforeach
              </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection


@section('vendor-script')
  {{-- vendor files --}}
  <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/pickers/flatpickr/flatpickr.min.js')) }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset(mix('js/scripts/forms/pickers/form-pickers.js')) }}"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>



  <script>
    var id = window.location.search;
    $(document).ready(function(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $('.metrics').text('APP');
      $('.month_input').show();
      $('.date-range').hide();
        var table = $('.data-report').DataTable({
        searching: false,
        serverSide: true,
        processing: true,
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        ajax: {
          url: '{{ route('report-app') }}'+id,
          type: "post",
          data: function (d) {
            d.month = $('#month_input').val()
          }
        },
          initComplete: function (settings, json) {
            if(settings._iRecordsTotal == 0){
              Swal.fire({
                text: 'Chưa có dữ liệu của APP. Vui lòng cập nhật mới.',
                icon: 'warning',
                timer: 1500,
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
              });
            }
          },
        columns: [
          {data: 'app', name: 'app'},
          {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
          {data: 'CLICKS', name: 'CLICKS'},
          {data: 'ESTIMATED_EARNINGS', name: 'ESTIMATED_EARNINGS'},
          {data: 'IMPRESSIONS', name: 'IMPRESSIONS'},
          {data: 'IMPRESSION_CTR', name: 'IMPRESSION_CTR'},
          {data: 'eCPM', name: 'eCPM'},
          {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
          {data: 'MATCH_RATE', name: 'MATCH_RATE'},
          {data: 'SHOW_RATE', name: 'SHOW_RATE'},

        ],
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Chi tiết';
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
      });
      $('#month_input').change(function(){
        table.draw();
      });
    });
    function today(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $('.metrics').text('Date');
      $('.month_input').hide();
      $('.date-range').hide();

      var table = $('.data-report').DataTable({
        order: [[ 0, "desc" ]],
        destroy: true,
        searching: true,
        serverSide: true,
        processing: true,
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        ajax: {
          url: '{{ route('report-today') }}'+id,
          type: "post",
        },
        initComplete: function (settings, json) {
          if(settings._iRecordsTotal == 0){
            Swal.fire({
              text: 'Chưa có dữ liệu ngày hôm nay. Vui lòng cập nhật mới.',
              icon: 'warning',
              timer: 1500,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        },
        columns: [
          {data: 'date', name: 'date'},
          {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
          {data: 'CLICKS', name: 'CLICKS'},
          {data: 'ESTIMATED_EARNINGS', name: 'ESTIMATED_EARNINGS'},
          {data: 'IMPRESSIONS', name: 'IMPRESSIONS'},
          {data: 'IMPRESSION_CTR', name: 'IMPRESSION_CTR'},
          {data: 'eCPM', name: 'eCPM'},
          {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
          {data: 'MATCH_RATE', name: 'MATCH_RATE'},
          {data: 'SHOW_RATE', name: 'SHOW_RATE'},

        ],

        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Chi tiết';
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

      });
    }
    function date(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $('.metrics').text('Date');
      $('.month_input').hide();
      $('.date-range').show();
      var table = $('.data-report').DataTable({
        order: [[ 0, "desc" ]],
        destroy: true,
        searching: true,
        serverSide: true,
        processing: true,
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        ajax: {
          url: '{{ route('report-date') }}'+id,
          type: "post",
          data: function (d) {
            d.month = $('#month_input').val(),
            d.date_range = $('#date-range').val()
          }
        },


        columns: [
          {data: 'date', name: 'date'},
          {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
          {data: 'CLICKS', name: 'CLICKS'},
          {data: 'ESTIMATED_EARNINGS', name: 'ESTIMATED_EARNINGS'},
          {data: 'IMPRESSIONS', name: 'IMPRESSIONS'},
          {data: 'IMPRESSION_CTR', name: 'IMPRESSION_CTR'},
          {data: 'eCPM', name: 'eCPM'},
          {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
          {data: 'MATCH_RATE', name: 'MATCH_RATE'},
          {data: 'SHOW_RATE', name: 'SHOW_RATE'},

        ],

        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Chi tiết';
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
      });
      $('#month_input').change(function(){
        table.draw();
      });
      $('#date-range').change(function(){
        table.draw();
      });
    }
    function month(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $('.metrics').text('MONTH');
      $('.month_input').show();
      $('.date-range').hide();


      var table = $('.data-report').DataTable({
        destroy: true,
        searching: true,
        serverSide: true,
        processing: true,
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        ajax: {
          url: '{{ route('report-month') }}'+id,
          type: "post",
          data: function (d) {
            d.month = $('#month_input').val()
          }
        },
        initComplete: function (settings, json) {
          if(settings._iRecordsTotal == 0){
            Swal.fire({
              text: 'Chưa có dữ liệu. Vui lòng cập nhật mới.',
              icon: 'warning',
              timer: 1500,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        },

        columns: [
          {data: 'month', name: 'month'},
          {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
          {data: 'CLICKS', name: 'CLICKS'},
          {data: 'ESTIMATED_EARNINGS', name: 'ESTIMATED_EARNINGS'},
          {data: 'IMPRESSIONS', name: 'IMPRESSIONS'},
          {data: 'IMPRESSION_CTR', name: 'IMPRESSION_CTR'},
          {data: 'eCPM', name: 'eCPM'},
          {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
          {data: 'MATCH_RATE', name: 'MATCH_RATE'},
          {data: 'SHOW_RATE', name: 'SHOW_RATE'},

        ],
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Chi tiết';
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
      });
      $('#month_input').change(function(){
        table.draw();
      });
    }
    function nagion(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $('.metrics').text('Country');
      $('.month_input').show();
      $('.date-range').hide();



      var table = $('.data-report').DataTable({
        destroy: true,
        searching: true,
        serverSide: true,
        processing: true,
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        ajax: {
          url: '{{ route('report-country') }}'+id,
          type: "post",
          data: function (d) {
            d.month = $('#month_input').val()
          }
        },
        initComplete: function (settings, json) {
          if(settings._iRecordsTotal == 0){
            Swal.fire({
              text: 'Chưa có dữ liệu. Vui lòng cập nhật mới.',
              icon: 'warning',
              timer: 1500,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        },


        columns: [
          {data: 'country', name: 'country'},
          {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
          {data: 'CLICKS', name: 'CLICKS'},
          {data: 'ESTIMATED_EARNINGS', name: 'ESTIMATED_EARNINGS'},
          {data: 'IMPRESSIONS', name: 'IMPRESSIONS'},
          {data: 'IMPRESSION_CTR', name: 'IMPRESSION_CTR'},
          {data: 'eCPM', name: 'eCPM'},
          {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
          {data: 'MATCH_RATE', name: 'MATCH_RATE'},
          {data: 'SHOW_RATE', name: 'SHOW_RATE'},

        ],
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Chi tiết';
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
      });
      $('#month_input').change(function(){
        table.draw();
      });

    }
    function ad_unit(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $('.metrics').text('Ad unit');
      $('.month_input').show();
      $('.date-range').hide();


      var table = $('.data-report').DataTable({
        destroy: true,
        searching: true,
        serverSide: true,
        processing: true,
        dom:
                '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-right"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

        ajax: {
          url: '{{ route('report-ad_unit') }}'+id,
          type: "post",
          data: function (d) {
            d.month = $('#month_input').val()
          }
        },
        initComplete: function (settings, json) {
          if(settings._iRecordsTotal == 0){
            Swal.fire({
              text: 'Chưa có dữ liệu. Vui lòng cập nhật mới.',
              icon: 'warning',
              timer: 1500,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        },

        columns: [
          {data: 'ad_unit', name: 'ad_unit'},
          {data: 'AD_REQUESTS', name: 'AD_REQUESTS'},
          {data: 'CLICKS', name: 'CLICKS'},
          {data: 'ESTIMATED_EARNINGS', name: 'ESTIMATED_EARNINGS'},
          {data: 'IMPRESSIONS', name: 'IMPRESSIONS'},
          {data: 'IMPRESSION_CTR', name: 'IMPRESSION_CTR'},
          {data: 'eCPM', name: 'eCPM'},
          {data: 'MATCHED_REQUESTS', name: 'MATCHED_REQUESTS'},
          {data: 'MATCH_RATE', name: 'MATCH_RATE'},
          {data: 'SHOW_RATE', name: 'SHOW_RATE'},

        ],
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Chi tiết';
              },
              modal: {
                width: '10000px',
                height: '4000px',
                draggable: true
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
      });
      $('#month_input').change(function(){
        table.draw();
      });

    }

  </script>
@endsection
