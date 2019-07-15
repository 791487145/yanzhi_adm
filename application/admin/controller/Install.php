<?php
namespace app\admin\controller;
use app\admin\model\CountModel;
use app\admin\model\DownLog;
use app\admin\model\PayPayment;
use app\admin\model\PayProfit;
use app\admin\model\User;
use app\admin\model\StaticModel;
use app\cps\model\StaticModel as CpsStaticModel;
use Carbon\Carbon;
use think\View;
use Session;
class Install extends Common
{
    public function install(DownLog $downLog)
    {
        $installs = $downLog->getAll()->order('id','desc')->paginate($this->limit);
        $data['installs'] = $downLog->datas($installs);

        return view('install',$data);
    }
}
