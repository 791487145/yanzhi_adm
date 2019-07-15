<?php
namespace app\admin\controller;

use app\admin\model\PayPayment;use service\UtilService as Util;
use Session;
use Db;
use Carbon\Carbon;
use app\admin\model\Channel as ChannelModel;

class Payment extends Common
{

    public function payment_list()
    {
        $data = Util::getMore([
            ['type',''],
            ['status',''],
            ['channel_statis',''],
            ['channel_id','']
        ],$this->request);

        $data = array(
            'data' => $data,
            'channels' => ChannelModel::where('status',ChannelModel::STATUS['success_check'])->field('id,name')->select(),
            'payments'  => PayPayment::getAll($data,$this->limit)
        );

        return view('payment_list',$data);
    }

}
