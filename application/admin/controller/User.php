<?php
namespace app\admin\controller;

use app\admin\model\User as UserModel;
use think\View;
use think\facade\Session;
class User extends Common
{
    /**
     * 退出
     */
    public function logout() {
        Session::delete('user');
        header('Location: '.url('admin/login/login'));
    }

    /**
     * 添加商务人员
     * @return \think\response\View
     */
    public function add_agent()
    {
        if($this->request->isPost()) {
            $user_exist = UserModel::where('user_login', $_POST['user_login'])->count();
            if($user_exist > 0) {
                $this->error('用户名已存在', url('admin/user/add_agent'));
                exit();
            }

            $data = $this->request->only(['user_pass','user_login']);

            $res = UserModel::create_one($data,UserModel::USER_TYPE['agent']);
            if($res) {
                $this->success('添加成功');
            }else {
                $this->error('添加失败', url('admin/user/add_agent'));
            }
        }

        return view('user_agent');
    }

    public function reset_password() {
        if($this->request->isPost()) {
            $password = $_POST['password'];
            $new_pwd = $_POST['user_pass'];
            $new_pwd2 = $_POST['user_pass_config'];
            if($new_pwd!==$new_pwd2 || strlen($new_pwd) < 6) {
                exit($this->error('两次密码输入不一致,密码长度最小为6'));
            }
            $pwd = $this->userInfo['user_pass'];
            if(cmf_password($password) !== $pwd) {
                exit($this->error('当前密码错误'));
            }
            // 修改密码
            $res = UserModel::where('id',$this->user_id)->update(['user_pass' => cmf_password($new_pwd)]);
            if($res) {
                Session::delete('user');
                $this->success('修改密码成功, 请重新登陆', url('admin/login/login'));
            }else {
                $this->error('修改密码失败');
            }
        }else {
            return view();
        }
    }

    public function app_total()
    {
        $data = UserModel::getAll($this->limit);

        return view('app_total',$data);
    }

}
