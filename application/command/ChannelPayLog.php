<?php

namespace app\command;

use app\admin\model\PayPayment;
use app\admin\model\PayProfit;
use Carbon\Carbon;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\model\ChannelPayLog as ChannelPayLogModel;
use think\Log;

class ChannelPayLog extends Command
{
    protected $start_time;
    protected $end_time;

    protected function configure()
    {
        // 指令配置
        $this->setName('channel_pay_log');
        $this->setDescription('Create channel pay log');
        $this->start_time = strtotime(Carbon::yesterday()->toDateTimeString());
        $this->end_time = strtotime(Carbon::now()->toDateTimeString());
        $this->addArgument('time');
    }

    protected function execute(Input $input, Output $output)
    {
        $time = $input->getArgument('time');
        if(!empty($time)){
            $this->start_time = (int)$time;
        }

        $data = [];
        $user_id = [];
        $resurt= PayPayment::with(['channel' => function($query){
            $query->field('id,user_store_id,ratio,ratio_vip');
        }])->status(PayPayment::STATUS['pay_success'])->channelStatis(PayPayment::CHANNEL_STATUS['yes'])->payTime($this->start_time)->select()->toArray();

        foreach ($resurt as $k=>$res){
            $coin_m = 0;
            if(!in_array($res['user_id'],$user_id)){
                $coin = PayProfit::where('user_id',$res['user_id'])->whereBetweenTime('add_time',$this->start_time)->where('type','in',array(PayProfit::TYPE['channel_stiff'],PayProfit::TYPE['channel_child_stiff']))->sum('coin');
                $user_id[] = $res['user_id'];
                if($coin == 0){
                    continue;
                }
                $coin_m = (int)$coin/10;
            }

            if(isset($data[$res['channel_id']])){
                $data[$res['channel_id']]['pay_info'] = $data[$res['channel_id']]['pay_info'].';'.$res['id'];
                $data[$res['channel_id']]['pay_num'] = $data[$res['channel_id']]['pay_num'] + $coin_m;
            }else{
                $no_pay_num = PayPayment::where('channel_id',$res['channel_id'])->status(PayPayment::STATUS['pay_success'])->channelStatis(PayPayment::CHANNEL_STATUS['no'])->payTime($this->start_time)->count();
                $pay_num = PayPayment::where('channel_id',$res['channel_id'])->status(PayPayment::STATUS['pay_success'])->channelStatis(PayPayment::CHANNEL_STATUS['yes'])->payTime($this->start_time)->count();
                $radio = number_format($no_pay_num/($pay_num + $no_pay_num),2);
                $data[$res['channel_id']]['pay_num'] = $coin_m;
                $data[$res['channel_id']]['user_store_id'] = empty($res['channel']['user_store_id']) ? 0 : $res['channel']['user_store_id'];
                $data[$res['channel_id']]['channel_id'] = $res['channel_id'];
                $data[$res['channel_id']]['channel_num'] = ($radio * 100) .'('.$no_pay_num.')'.'%';
                $data[$res['channel_id']]['pay_info'] = $res['id'];
                $data[$res['channel_id']]['created_time'] = $this->end_time;
                $data[$res['channel_id']]['remark'] = '';
                $data[$res['channel_id']]['payed_payment_num'] = 0;
                $data[$res['channel_id']]['payed_payment_num_ratio'] = 0;
                $data[$res['channel_id']]['payed_payment_num_vip'] = 0;
                $data[$res['channel_id']]['payed_payment_num_vip_ratio'] = 0;
            }

            if($res['type'] == PayPayment::TYPE['balance']){
                $data[$res['channel_id']]['payed_payment_num'] = $data[$res['channel_id']]['payed_payment_num'] + $res['money'];
                $data[$res['channel_id']]['payed_payment_num_ratio'] = $res['ratio'];
            }

            if($res['type'] == PayPayment::TYPE['vip']){
                $data[$res['channel_id']]['payed_payment_num_vip'] = $data[$res['channel_id']]['payed_payment_num_vip'] + $res['money'];
                $data[$res['channel_id']]['payed_payment_num_vip_ratio'] = $res['ratio'];
            }

        }

        if(!empty($data)){
            ChannelPayLogModel::insertAll($data);
        }

    }

}
