
@extends('layouts/contentLayoutMaster')

@section('title', 'Trang chủ')

@section('vendor-style')
  {{-- vendor css files --}}
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/charts/apexcharts.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
@endsection
@section('page-style')
  {{-- Page css files --}}

  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/charts/chart-apex.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-toastr.css')) }}">
@endsection

@section('content')
<!-- Dashboard  Starts -->
<section id="dashboard-index">
  <div class="row match-height">

    <!-- Statistics Card -->
    <div class="col-12">
      <div class="card card-statistics">
        <div class="card-header">
          <h4 class="card-title">Tài khoản AdMod</h4>
        </div>
        <div class="card-body statistics-body">
          <div class="row">
            <div class="col-xl-6 col-sm-6 col-12 mb-2 mb-xl-0">
              <div class="media">
                <div class="avatar bg-light-info mr-2">
                  <div class="avatar-content">
                    <i data-feather="user" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{!!$dataHome['admod_count']!!}</h4>
                  <p class="card-text font-small-3 mb-0">Tổng tài khoản</p>
                </div>
              </div>
            </div>
            <div class="col-xl-6 col-sm-6 col-12 mb-2 mb-sm-0">
              <div class="media">
                <div class="avatar bg-light-danger mr-2">
                  <div class="avatar-content">
                    <i data-feather="eye" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{!!$dataHome['pageview']!!}</h4>
                  <div class="stat-percent font-bold text-{{$dataHome['pageviewpercent']>0?'success':'danger'}}">{{number_format($dataHome['pageviewpercent'],0,'.',',')}}%
                    <i data-feather='arrow-{{$dataHome['pageviewpercent']>0?'up':'down'}}'></i>
                  </div>
                  <p class="card-text font-small-3 mb-0">Tổng lượt xem</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="card card-statistics">
        <div class="card-header">
          <h4 class="card-title">Số tiền kiếm được</h4>
        </div>
        <div class="card-body statistics-body">
          <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
              <div class="media">
                <div class="avatar bg-light-primary mr-2">
                  <div class="avatar-content">
                    <i data-feather="dollar-sign" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{!!$dataHome['earnings']!!} $</h4>
                  <div class="stat-percent font-bold text-{{$dataHome['earningspercent']>0?'success':'danger'}}">{{number_format($dataHome['earningspercent'],0,'.',',')}}%
                       <i data-feather='arrow-{{$dataHome['earningspercent']>0?'up':'down'}}'></i>
                  </div>
                  <p class="card-text font-small-3 mb-0">Ngày Hôm nay</p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
              <div class="media">
                <div class="avatar bg-light-info mr-2">
                  <div class="avatar-content">
                    <i data-feather="dollar-sign" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{!!$dataHome['yesterday']!!} $</h4>
                  <p class="card-text font-small-3 mb-0">Ngày hôm qua</p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-sm-0">
              <div class="media">
                <div class="avatar bg-light-danger mr-2">
                  <div class="avatar-content">
                    <i data-feather="dollar-sign" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{!!$dataHome['thismonth']!!} $</h4>
                  <p class="card-text font-small-3 mb-0">Trong tháng này</p>
                </div>
              </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12">
              <div class="media">
                <div class="avatar bg-light-success mr-2">
                  <div class="avatar-content">
                    <i data-feather="dollar-sign" class="avatar-icon"></i>
                  </div>
                </div>
                <div class="media-body my-auto">
                  <h4 class="font-weight-bolder mb-0">{!!$dataHome['lifetime']!!} $</h4>
                  <p class="card-text font-small-3 mb-0">Toàn thời gian</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Statistics Card -->
  </div>
</section>
<!-- Dashboard Ecommerce ends -->
@endsection

@section('vendor-script')
  {{-- vendor files --}}
  <script src="{{ asset(mix('vendors/js/charts/apexcharts.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
  <script>
    var isRtl = $('html').attr('data-textdirection') === 'rtl';
    setTimeout(function () {
      toastr['success'](
              'You have successfully logged in to VietMMO. Now you can start to explore!',
              '👋 Welcome!',
              {
                closeButton: true,
                tapToDismiss: false,
                rtl: isRtl
              }
      );
    }, 1500);
  </script>
@endsection

