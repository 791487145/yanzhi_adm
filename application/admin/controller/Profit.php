<?php
namespace app\admin\controller;

use app\admin\model\PayPayment;
use app\admin\model\PayProfitLog;
use service\UtilService as Util;
use Session;
use Db;
use Carbon\Carbon;
use app\admin\model\Channel as ChannelModel;

class Profit extends Common
{

    public function profit_log_list()
    {
        $data = Util::getMore([
            ['start_time',''],
            ['end_time',''],
        ],$this->request);

        $data = array(
            'data' => $data,
            'profit_logs'  => PayProfitLog::getAll($data,$this->limit)
        );

        return view('profit_log',$data);
    }

}
