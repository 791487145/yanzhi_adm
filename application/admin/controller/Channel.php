<?php
namespace app\admin\controller;

use app\admin\model\Channel as ChannelModel;
use app\admin\model\ChannelPayLog;
use app\admin\model\DownLog;
use app\admin\model\PayPayment;
use app\admin\model\User;
use service\UtilService as Util;
use Session;
use think\Request;
use think\Db;
use Carbon\Carbon;
use think\response\Json;

class Channel extends Common
{
    /**
     * 渠道列表
     * @return \think\response\View
     */
    public function channel()
    {
        $data = Util::getMore([
            ['user_store_id',''],
            ['not_effective',0],
            ['domain','']
        ],$this->request);

        if($this->userInfo['user_type'] != 1){
            $data['user_store_id'] = $this->user_id;
        }

        $channels = ChannelModel::getAll($data);
        $data['channels'] = Util::sortListTier($channels,0,'parent_id');
        $data['user_store'] = User::userType(User::USER_TYPE['agent'])->field('id,user_login')->select()->toArray();
        return view('channel_list',$data);
    }

    /**
     * 添加渠道
     * @return \think\response\View|void
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if($this->request->isPost()) {
            $user_exist = User::where('user_login', $this->request->post('user_login'))->count();
            if($user_exist > 0) {
                $this->error('用户名已存在', url('admin/channel/add'));
                exit();
            }
            $data = $this->request->only(['user_login','user_pass','confirm_password']);
            if($data['user_pass'] != $data['confirm_password']) return $this->error('密码不正确', url('admin/channel/add'));

            Db::startTrans();
            unset($data['confirm_password']);
            $user = User::create_one($data);

            $payment['pay_name'] = $_POST['pay_name'];
            $payment['pay_type'] = $_POST['pay_type'];
            $payment['pay_account'] = $_POST['pay_account'];
            $payment['ratio'] = $_POST['ratio'];
            $payment['ratio_vip'] = $_POST['ratio_vip'];
            $payment['name'] = $user->user_login;
            $payment['cdr'] = $_POST['cdr'];
            $payment['mobile'] = $_POST['mobile'];
            $payment['user_id'] = $user->id;
            $payment['user_store_id'] = $this->user_id;
            $payment['domain'] = $_POST['domain'];
            $res = ChannelModel::addChannel($payment);

            $result = User::where('id',$user->id)->update(['channel_id' => $res->id]);

            if($result) {
                Db::commit();
                $this->success('添加成功');
            }else {
                Db::rollback();
                $this->error('添加失败', url('admin/channel/add'));
            }
        }

        return view('channel_add');
    }

    /**
     * 添加子渠道
     * @return \think\response\View|void
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function child_add()
    {
        if($this->request->isPost()) {
            $user_exist = User::where('user_login', $this->request->post('user_login'))->count();
            if($user_exist > 0) {
                $this->error('用户名已存在', url('admin/channel/add'));
                exit();
            }
            $channel_parent = ChannelModel::where('id',$this->request->post('parent_id'))->find();
            $data = $this->request->only(['user_login','user_pass','confirm_password']);
            if($data['user_pass'] != $data['confirm_password']) return $this->error('密码不正确', url('admin/channel/child_add'));
            $channel = ChannelModel::where('id',$this->request->post('parent_id'))->find();
            Db::startTrans();
            unset($data['confirm_password']);
            $user = User::create_one($data);
            $payment['pay_name'] = $_POST['pay_name'];
            $payment['parent_id'] = $this->request->post('parent_id');
            $payment['pay_type'] = $_POST['pay_type'];
            $payment['pay_account'] = $_POST['pay_account'];
            $payment['ratio'] = $_POST['ratio'];
            $payment['ratio_vip'] = $_POST['ratio_vip'];
            $payment['ratio_parent'] = $channel_parent['ratio'];
            $payment['ratio_vip_parent'] = $channel_parent['ratio_vip'];
            $payment['name'] = $user->user_login;
            $payment['cdr'] = $_POST['cdr'];
            $payment['mobile'] = $_POST['mobile'];
            $payment['user_id'] = $user->id;
            $payment['user_store_id'] = $channel['user_store_id'];
            $payment['domain'] = $_POST['domain'];
            $res = ChannelModel::addChannel($payment);

            $result = User::where('id',$user->id)->update(['channel_id' => $res->id]);

            if($result) {
                Db::commit();
                $this->success('添加成功');
            }else {
                Db::rollback();
                $this->error('添加失败', url('admin/channel/add'));
            }
        }

        $channels = ChannelModel::select()->toArray();
        if($this->userInfo['user_type'] != 1){
            $channels = ChannelModel::where('user_store_id',$this->user_id)->select()->toArray();
        }

        $data['channels'] = Util::sortListTier($channels,0,'parent_id');

        return view('channel_child_add',$data);
    }

    /**
     * 编辑
     * @param $id
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        $data['channel'] = ChannelModel::id($id)->with(['user' => function($query){
            $query->field('id,user_login');
        }])->find()->toArray();

        if($this->request->isPost()){
            $data = Util::postMore([
                ['pwd',''],
                ['confirm_pwd','']
            ],$this->request);

            if(!empty($pwd)) {
                if($data['pwd'] !== $data['confirm_pwd']) {
                    die($this->error('两次密码输入不一致'));
                }
                if(strlen($data['pwd']) < 6) {
                    die($this->error('密码最小长度为6位数'));
                }
                $data['password'] = cmf_password($data['pwd']);
                Db::startTrans();
                User::where('id',$data['channel']['user']['id'])->update(['user_pass' => $data['password']]);
            }

            $param = Util::postMore([
                ['pay_name',''],
                ['pay_type',''],
                ['pay_account',''],
                ['ratio',''],
                ['ratio_vip',''],
                ['effective',0],
                ['domain','']
            ],$this->request);
            $param['effective'] = 100 - $param['effective'];
            ChannelModel::id($id)->update($param);
            Db::commit();
            $this->success('修改成功', url('admin/channel/channel'));
        }

        return view('channel_edit',$data);
    }

    /**
     * 渠道打款列表
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function payed()
    {
        $data = array(
            'param' => $param = Util::getMore([
                ['status',0],
                ['start_time',''],
                ['end_time','']
            ],$this->request),

            'channel_logs' =>  ChannelPayLog::getAll($param,$this->limit),
        );
        return  view('channel_payed',$data);
    }

    /**
     *打款
     */
    public function topayed($oid)
    {
        ChannelPayLog::where('id',$oid)->update(['status' => ChannelPayLog::STATUS['pay_success'],'end_time' => time()]);
        $this->success('打款成功',url('admin/channel/payed'));
    }

    /**
     * 导出
     * @throws \think\exception\DbException
     */
    public function getPayed()
    {
        $data['start_time'] = date("Y-m-d",strtotime("-1 day"));
        $data['end_time '] = date("Y-m-d");
        $data['export'] = 1;

        ChannelPayLog::getAll($data);
    }

    /**
     * @param int $user_id（角色）
     * @param string $action（商务）
     * @param ChannelModel $channel
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function table($user_id = 0,$action = '',ChannelModel $channel)
    {
        $channels = $channel->field('id,name')->select()->toArray();

        if($this->userInfo['user_type'] != 1){
            $channels = $channel->where('user_store_id',$this->user_id)->field('id,name')->select()->toArray();
        }

        if(!empty($user_id)){
            $channels = $channel->where('user_store_id',$user_id)->column('id');
        }

        if(!empty($action) && $this->userInfo['user_type'] == 1){
            $channels = $channel->column('id');
        }

        if(!empty($action) && $this->userInfo['user_type'] != 1){
            $channels = $channel->where('user_store_id',$this->user_id)->column('id');
        }

        if(empty($channels)){
            $channels = array(100000000);
        }

        $data = array(
            'channel' => $channels,
            'param'  => $param = Util::getMore([
                ['device',''],
                ['start_time',date("Y-m-d")],
                ['end_time',date("Y-m-d")],
                ['channel_id',(empty($action) && !empty($channels)) ? array($channels[0]['id']) : '']
            ],$this->request),
            'channel_tabel' => $channel->tables(empty($user_id) && empty($action) ? $param : array_merge($param,array('channel_ids' => $channels)))
        );

        if(!empty($user_id)){
            return view('store_table',$data);
        }

        if(!empty($action)){
            return view('zong_table',$data);
        }

        return view('table',$data);
    }


    /**
     *
     */
    public function search($channel_name = '')
    {
        if(!empty($channels)){
            $channels = ChannelModel::where('name','like', $channels . '%')->field('id,name')->select()->toArray();
        }else{
            $channels = ChannelModel::field('id,name')->select()->toArray();
        }

        $data = array(
            'message' => '成功',
            'value'  => $channels,
            'code' => 200,
            'redirect' => ''
        );

        return json($data);
    }
}
