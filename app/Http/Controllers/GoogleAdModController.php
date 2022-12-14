<?php

namespace App\Http\Controllers;

use App\Models\AdModAccount;
use App\Models\AdModReport;
use App\Models\User;
use App\Models\User_admod;
use Carbon\Carbon;
use Google_Service_AdMob;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\CountValidator\Exception;
use Yajra\DataTables\Facades\DataTables;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;
use Illuminate\Support\Facades\URL;


class GoogleAdModController extends Controller
{
    //
    protected $client;
    protected $config;
    protected $token;
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function index()
    {
        $pageConfigs = ['pageHeader' => false];
        $breadcrumbs = [
            ['link' => "/", 'name' => "Home"], ['link' => "javascript:void(0)", 'name' => "Google"], ['name' => "AdMod"]
        ];
        return view('content.api.admod.index', ['pageConfigs' => $pageConfigs, 'breadcrumbs' => $breadcrumbs]);
    }

    public function get_admod_list(Request $request)
    {
        $pageConfigs = ['pageHeader' => false];
        $rs = null;
        $array_toview = array();
        $acc_service = null;
        $client = null;
        if ($request->start == '') {
            if (date("d") >= 2) {
                $request->start = date('Y-m-d', strtotime('first day of this month', time()));
            } else {
                $request->start = date('Y-m-d', strtotime('last month'));
            }
        }
        if ($request->end == '') $request->end = date('Y-m-d', strtotime('this month'));

        if ($request->pub_id != '') {
            $admod_account = AdModAccount::where('admod_pub_id', $request->pub_id)->first();
//            dd($admod_account);
            if ($admod_account) {
                $admod_account->updated_at = Carbon::now();
                $admod_account->save();
                try {
                    $client = $this->get_gclient($request, $admod_account);

                } catch (\Google_Exception $exception) {
                    //  print_r($exception);
                    //  exit();
                }
                try {
                    $refresh = \GuzzleHttp\json_decode($admod_account->access_token_full);
                    $client->refreshToken($refresh->refresh_token);
                    $verify = $client->verifyIdToken();
                    $access_token = $client->getAccessToken();
                    $service = new \Google_Service_AdMob($client);
                    $acc_service = $this->get_user($service);

                    $user_id = $acc_service["id"];
                    $array_data['account'] = $acc_service;


                    $networkReportMonthRequest = self::createNetworkReportMonthRequest();
                    $month = $service->accounts_networkReport->generate('accounts/pub-' . $user_id, $networkReportMonthRequest);
                    $month = $month->tosimpleObject();

                    $networkReportDateRequest = self::createNetworkReportDateRequest();
                    $date = $service->accounts_networkReport->generate('accounts/pub-' . $user_id, $networkReportDateRequest);
                    $date = $date->tosimpleObject();

                    $networkReportAppRequest = self::createNetworkReportAppRequest();
                    $app = $service->accounts_networkReport->generate('accounts/pub-' . $user_id, $networkReportAppRequest);
                    $app = $app->tosimpleObject();

                    $networkReportCountryRequest = self::createNetworkReportCountryRequest();
                    $counth = $service->accounts_networkReport->generate('accounts/pub-' . $user_id, $networkReportCountryRequest);
                    $counth = $counth->tosimpleObject();

                    $networkReportAdUnitRequest = self::createNetworkReportAdUnitRequest();
                    $ad_unit = $service->accounts_networkReport->generate('accounts/pub-' . $user_id, $networkReportAdUnitRequest);
                    $ad_unit = $ad_unit->tosimpleObject();

                    $array_data['report']['month'] = $month;
                    $array_data['report']['date'] = $date;
                    $array_data['report']['app'] = $app;
                    $array_data['report']['country'] = $counth;
                    $array_data['report']['ad_unit'] = $ad_unit;
                    $data =  $array_data['report'];

                    $data = json_decode(json_encode($data),true);
                    if(count($data['date']) >2){


                        unset($data['date'][0]);
                        unset($data['date'][count($data['date'])]);

                        unset($data['month'][0]);
                        unset($data['month'][count($data['month'])]);

                        unset($data['app'][0]);
                        unset($data['app'][count($data['app'])]);

                        unset($data['country'][0]);
                        unset($data['country'][count($data['country'])]);

                        unset($data['ad_unit'][0]);
                        unset($data['ad_unit'][count($data['ad_unit'])]);



                        foreach ($data['date'] as $itemDate){
                            $dataDate['date'] = $itemDate['row']['dimensionValues']['DATE']['value'];
                            if (!isset($itemDate['row']['metricValues']['AD_REQUESTS'])){
                                $dataDate['AD_REQUESTS'] = 0;
                            }else{
                                $dataDate['AD_REQUESTS'] =$itemDate['row']['metricValues']['AD_REQUESTS']['integerValue'];
                            }

                            if (!isset($itemDate['row']['metricValues']['CLICKS'])){
                                $dataDate['CLICKS'] = 0;
                            }else{
                                $dataDate['CLICKS']           =   $itemDate['row']['metricValues']['CLICKS']['integerValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['ESTIMATED_EARNINGS'])){
                                $dataDate['ESTIMATED_EARNINGS'] = 0;
                            }else{
                                $dataDate['ESTIMATED_EARNINGS'] =   $itemDate['row']['metricValues']['ESTIMATED_EARNINGS']['microsValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['IMPRESSIONS'])){
                                $dataDate['IMPRESSIONS'] = 0;
                            }else{
                                $dataDate['IMPRESSIONS']        =   $itemDate['row']['metricValues']['IMPRESSIONS']['integerValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['IMPRESSION_CTR'])){
                                $dataDate['IMPRESSION_CTR'] = 0;
                            }else{
                                $dataDate['IMPRESSION_CTR']     =   $itemDate['row']['metricValues']['IMPRESSION_CTR']['doubleValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['IMPRESSION_RPM'])){
                                $dataDate['IMPRESSION_RPM'] = 0;
                            }else{
                                $dataDate['IMPRESSION_RPM']     =   $itemDate['row']['metricValues']['IMPRESSION_RPM']['doubleValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['MATCHED_REQUESTS'])){
                                $dataDate['MATCHED_REQUESTS'] = 0;
                            }else{
                                $dataDate['MATCHED_REQUESTS']   =   $itemDate['row']['metricValues']['MATCHED_REQUESTS']['integerValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['MATCH_RATE'])){
                                $dataDate['MATCH_RATE'] = 0;
                            }else{
                                $dataDate['MATCH_RATE']         =   $itemDate['row']['metricValues']['MATCH_RATE']['doubleValue'];
                            }
                            if (!isset($itemDate['row']['metricValues']['SHOW_RATE'])){
                                $dataDate['SHOW_RATE']= 0;
                            }else {
                                $dataDate['SHOW_RATE'] = $itemDate['row']['metricValues']['SHOW_RATE']['doubleValue'];
                            }
                            $d[] = $dataDate;

                        }
                        foreach ($data['month'] as $itemMonth){

                            if (!isset($itemMonth['row']['metricValues']['AD_REQUESTS'])){
                                $dataMonth['AD_REQUESTS'] = 0;
                            }else{
                                $dataMonth['AD_REQUESTS'] =$itemMonth['row']['metricValues']['AD_REQUESTS']['integerValue'];
                            }

                            if (!isset($itemMonth['row']['metricValues']['CLICKS'])){
                                $dataMonth['CLICKS'] = 0;
                            }else{
                                $dataMonth['CLICKS']           =   $itemMonth['row']['metricValues']['CLICKS']['integerValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['ESTIMATED_EARNINGS'])){
                                $dataMonth['ESTIMATED_EARNINGS'] = 0;
                            }else{
                                $dataMonth['ESTIMATED_EARNINGS'] =   $itemMonth['row']['metricValues']['ESTIMATED_EARNINGS']['microsValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['IMPRESSIONS'])){
                                $dataMonth['IMPRESSIONS'] = 0;
                            }else{
                                $dataMonth['IMPRESSIONS']        =   $itemMonth['row']['metricValues']['IMPRESSIONS']['integerValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['IMPRESSION_CTR'])){
                                $dataMonth['IMPRESSION_CTR'] = 0;
                            }else{
                                $dataMonth['IMPRESSION_CTR']     =   $itemMonth['row']['metricValues']['IMPRESSION_CTR']['doubleValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['IMPRESSION_RPM'])){
                                $dataMonth['IMPRESSION_RPM'] = 0;
                            }else{
                                $dataMonth['IMPRESSION_RPM']     =   $itemMonth['row']['metricValues']['IMPRESSION_RPM']['doubleValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['MATCHED_REQUESTS'])){
                                $dataMonth['MATCHED_REQUESTS'] = 0;
                            }else{
                                $dataMonth['MATCHED_REQUESTS']   =   $itemMonth['row']['metricValues']['MATCHED_REQUESTS']['integerValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['MATCH_RATE'])){
                                $dataMonth['MATCH_RATE'] = 0;
                            }else{
                                $dataMonth['MATCH_RATE']         =   $itemMonth['row']['metricValues']['MATCH_RATE']['doubleValue'];
                            }
                            if (!isset($itemMonth['row']['metricValues']['SHOW_RATE'])){
                                $dataMonth['SHOW_RATE']= 0;
                            }else {
                                $dataMonth['SHOW_RATE'] = $itemMonth['row']['metricValues']['SHOW_RATE']['doubleValue'];
                            }
                            $m[] = $dataMonth;

                        }

                        foreach ($data['app'] as $itemApp){
                            $dataApp['app'] = $itemApp['row']['dimensionValues']['APP']['displayLabel'];
                            if (!isset($itemApp['row']['metricValues']['AD_REQUESTS'])){
                                $dataApp['AD_REQUESTS'] = 0;
                            }else{
                                $dataApp['AD_REQUESTS'] =$itemApp['row']['metricValues']['AD_REQUESTS']['integerValue'];
                            }

                            if (!isset($itemApp['row']['metricValues']['CLICKS'])){
                                $dataApp['CLICKS'] = 0;
                            }else{
                                $dataApp['CLICKS']           =   $itemApp['row']['metricValues']['CLICKS']['integerValue'];
                            }
                            if (!isset($itemApp['row']['metricValues']['ESTIMATED_EARNINGS'])){
                                $dataApp['ESTIMATED_EARNINGS'] = 0;
                            }else{
                                $dataApp['ESTIMATED_EARNINGS'] =   $itemApp['row']['metricValues']['ESTIMATED_EARNINGS']['microsValue'];
                            }
                            if (!isset($itemApp['row']['metricValues']['IMPRESSIONS'])){
                                $dataApp['IMPRESSIONS'] = 0;
                            }else{
                                $dataApp['IMPRESSIONS']        =   $itemApp['row']['metricValues']['IMPRESSIONS']['integerValue'];
                            }
                            if (!isset($item['row']['metricValues']['IMPRESSION_CTR'])){
                                $dataApp['IMPRESSION_CTR'] = 0;
                            }else{
                                $dataApp['IMPRESSION_CTR']     =   $itemApp['row']['metricValues']['IMPRESSION_CTR']['doubleValue'];
                            }
                            if (!isset($itemApp['row']['metricValues']['IMPRESSION_RPM'])){
                                $dataApp['IMPRESSION_RPM'] = 0;
                            }else{
                                $dataApp['IMPRESSION_RPM']     =   $itemApp['row']['metricValues']['IMPRESSION_RPM']['doubleValue'];
                            }
                            if (!isset($itemApp['row']['metricValues']['MATCHED_REQUESTS'])){
                                $dataApp['MATCHED_REQUESTS'] = 0;
                            }else{
                                $dataApp['MATCHED_REQUESTS']   =   $itemApp['row']['metricValues']['MATCHED_REQUESTS']['integerValue'];
                            }
                            if (!isset($itemApp['row']['metricValues']['MATCH_RATE'])){
                                $dataApp['MATCH_RATE'] = 0;
                            }else{
                                $dataApp['MATCH_RATE']         =   $itemApp['row']['metricValues']['MATCH_RATE']['doubleValue'];
                            }
                            if (!isset($itemApp['row']['metricValues']['SHOW_RATE'])){
                                $dataApp['SHOW_RATE']= 0;
                            }else {
                                $dataApp['SHOW_RATE'] = $itemApp['row']['metricValues']['SHOW_RATE']['doubleValue'];
                            }
                            $a[] = $dataApp;

                        }

                        foreach ($data['country'] as $itemCountry){
                            $dataCountry['country']  = $itemCountry['row']['dimensionValues']['COUNTRY']['value'];
                            if (!isset($itemCountry['row']['metricValues']['AD_REQUESTS'])){
                                $dataCountry['AD_REQUESTS'] = 0;
                            }else{
                                $dataCountry['AD_REQUESTS'] =$itemCountry['row']['metricValues']['AD_REQUESTS']['integerValue'];
                            }

                            if (!isset($itemCountry['row']['metricValues']['CLICKS'])){
                                $dataCountry['CLICKS'] = 0;
                            }else{
                                $dataCountry['CLICKS']           =   $itemCountry['row']['metricValues']['CLICKS']['integerValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['ESTIMATED_EARNINGS'])){
                                $dataCountry['ESTIMATED_EARNINGS'] = 0;
                            }else{
                                $dataCountry['ESTIMATED_EARNINGS'] =   $itemCountry['row']['metricValues']['ESTIMATED_EARNINGS']['microsValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['IMPRESSIONS'])){
                                $dataCountry['IMPRESSIONS'] = 0;
                            }else{
                                $dataCountry['IMPRESSIONS']        =   $itemCountry['row']['metricValues']['IMPRESSIONS']['integerValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['IMPRESSION_CTR'])){
                                $dataCountry['IMPRESSION_CTR'] = 0;
                            }else{
                                $dataCountry['IMPRESSION_CTR']     =   $itemCountry['row']['metricValues']['IMPRESSION_CTR']['doubleValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['IMPRESSION_RPM'])){
                                $dataCountry['IMPRESSION_RPM'] = 0;
                            }else{
                                $dataCountry['IMPRESSION_RPM']     =   $itemCountry['row']['metricValues']['IMPRESSION_RPM']['doubleValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['MATCHED_REQUESTS'])){
                                $dataCountry['MATCHED_REQUESTS'] = 0;
                            }else{
                                $dataCountry['MATCHED_REQUESTS']   =   $itemCountry['row']['metricValues']['MATCHED_REQUESTS']['integerValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['MATCH_RATE'])){
                                $dataCountry['MATCH_RATE'] = 0;
                            }else{
                                $dataCountry['MATCH_RATE']         =   $itemCountry['row']['metricValues']['MATCH_RATE']['doubleValue'];
                            }
                            if (!isset($itemCountry['row']['metricValues']['SHOW_RATE'])){
                                $dataCountry['SHOW_RATE']= 0;
                            }else {
                                $dataCountry['SHOW_RATE'] = $itemCountry['row']['metricValues']['SHOW_RATE']['doubleValue'];
                            }
                            $c[] = $dataCountry;

                        }

                        foreach ($data['ad_unit'] as $itemAd_Unit){
                            $dataAd_Unit['ad_unit']  = $itemAd_Unit['row']['dimensionValues']['APP']['displayLabel'].' - '.$itemAd_Unit['row']['dimensionValues']['AD_UNIT']['displayLabel'];
                            if (!isset($itemAd_Unit['row']['metricValues']['AD_REQUESTS'])){
                                $dataAd_Unit['AD_REQUESTS'] = 0;
                            }else{
                                $dataAd_Unit['AD_REQUESTS'] =$itemAd_Unit['row']['metricValues']['AD_REQUESTS']['integerValue'];
                            }

                            if (!isset($itemAd_Unit['row']['metricValues']['CLICKS'])){
                                $dataAd_Unit['CLICKS'] = 0;
                            }else{
                                $dataAd_Unit['CLICKS']           =   $itemAd_Unit['row']['metricValues']['CLICKS']['integerValue'];
                            }
                            if (!isset($itemAd_Unit['row']['metricValues']['ESTIMATED_EARNINGS'])){
                                $dataAd_Unit['ESTIMATED_EARNINGS'] = 0;
                            }else{
                                $dataAd_Unit['ESTIMATED_EARNINGS'] =   $itemAd_Unit['row']['metricValues']['ESTIMATED_EARNINGS']['microsValue'];
                            }
                            if (!isset($itemAd_Unit['row']['metricValues']['IMPRESSIONS'])){
                                $dataAd_Unit['IMPRESSIONS'] = 0;
                            }else{
                                $dataAd_Unit['IMPRESSIONS']        =   $itemAd_Unit['row']['metricValues']['IMPRESSIONS']['integerValue'];
                            }
                            if (!isset($item['row']['metricValues']['IMPRESSION_CTR'])){
                                $dataAd_Unit['IMPRESSION_CTR'] = 0;
                            }else{
                                $dataAd_Unit['IMPRESSION_CTR']     =   $itemAd_Unit['row']['metricValues']['IMPRESSION_CTR']['doubleValue'];
                            }
                            if (!isset($itemAd_Unit['row']['metricValues']['IMPRESSION_RPM'])){
                                $dataAd_Unit['IMPRESSION_RPM'] = 0;
                            }else{
                                $dataAd_Unit['IMPRESSION_RPM']     =   $itemAd_Unit['row']['metricValues']['IMPRESSION_RPM']['doubleValue'];
                            }
                            if (!isset($itemAd_Unit['row']['metricValues']['MATCHED_REQUESTS'])){
                                $dataAd_Unit['MATCHED_REQUESTS'] = 0;
                            }else{
                                $dataAd_Unit['MATCHED_REQUESTS']   =   $itemAd_Unit['row']['metricValues']['MATCHED_REQUESTS']['integerValue'];
                            }
                            if (!isset($itemAd_Unit['row']['metricValues']['MATCH_RATE'])){
                                $dataAd_Unit['MATCH_RATE'] = 0;
                            }else{
                                $dataAd_Unit['MATCH_RATE']         =   $itemAd_Unit['row']['metricValues']['MATCH_RATE']['doubleValue'];
                            }
                            if (!isset($itemAd_Unit['row']['metricValues']['SHOW_RATE'])){
                                $dataAd_Unit['SHOW_RATE']= 0;
                            }else {
                                $dataAd_Unit['SHOW_RATE'] = $itemAd_Unit['row']['metricValues']['SHOW_RATE']['doubleValue'];
                            }
                            $ad[] = $dataAd_Unit;

                        }
                        $d = json_encode($d);
                        $a = json_encode($a);
                        $c = json_encode($c);
                        $ad = json_encode($ad);


                        AdModReport::updateOrCreate(
                            [
                                "pub_id" => $user_id,
                                "month" => Carbon::now()->format('m-Y')

                            ],
                            [
                                "date" => $d,
                                'app' => $a,
                                'ad_unit' => $ad,
                                'country' => $c,
                                'AD_REQUESTS' => $dataMonth['AD_REQUESTS'],
                                'CLICKS' =>  $dataMonth['CLICKS'],
                                'ESTIMATED_EARNINGS' =>  $dataMonth['ESTIMATED_EARNINGS'],
                                'IMPRESSIONS' =>  $dataMonth['IMPRESSIONS'],
                                'IMPRESSION_CTR' =>  $dataMonth['IMPRESSION_CTR'],
                                'eCPM' =>  $dataMonth['IMPRESSION_RPM'],
                                'MATCHED_REQUESTS' =>  $dataMonth['MATCHED_REQUESTS'],
                                'MATCH_RATE' =>  $dataMonth['MATCH_RATE'],
                                "SHOW_RATE" =>  $dataMonth['SHOW_RATE'],
                                "updated_at" => Carbon::now('Asia/Ho_Chi_Minh')
                            ]);
                    }


                } catch (Exception $ex) {
                    Log::error($ex->getMessage());
                }
                echo '<META http-equiv="refresh" content="0;URL=' . url("api/admod/show?pub_id=") .$user_id. '">';
            } else {
                echo '<META http-equiv="refresh" content="0;URL=' . url("api/admod") . '">';
            }
        }
        else {
            echo '<META http-equiv="refresh" content="0;URL=' . url("api/admod") . '">';
        }

    }

    public function post_admod_list(Request $request)
    {
        $users = Auth::user()->admods;
        foreach ($users as $user) {
            $user_id[] = $user->id;
        }
        if(!isset($user_id)){
            return response()->json(['error' => 'không có dữ liệu']);
        }
        $admod_account  = AdModAccount::whereIn('id', $user_id)->get();
        $arrayjson = array();
        $arrayreturn = array();

        foreach ($admod_account as $key=>$item) {
            if ($item->error != 'OK') {
                $action = '<a href="javascript:void(0)" onclick="editGa('.$item->id.')" class="btn btn-warning">Edit</i></a>';

                $action .= ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$item->id.'" data-original-title="Delete" class="btn btn-danger deleteAdmod">Del</i></a>';
            } else {
                $action = '<a href="javascript:void(0)" onclick="editGa('.$item->id.')" class="btn btn-warning">Edit</i></a>';
                $action .= '  <a class="btn btn-outline btn-success" href="admod/show?pub_id=' . $item->admod_pub_id . '"  >View</a>';
            }

            $report = $this->getReportToday($item->admod_pub_id);
            $total_month = $this->getReportThisMonth($item->admod_pub_id);
            $arrayjson[$key]['id'] = $item->id;
            $arrayjson[$key]['admod_pub_id'] = $item->admod_pub_id;
            $arrayjson[$key]['AD_REQUESTS'] = $report ? number_format($report['AD_REQUESTS']) : 0;
            $arrayjson[$key]['CLICKS'] = $report ? number_format($report['CLICKS']) : 0;
            $arrayjson[$key]['eCPM'] = $report ? number_format($report['IMPRESSION_RPM'], 2) .' $' : 0;
            $arrayjson[$key]['MATCHED_REQUESTS'] = $report ? number_format($report['MATCHED_REQUESTS']) : 0;
            $arrayjson[$key]['total_month'] = $total_month ? number_format($total_month/1000000, 3).' $' : 0 ;
            $arrayjson[$key]['note'] = $item->note;
            $arrayjson[$key]["action"] = $action;
            $arrayjson[$key]["status"] = $item->status;

        }
        return json_encode(array('data' => $arrayjson, 'draw' => 1, 'recordsTotal' => count($arrayreturn), 'recordsFiltered' => count($arrayreturn)));
    }

    public function getReportToday($pub_id)
    {
        $report_all = DB::table('admod_report')
            ->select('date')
            ->where('pub_id',$pub_id)
            ->where('month',Carbon::now('Asia/Ho_Chi_Minh')->format('m-Y'))
            ->get();
        foreach ($report_all as $record) {
            $record = json_decode($record->date,true);
            $item = $record[count($record)-1];
            $today = Carbon::now('Asia/Ho_Chi_Minh')->format('Ymd');
            if($item['date'] == $today ) {
                return $item;
            }
            return 0;
        }
    }

    public function getReportThisMonth($pub_id){
        $records= AdModReport::select('month','ESTIMATED_EARNINGS')
            ->where('pub_id',$pub_id)
            ->where('month',Carbon::now('Asia/Ho_Chi_Minh')->format('m-Y'))
            ->get();
        if(count($records)>0) {
            $record = $records[count($records) - 1];
            return $record->ESTIMATED_EARNINGS;
        }else{
            return 0;
        }
    }
    public function delete($id)
    {
        AdModAccount::find($id)->delete();
        return response()->json(['success'=>'Đã xóa thành công.']);
    }

    function post_add_ga(Request $request)
    {
        if (isset($request->id)) {
            $rules = [
                'g_client_id' => 'required',
                'g_secret' => 'required',
            ];
            $message = [
                'g_secret.required' => 'Google client id không được để trống',
                'g_client_id.required' => 'Google secret không được để trống',
            ];
            $error = Validator::make($request->all(), $rules, $message);
            if ($error->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $error->getMessageBag()
                ]);
            }
            $admod_account = AdModAccount::where('id', $request->id)->first();
            $admod_account->g_client_id = $request->g_client_id;
            $admod_account->g_secret = $request->g_secret;
            $admod_account->g_dev_key = $request->g_dev_key;
            $admod_account->note = $request->note;
            $admod_account->save();
        } else {
            $rules = [
                'g_client_id' => 'required',
                'g_secret' => 'required',
            ];
            $message = [
                'g_secret.required' => 'Google client id không được để trống',
                'g_client_id.required' => 'Google secret không được để trống',
            ];
            $error = Validator::make($request->all(), $rules, $message);

            if ($error->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $error->getMessageBag()
                ]);
            }
            $admod_account = AdModAccount::create([
                'g_client_id' => $request->g_client_id,
                'g_secret' => $request->g_secret,
                'g_dev_key' => $request->g_dev_key,
                'note' => $request->note,
            ]);
            $user_admod = User_admod::create([
                'user_id' => Auth::user()->id,
                'admod_id' => $admod_account->id
            ]);


        }
        return response()->json(['success' => 'Thêm mới thành công', 'id' => $admod_account->id]);
    }

    function get_add_ga($id)
    {

        if (isset($id)) {
            $admod_account = AdModAccount::where('id', $id)->first();
            if (isset($admod_account)) {
                return response()->json(['success' => 'Cập nhật thành công', $admod_account]);
            }
            return response()->json(['errors' => 'ID không tồn tại']);
        }
    }

    function get_get_token_callback(Request $request)
    {
        $admod_account = AdModAccount::where('id', $request->state)->first();
        $g_client = $this->get_gclient($request, $admod_account);

        if (isset($request->code) && $request->code != '') {
            $code = $request->code;
            $auth_result = $g_client->authenticate($code);

//            Log::debug("auth_result: ".$auth_result['auth_result']);
            $access_token = $g_client->getAccessToken();
//            Log::debug('access_token:'.$access_token);
//            $service = new Google_Service_AdSense($g_client);
            $service = new \Google_Service_AdMob($g_client);
            $acc_service = $this->get_user($service);

            $user_name = $acc_service["name"];
            $user_id = $acc_service["id"];
            //$adsense_account=AdsenseAccount::where('adsense_pub_id',$user_id)->first();
            $admod_account->admod_pub_id = $user_id;
            $admod_account->admod_name = $user_name;
            $admod_account->access_token_full = \GuzzleHttp\json_encode($access_token);
            $admod_account->access_token = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($access_token))->access_token;
            $admod_account->save();
            echo '<META http-equiv="refresh" content="0;URL=' . url("/api/admod") . '">';
            return;

        }
        $authUrl = $g_client->createAuthUrl();

        echo '<META http-equiv="refresh" content="0;URL=' . $authUrl . '">';
        return;
    }

    function get_get_ga_token(Request $request)
    {

        if (isset($request->id)) {
            $admod_account = AdModAccount::where('id', $request->id)->first();
        }

        $g_client = $this->get_gclient($request, $admod_account);

        if (isset($request->code) && $request->code != '') {
            $code = $request->code;
            $auth_result = $g_client->authenticate($code);

            Log::debug("auth_result: " . $auth_result['auth_result']);
            $access_token = $g_client->getAccessToken();
            Log::debug('access_token:' . $access_token);
//            $service = new \Google_Service_AdSense($g_client);
            $service = new Google_Service_AdMob($g_client);
            $acc_service = $this->get_user($service);

            $user_name = $acc_service["name"];
            $user_id = $acc_service["id"];
            $admod_account = AdModAccount::where('admod_pub_id', $user_id)->first();
            if (count($admod_account) > 0) {
                $admod_account->access_token_full = \GuzzleHttp\json_encode($access_token);
                $admod_account->access_token = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($access_token))->access_token;
                $admod_account->save();
            } else {
                AdModAccount::create([
                    'admod_pub_id' => $user_id,
                    'adsmod_name' => $user_name,
                    'access_token_full' => \GuzzleHttp\json_encode($access_token),
                    'access_token' => \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($access_token))->access_token
                ]);
            }
            echo '<META http-equiv="refresh" content="0;URL=' . url("/api/admod") . '">';
            return;

        }
        $authUrl = $g_client->createAuthUrl();

        echo '<META http-equiv="refresh" content="0;URL=' . $authUrl . '">';
        return;
    }
    function get_user($service)
    {
        try {
            $info = $service->accounts;
            $item = $info->listAccounts();
            $acc = $item['account'][0];
            $result["id"] = preg_replace("/[^0-9]/", "", $acc['name']);
            $result["name"] = $acc->getName();
            return $result;
        } catch (\Exception $exception) {
            echo 'get_user ==> ' . $exception->getMessage();
        }
    }
    function get_gclient($request, $admod_account)
    {
        $g_client = new \Google_Client();
        try {
            $this->config = Config::get('admod');
            // set application name
            $g_client->setApplicationName(Arr::get($this->config, 'application_name', ''));

            // set oauth2 configs
            $g_client->setClientId($admod_account->g_client_id);
            $g_client->setClientSecret($admod_account->g_secret);

            $redirect_uri = URL::current();

            // $redirect_uri = $request->fullUrl();
            $redirect_uri = substr($redirect_uri, 0, strrpos($redirect_uri, '/'));
            $redirect_uri .= "/get-token-callback";

            Log::debug('redirect_uri: ' . $redirect_uri);

            $g_client->setRedirectUri($redirect_uri);
            $g_client->setState(array('a_id' => $admod_account->id));
            $g_client->setScopes(Arr::get($this->config, 'scopes', []));
            $g_client->setAccessType(Arr::get($this->config, 'access_type', 'offline'));
            $g_client->setApprovalPrompt('force');
            $g_client->setRequestVisibleActions('http://schema.org/AddAction');
            $g_client->setDeveloperKey($admod_account->g_dev_key);

        } catch (\Google_Exception $exception) {
            print_r($exception);
        }
        return $g_client;

    }
    public static function createNetworkReportMonthRequest()
    {
        $startDate = self::oneMonth();
        $endDate = self::today();
        // Specify date range.
        $dateRange = new \Google_Service_AdMob_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        // Create network report specification.
        $reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
        $reportSpec->setMetrics(['AD_REQUESTS', 'CLICKS','ESTIMATED_EARNINGS','IMPRESSIONS','IMPRESSION_CTR','IMPRESSION_RPM','MATCHED_REQUESTS','MATCH_RATE','SHOW_RATE']);
//        $reportSpec->setDimensions(['DATE','APP','COUNTRY','AD_UNIT']);
        $reportSpec->setDimensions(['MONTH']);
        $reportSpec->setDateRange($dateRange);

        // Create network report request.
        $networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
        $networkReportRequest->setReportSpec($reportSpec);

        return $networkReportRequest;
    }
    public static function createNetworkReportDateRequest()
    {
        $startDate = self::oneMonth();
        $endDate = self::today();
        // Specify date range.
        $dateRange = new \Google_Service_AdMob_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        // Create network report specification.
        $reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
        $reportSpec->setMetrics(['AD_REQUESTS', 'CLICKS','ESTIMATED_EARNINGS','IMPRESSIONS','IMPRESSION_CTR','IMPRESSION_RPM','MATCHED_REQUESTS','MATCH_RATE','SHOW_RATE']);
//        $reportSpec->setDimensions(['DATE','APP','COUNTRY','AD_UNIT']);
        $reportSpec->setDimensions(['DATE']);
        $reportSpec->setDateRange($dateRange);

        // Create network report request.
        $networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
        $networkReportRequest->setReportSpec($reportSpec);

        return $networkReportRequest;
    }
    public static function createNetworkReportAppRequest()
    {
        $startDate = self::oneMonth();
        $endDate = self::today();
        // Specify date range.
        $dateRange = new \Google_Service_AdMob_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        // Create network report specification.
        $reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
        $reportSpec->setMetrics(['AD_REQUESTS', 'CLICKS','ESTIMATED_EARNINGS','IMPRESSIONS','IMPRESSION_CTR','IMPRESSION_RPM','MATCHED_REQUESTS','MATCH_RATE','SHOW_RATE']);
//        $reportSpec->setDimensions(['DATE','APP','COUNTRY','AD_UNIT']);
        $reportSpec->setDimensions(['APP']);
        $reportSpec->setDateRange($dateRange);

        // Create network report request.
        $networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
        $networkReportRequest->setReportSpec($reportSpec);

        return $networkReportRequest;
    }
    public static function createNetworkReportCountryRequest()
    {
        $startDate = self::oneMonth();
        $endDate = self::today();
        // Specify date range.
        $dateRange = new \Google_Service_AdMob_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        // Create network report specification.
        $reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
        $reportSpec->setMetrics(['AD_REQUESTS', 'CLICKS','ESTIMATED_EARNINGS','IMPRESSIONS','IMPRESSION_CTR','IMPRESSION_RPM','MATCHED_REQUESTS','MATCH_RATE','SHOW_RATE']);
//        $reportSpec->setDimensions(['DATE','APP','COUNTRY','AD_UNIT']);
        $reportSpec->setDimensions(['COUNTRY']);
        $reportSpec->setDateRange($dateRange);

        // Create network report request.
        $networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
        $networkReportRequest->setReportSpec($reportSpec);

        return $networkReportRequest;
    }
    public static function createNetworkReportAdUnitRequest()
    {
        $startDate = self::oneMonth();
        $endDate = self::today();
        // Specify date range.
        $dateRange = new \Google_Service_AdMob_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        // Create network report specification.
        $reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
        $reportSpec->setMetrics(['AD_REQUESTS', 'CLICKS','ESTIMATED_EARNINGS','IMPRESSIONS','IMPRESSION_CTR','IMPRESSION_RPM','MATCHED_REQUESTS','MATCH_RATE','SHOW_RATE']);
//        $reportSpec->setDimensions(['DATE','APP','COUNTRY','AD_UNIT']);
        $reportSpec->setDimensions(['AD_UNIT']);
        $reportSpec->setDateRange($dateRange);

        // Create network report request.
        $networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
        $networkReportRequest->setReportSpec($reportSpec);

        return $networkReportRequest;
    }

    public static function today()
    {
        $dateTime = getdate();
        return self::toDate($dateTime);
    }
    private static function toDate($dateTime)
    {
        $date = new \Google_Service_AdMob_Date();
        $date->setDay($dateTime['mday']);
        $date->setMonth($dateTime['mon']);
        $date->setYear($dateTime['year']);
        return $date;
    }
    public static function oneMonth()
    {
        $dateTime = getdate(strtotime('now'));
        $dateTime['mday'] = 1;
        return self::toDate($dateTime);
    }
    public function showAdmod(Request $request){
        $pubId = $request->pub_id;
        $data['user'] = AdModAccount::where('admod_pub_id',$pubId)->first();
        $data['metrics'] = array('Yêu cầu mạng','CLICKS','Thu nhập ước tính','Số lần hiện thị','CTR hiện thị','IMPRESSION_RPM','Số yêu cầu đã so khớp','Tỷ lệ so khớp','Tỷ lệ hiện thị');
        $data['records'] =  AdModReport::where('pub_id',$pubId) ->get();
        return view('content.api.admod.admod-report',$data);
    }
    public function getReportMonth(Request $request){
        $pubId = $request->pub_id;
            $records= AdModReport::where('pub_id',$pubId)
                ->where('month',$request->month)
                ->get();
        $data_arr = array();
        if($records->count()==0){
            return Datatables::of($data_arr)->make(true);
        }
        foreach ($records as $key=>$record) {
            $data_arr[] = array(
                "month" =>  $record->month,
                "AD_REQUESTS" => $record->AD_REQUESTS,
                "CLICKS" => $record->CLICKS,
                "ESTIMATED_EARNINGS" => number_format($record->ESTIMATED_EARNINGS/1000000,3).' $',
                "IMPRESSIONS" => $record->IMPRESSIONS,
                "IMPRESSION_CTR" => number_format($record->IMPRESSION_CTR,4)* 100 .'%' ,
                "eCPM" => number_format($record->eCPM,3).' $',
                "MATCHED_REQUESTS" => $record->MATCHED_REQUESTS,
                "MATCH_RATE" => number_format($record->MATCH_RATE,4)* 100 .'%',
                "SHOW_RATE" => number_format($record->SHOW_RATE,4)* 100 .'%',
            );
        }
        return Datatables::of($data_arr)->make(true);
    }
    public function getReportDate(Request $request){
        $date =  explode(' to ',$request->date_range);

        $pubId = $request->pub_id;
            $records = DB::table('admod_report')
                ->select('date')
                ->where('pub_id',$pubId)
                ->get();
        $data_arr = array();
        if($records->count()==0){
            return Datatables::of($data_arr)->make(true);
        }
        foreach ($records as $i=>$record) {
            $record = json_decode($record->date,true);
            foreach ($record as $item){
                $data_arr[] = array(
                    "date" =>date('d-m-Y',strtotime($item['date'])),
                    "AD_REQUESTS" => $item['AD_REQUESTS'],
                    "CLICKS" => $item['CLICKS'],
                    "ESTIMATED_EARNINGS" => number_format($item['ESTIMATED_EARNINGS']/1000000,3).' $',
                    "IMPRESSIONS" => $item['IMPRESSIONS'],
                    "IMPRESSION_CTR" => number_format($item['IMPRESSION_CTR'],4)* 100 .'%' ,
                    "eCPM" => number_format($item['IMPRESSION_RPM'],3).' $',
                    "MATCHED_REQUESTS" => $item['MATCHED_REQUESTS'],
                    "MATCH_RATE" => number_format($item['MATCH_RATE'],4)* 100 .'%',
                    "SHOW_RATE" => number_format($item['SHOW_RATE'],4)* 100 .'%',
                );
            }
        }
        $returned_array = [];

        foreach($data_arr as $array_item)
        {
            $price = $array_item["date"];
            if(array_key_exists(1,$date)){
                if(strtotime($price) >= strtotime($date[0]) && strtotime($price) <= strtotime($date[1])) $returned_array[] = $array_item;
            }else{
                if(strtotime($price) == strtotime($date[0])) $returned_array[] = $array_item;
            }
        }
        return Datatables::of($returned_array)->make(true);

    }
    public function getReportDay(Request $request){
        $pubId = $request->pub_id;
        $records = DB::table('admod_report')
            ->select('date')
            ->where('pub_id',$pubId)
            ->where('month',Carbon::now()->format('m-Y'))
            ->get();
        $data_arr = array();
        if($records->count()==0){
            return Datatables::of($data_arr)->make(true);
        }
        else{
            foreach ($records as $record) {
                $record = json_decode($record->date,true);
                $item = $record[count($record)-1];
                $today = Carbon::now('Asia/Ho_Chi_Minh')->format('Ymd');
                if($item['date'] == $today ){
                    $data_arr[] = array(
                        "date" =>date('d-m-Y',strtotime($record[count($record)-1]['date'])),
                        "AD_REQUESTS" => $record[count($record)-1]['AD_REQUESTS'],
                        "CLICKS" => $record[count($record)-1]['CLICKS'],
                        "ESTIMATED_EARNINGS" => number_format($record[count($record)-1]['ESTIMATED_EARNINGS']/1000000,3).' $',
                        "IMPRESSIONS" => $record[count($record)-1]['IMPRESSIONS'],
                        "IMPRESSION_CTR" => number_format($record[count($record)-1]['IMPRESSION_CTR'],3) * 100 .'%' ,
                        "eCPM" => number_format($record[count($record)-1]['IMPRESSION_RPM'],3).' $',
                        "MATCHED_REQUESTS" => $record[count($record)-1]['MATCHED_REQUESTS'],
                        "MATCH_RATE" => number_format($record[count($record)-1]['MATCH_RATE'],3).'%' ,
                        "SHOW_RATE" => number_format($record[count($record)-1]['SHOW_RATE'],3).'%' ,
                    );
                }
                return Datatables::of($data_arr)->make(true);
            }
        }

    }
    public function getReportApp(Request $request){
        $pubId = $request->pub_id;
        $records = DB::table('admod_report')
            ->select('app')
            ->where('pub_id',$pubId)
            ->where('month',$request->month)
            ->get();
        $data_arr = array();
        if($records->count()==0){
            return Datatables::of($data_arr)->make(true);
        }
        foreach ($records as $record) {
            $record = json_decode($record->app,true);
            foreach ($record as $item){
                $data_arr[] = array(
                    "app" =>$item['app'],
                    "AD_REQUESTS" => $item['AD_REQUESTS'],
                    "CLICKS" => $item['CLICKS'],
                    "ESTIMATED_EARNINGS" => number_format($item['ESTIMATED_EARNINGS']/1000000,3).' $',
                    "IMPRESSIONS" => $item['IMPRESSIONS'],
                    "IMPRESSION_CTR" => number_format($item['IMPRESSION_CTR'],4)* 100 .'%' ,
                    "eCPM" => number_format($item['IMPRESSION_RPM'],3).' $',
                    "MATCHED_REQUESTS" => $item['MATCHED_REQUESTS'],
                    "MATCH_RATE" => number_format($item['MATCH_RATE'],4)* 100 .'%',
                    "SHOW_RATE" => number_format($item['SHOW_RATE'],4)* 100 .'%',
                );
            }
        }
        return Datatables::of($data_arr)->make(true);
    }
    public function getReportCountry(Request $request){
        $pubId = $request->pub_id;
        $records = DB::table('admod_report')
            ->select('country')
            ->where('pub_id',$pubId)
            ->where('month',$request->month)
            ->get();
        $data_arr = array();
        if($records->count()==0){
            return Datatables::of($data_arr)->make(true);
        }
        foreach ($records as $record) {
            $record = json_decode($record->country,true);
            foreach ($record as $item){
                $data_arr[] = array(
                    "country" =>$item['country'],
                    "AD_REQUESTS" => $item['AD_REQUESTS'],
                    "CLICKS" => $item['CLICKS'],
                    "ESTIMATED_EARNINGS" => number_format($item['ESTIMATED_EARNINGS']/1000000,3).' $',
                    "IMPRESSIONS" => $item['IMPRESSIONS'],
                    "IMPRESSION_CTR" => number_format($item['IMPRESSION_CTR'],4)* 100 .'%' ,
                    "eCPM" => number_format($item['IMPRESSION_RPM'],3).' $',
                    "MATCHED_REQUESTS" => $item['MATCHED_REQUESTS'],
                    "MATCH_RATE" => number_format($item['MATCH_RATE'],4)* 100 .'%',
                    "SHOW_RATE" => number_format($item['SHOW_RATE'],4)* 100 .'%',
                );
            }
        }
        return Datatables::of($data_arr)->make(true);

    }
    public function getReportAd_unit(Request $request){
        $pubId = $request->pub_id;
        $records = DB::table('admod_report')
            ->select('ad_unit')
            ->where('pub_id',$pubId)
            ->where('month',$request->month)
            ->get();
        $data_arr = array();
        if($records->count()==0){
            return Datatables::of($data_arr)->make(true);
        }
        foreach ($records as $record) {
            $record = json_decode($record->ad_unit,true);
            foreach ($record as $item){
                $data_arr[] = array(
                    "ad_unit" =>$item['ad_unit'],
                    "AD_REQUESTS" => $item['AD_REQUESTS'],
                    "CLICKS" => $item['CLICKS'],
                    "ESTIMATED_EARNINGS" => number_format($item['ESTIMATED_EARNINGS']/1000000,3).' $',
                    "IMPRESSIONS" => $item['IMPRESSIONS'],
                    "IMPRESSION_CTR" => number_format($item['IMPRESSION_CTR'],4)* 100 .'%' ,
                    "eCPM" => number_format($item['IMPRESSION_RPM'],3).' $',
                    "MATCHED_REQUESTS" => $item['MATCHED_REQUESTS'],
                    "MATCH_RATE" => number_format($item['MATCH_RATE'],4)* 100 .'%',
                    "SHOW_RATE" => number_format($item['SHOW_RATE'],4)* 100 .'%',
                );
            }
        }
        return Datatables::of($data_arr)->make(true);
    }

}