<?php
namespace app\admin\controller;

use app\admin\model\User;
use service\HttpService;
use think\cache\driver\Redis;
use think\Controller;
use service\UtilService as Util;
use think\View;
use think\Log;
class Login extends Controller
{
    public function post_login(User $userModel)
    {
        $data = Util::postMore([
            ['user_login'],
            ['user_pass']
        ],$this->request);

        $user = $userModel->login($data);

        if($user) {
           return redirect('admin/index/index');
        }else {
            $this->error('用户名或密码错误', url('admin/login/login'));
        }
    }

    public function login()
    {
        return view('user_login');
    }

    public function order()
    {
        $order = [1,2,3,4,5];
        $redis = new Redis();
        foreach ($order as $v){
            $redis->set('huashu:'.$v,$v,10);
        }
    }


}
