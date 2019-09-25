<?php
namespace app\admin\controller;

use app\admin\model\ApiLog;
use app\admin\model\ApiName;
use service\UtilService as Util;
use Session;
use Db;
use think\Request;

class Visit extends Common
{

    public function api_log()
    {
        $api_logs = ApiLog::with(['user' => function($query){
            $query->field('id,user_nickname');
        }])->order('id','desc')->paginate($this->limit);

        $data = array(
            'api_logs' => $api_logs,
        );
        return view('api_log',$data);
    }

    public function api_number(Request $request)
    {
        $param = Util::getMore([
                ['start_time',''],
                ['end_time','']
            ],$this->request);
        $api_names = ApiName::getAll($param,$this->limit);

        $data = array(
            'api_names' => $api_names,
            'param' => $param
        );
        return view('api_number',$data);
    }

}
