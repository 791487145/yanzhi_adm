<?php

namespace app\command;

use think\cache\driver\Redis;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;

class RedisTest extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('redis:test');
        // 设置参数
        
    }

    protected function execute(Input $input,Output $output)
    {
    	$redis = new Redis(['timeout' => 5]);
        $pattern = '__keyevent@0__:expired';

        $redis->subscribe([$pattern],function ($pattern, $chan, $msg){
            /*var_dump($pattern);
            var_dump($chan);
            var_dump($msg);*/
            trace('错误信息','error');
        });
    }




}
