<?php
namespace app\admin\controller;

use app\admin\model\ApiLog;
use app\admin\model\PayPayment;
use app\admin\model\PayProfitLog;
use service\UtilService as Util;
use Session;
use Db;
use Carbon\Carbon;
use app\admin\model\Channel as ChannelModel;

class Visit extends Common
{

    public function api_log()
    {
        $api_logs = ApiLog::with(['user' => function($query){
            $query->field('id,user_login');
        }])->order('id','desc')->paginate($this->limit);

        $data = array(
            'api_logs' => $api_logs,
        );
        return view('api_log',$data);
    }

}
