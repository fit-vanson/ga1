<?php

namespace App\Http\Controllers;

use App\Models\AdModAccount;
use App\Models\AdModReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $total_month=$this->getReportThisMonth();
        $today_date = Carbon::now('Asia/Ho_chi_minh')->format('Ymd');
        $yesterday_date = Carbon::yesterday()->format('Ymd');
        $users = Auth::user()->admods;
        foreach ($users as $user) {
            $user_id[] = $user->id;
        }
        if(!isset($user_id)){
            return 'Tài khoản chưa xem được admod';
        }
        $count_admod  = DB::table('admod_accounts')
            ->where("error", '=', "OK")
            ->whereIn('id', $user_id)
            ->count();
        $account_admod_ok=AdModAccount::where("error", '=', "OK")->whereIn('id', $user_id)->select("admod_pub_id")->get()->toArray();
        $total_array = AdModReport::select('date')
            ->whereIn("pub_id",$account_admod_ok)
            ->get();
        $data_arr = array();
        foreach ($total_array as $data ){
            $data = json_decode($data->date,true);
            foreach ($data as $item){
                $data_arr[] = array(
                    "date" =>$item['date'],
                    "ESTIMATED_EARNINGS" => $item['ESTIMATED_EARNINGS'],
                    "AD_REQUESTS" => $item['AD_REQUESTS'],
                    "CLICKS" => $item['CLICKS'],
                    "IMPRESSIONS" => $item['IMPRESSIONS'],
                    "IMPRESSION_CTR" => $item['IMPRESSION_CTR'] ,
                    "eCPM" => $item['IMPRESSION_RPM'],
                    "MATCHED_REQUESTS" => $item['MATCHED_REQUESTS'],
                    "MATCH_RATE" => $item['MATCH_RATE'],
                    "SHOW_RATE" => $item['SHOW_RATE'],
                );
            }
        }
        $date_keys = [];
        array_walk($data_arr, function($v) use(&$date_keys){
            $datePart = $v['date'];
            if (isset($date_keys[$datePart])) {
                $date_keys[$datePart]['ESTIMATED_EARNINGS'] +=$v['ESTIMATED_EARNINGS'];
                $date_keys[$datePart]['AD_REQUESTS'] +=$v['AD_REQUESTS'];
                $date_keys[$datePart]['CLICKS'] +=$v['CLICKS'];
                $date_keys[$datePart]['IMPRESSIONS'] +=$v['IMPRESSIONS'];
                $date_keys[$datePart]['IMPRESSION_CTR'] +=$v['IMPRESSION_CTR'];
                $date_keys[$datePart]['eCPM'] +=$v['eCPM'];
                $date_keys[$datePart]['MATCHED_REQUESTS'] +=$v['MATCHED_REQUESTS'];
                $date_keys[$datePart]['MATCH_RATE'] +=$v['MATCH_RATE'];
                $date_keys[$datePart]['SHOW_RATE'] +=$v['SHOW_RATE'];
            } else {
                $date_keys[$datePart] = $v;
            }
        });

        if(!isset($date_keys[$today_date])){
            $total_earnings_today = 0;
            $total_click = 0;
            $total_pageviews =0;
        }else{
            $total_earnings_today = $date_keys[$today_date] ? number_format($date_keys[$today_date]['ESTIMATED_EARNINGS']/1000000,3): 0;
            $total_click = $date_keys[$today_date]['CLICKS'];
            $total_pageviews = $date_keys[$today_date]['AD_REQUESTS'];
        }
        if(!isset($date_keys[$yesterday_date])){
            $total_earnings_yesterday = 0;
            $total_pageviews_yesterday = 0;
            $total_click_yesterday =0;
        }else{
            $total_earnings_yesterday = number_format($date_keys[$yesterday_date]['ESTIMATED_EARNINGS']/1000000,3);
            $total_pageviews_yesterday = $date_keys[$yesterday_date]['AD_REQUESTS'];
            $total_click_yesterday = $date_keys[$yesterday_date]['CLICKS'];
        }
        $total_earnings_lifetime = number_format(AdModReport::whereIn("pub_id",$account_admod_ok)->sum('ESTIMATED_EARNINGS')/1000000,3);


        $array_data['admod_count'] = $count_admod;
        $array_data['pageview'] = $total_pageviews;

        if ($total_pageviews_yesterday > 0) {
            $array_data['pageviewpercent'] = ($total_pageviews - $total_pageviews_yesterday)
                / $total_pageviews_yesterday * 100;
        } else {
            $array_data['pageviewpercent'] = 0;
        }

        $array_data['click'] = $total_click;
        if ($total_click_yesterday > 0) {
            $array_data['clickpercent'] = ($total_click - $total_click_yesterday)
                / $total_click_yesterday * 100;
        } else {
            $array_data['clickpercent'] = 0;
        }

        $array_data['earnings'] = $total_earnings_today;
        if ($total_earnings_yesterday > 0) {
            $array_data['earningspercent'] = ($total_earnings_today - $total_earnings_yesterday) / $total_earnings_yesterday * 100;
        } else {
            $array_data['earningspercent'] = 0;
        }
        $array_data['lifetime'] = $total_earnings_lifetime ? $total_earnings_lifetime : 0;
        $array_data['thismonth']=number_format($total_month/1000000,3);
        $array_data['yesterday']=$total_earnings_yesterday;

        return view('content/dashboard/index')->with('dataHome', $array_data);
    }
    public function getReportThisMonth(){
        $users = Auth::user()->admods;
        foreach ($users as $user) {
            $user_id[] = $user->id;
        }
        if(!isset($user_id)){
            return response()->json(['error' => 'không có dữ liệu']);
        }

        $count_admods  = DB::table('admod_accounts')
            ->where("error", '=', "OK")
            ->whereIn('id', $user_id)
            ->get();
        foreach ($count_admods as $count_admod) {
            $count_admod = json_decode(json_encode($count_admod),true);
            $admod[] = $count_admod['admod_pub_id'];
        }
        $records= AdModReport::select('month','ESTIMATED_EARNINGS')
            ->where('month',date('m-Y',time()))
            ->whereIn('pub_id', $admod)
            ->get();
        if(count($records)>0){
            $data_arr = array();
            foreach ($records as $record) {
                $data_arr[] = array(
                    'month' => $record->month,
                    "ESTIMATED_EARNINGS" => $record->ESTIMATED_EARNINGS,
                );
            }
            $date_keys = [];
            array_walk($data_arr, function($v) use(&$date_keys){
                $datePart = $v['month'];
                if (isset($date_keys[$datePart])) {
                    $date_keys[$datePart]['ESTIMATED_EARNINGS'] +=$v['ESTIMATED_EARNINGS'];
                } else {
                    $date_keys[$datePart] = $v;
                }
            });
            $month = date('m-Y',time());
            return $date_keys[$month]['ESTIMATED_EARNINGS'];
        }
    }
}
