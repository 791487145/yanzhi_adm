<?php

namespace app\command;

use app\admin\model\PayProfitLog as PayProfitLogModel;
use think\helper\Time;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\model\ChannelPayLog as ChannelPayLogModel;

class PayProfitLog extends Command
{
    protected $time;

    protected function configure()
    {
        // 指令配置
        $this->setName('pay_profit_log');
        list($start, $end) = Time::yesterday();
        $this->time = $start;
        $this->addArgument('time');
    }

    protected function execute(Input $input, Output $output)
    {
        $time = $input->getArgument('time');
        if(!empty($time)){
            $this->time = (int)$time;
        }

        $data = PayProfitLogModel::profit_log($this->time);

        $data['create_time'] = $this->time;

        PayProfitLogModel::create($data);

    }
}
