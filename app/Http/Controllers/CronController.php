<?php

namespace App\Http\Controllers;

use App\Models\AdModAccount;
use App\Models\AdModReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery\CountValidator\Exception;
use function PHPUnit\Framework\isNull;


class CronController extends Controller
{
    //
    protected $client;
    protected $config;
    protected $token;

    function __construct()
    {

    }

    public function getIndex(Request $request)
    {
        $rs = null;
        $array_toview = array();
        $acc_service = null;
        $client = null;
        $service = null;
        if ($request->start == '') {
            if (date("d") >= 2) {
                $request->start = date('Y-m-d', strtotime('first day of this month', time()));
            } else {
                $request->start = date('Y-m-d', strtotime('last month'));
            }
        }
        if ($request->end == '') $request->end = date('Y-m-d', strtotime('this month'));
        $time_to_cron = Carbon::now()->subMinute(10);
        $time_to_cron->setTimezone('Asia/Ho_Chi_Minh');
        echo $time_to_cron . PHP_EOL;
        $admod_account = AdModAccount::where('updated_at', '<=', $time_to_cron)->limit(5)->get();
        if ($admod_account) {
            foreach ($admod_account as $items) {
                $items->updated_at = Carbon::now()->timezone('Asia/Ho_Chi_Minh');
                $items->error = "OK";
                $items->status = 1;
                $items->save();
                echo '<br/>'.'Dang chay Pub ID:' . $items->admod_pub_id . PHP_EOL;
                $client = $this->get_gclient($request, $items);
                try {
                    $refresh = \GuzzleHttp\json_decode($items->access_token_full);
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
                        $d = array();
                        $a = array();
                        $c = array();
                        $ad = array();

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

                } catch (\Google_Service_Exception $ex) {
                    $items->status = 0;
                    $items->error = $ex->GetMessage();
                    $items->save();
                } catch (Exception $ex) {
                    $items->status = 0;
                    $items->error = $ex->GetMessage();
                    $items->save();
                    echo 'getIndex ==> ' . $ex->getMessage();
                }
            }
        }
        if(count($admod_account)==0) {
            echo 'Chưa đến time cron'.PHP_EOL .'<br>';
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

//            $redirect_uri = $_SERVER["HTTP_REFERER"];

            $redirect_uri = $request->fullUrl();
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


    public static function today()
    {
        $dateTime = getdate();
        return self::toDate($dateTime);
    }

    public static function oneWeekBeforeToday()
    {
        $dateTime = getdate(strtotime('-1 week'));
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

}
