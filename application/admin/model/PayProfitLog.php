<?php
namespace app\admin\model;
use think\Model;
use Log;

class PayProfitLog extends Model
{


    public static function profit_log($time)
    {
        $account_payment = PayPayment::status(PayPayment::STATUS['pay_success'])->payTime($time)->sum('money');

        $account_profit = PayPayment::status(PayPayment::STATUS['pay_success'])->channelStatis(PayPayment::CHANNEL_STATUS['no'])->where('ratio','<',100)->payTime($time)->sum('money');

        $account_channel_statis = PayPayment::status(PayPayment::STATUS['pay_success'])->payTime($time)->sum('money_system');
        halt($account_channel_statis);
        return compact('account_payment','account_profit','account_channel_statis');
    }

    public static function getAll($data,$limit)
    {
        $model = empty($data['start_time']) ? new self() : self::where('create_time','>=',strtotime($data['start_time']));
        if(!empty($data['end_time'])) $model = $model->where('create_time','<=',strtotime($data['end_time']));
        return $model->order('id','desc')->paginate($limit);
    }

}

?>