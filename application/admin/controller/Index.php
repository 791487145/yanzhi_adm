<?php
namespace app\admin\controller;
use app\admin\model\CountModel;
use app\admin\model\PayPayment;
use app\admin\model\PayProfit;
use app\admin\model\User;
use app\admin\model\StaticModel;
use app\cps\model\StaticModel as CpsStaticModel;
use Carbon\Carbon;
use think\View;
use Session;
class Index extends Common
{
    /**
     * 框架首页
     */
    public function home() {
        return view('home');
    }

    /**
     * 首页
     */
    public function index(User $user)
    {
        $data['cps'] = $user->channel()->field('id,name')->select()->toArray();
        $data['user_store'] = $user->userType(User::USER_TYPE['agent'])->field('id,user_login')->select()->toArray();
        $data['user'] = $this->userInfo;
        return view('index', $data);

    }

    
    /**
     * VIP报表
     */

    public function table() {
        set_time_limit(0);
        // 接受搜索条件
        $startDate = $start = isset($_GET['startDate']) ? $_GET['startDate'] : 0;
        $endDate = $end = isset($_GET['endDate']) ? $_GET['endDate'] : 0;
        $device = isset($_GET['device']) ? $_GET['device'] : 0;
        if(!empty($start)) {
            $start = strtotime($start);
        }else {
            $start = strtotime(date('Y-m-d'));
        }
        if(!empty($end)) {
            $end = strtotime($end);
        }else {
            $end = $start;
        }
        // 是否为子账户查询
        if(isset($_GET['son']) && $_GET['son'] == 1) {
            $id = isset($_GET['id']) ? $_GET['id'] : '';
            if(empty($id)) {
                exit();
            }
            $user_id = $id;
            $SHIIHDSUIBSB = true;
        }else {
            $user_id = $this->user_id;
        }
        $userinfo = db('manager')->where('id', $user_id)->find();
        $username = $userinfo['username'];

        // 判断是否有子账户
        if($userinfo['id'] == '1') {
            $user_list = db('manager')->where('status', 4)->field('id,username')->select();
        }else {
            $user_list = db('manager')->where('parent_id', $user_id)->field('id,username')->select();
        }
        if(isset($SHIIHDSUIBSB)) {
            $user_list = [['id'=>$user_id, 'username'=>$username]];
        }else {
            array_push($user_list, ['id'=>$user_id, 'username'=>$username]);
        }

        $data_list = [];
      	$config = config('kouliang2');
        foreach($user_list as $user) {
            $user_id = $user['id'];
            $username = $user['username'];
            // 查询条件内数据
            if($start > 0) {
                //$model = new StaticModel();
                //$temp = $model->getStatistics($user_id, $start, $end, $device);
              	$model = new CpsStaticModel();
                $temp = $model->getStatisticskouliang2($user_id, $start, $end, $device);
            }
            $countModel = new CountModel();
            // 是否需要查询今日数据
            // if($end < time() && $end > 0) {
            //     $data = ['manager_name'=>$username, 'click_num'=>0, 'install_num'=>0, 'register_num'=>0, 'recharge_num'=>0, 'recharge_member_num'=>0];
            // }else {
            //     $data = $countModel->getToday($user_id, $device);
            // }
            if($end > 0 && $end >= strtotime(date('Y-m-d'))) {
              	$session = session('admin_user');
                $session = unserialize($session);
              	if($session['username']=='huhuwangluokeji'){
                  	$data = $countModel->getToday2($user_id, $device);
                }else{
                	$data = $countModel->getToday($user_id, $device);
                }
              	if($data==false) continue;
            }else {
                $data = ['manager_name'=>$username, 'click_num'=>0, 'install_num'=>0, 'register_num'=>0, 'recharge_num'=>0, 'recharge_member_num'=>0];
            }
            if(isset($temp)) {
                $data['click_num'] += $temp['click_num'];
                $data['install_num'] =$data['install_num'] + $temp['install_num'] - intval(($data['install_num']+$temp['install_num'])*$config['install']);
                $data['register_num'] = $data['register_num'] +$temp['register_num'] - intval(($data['register_num']+$temp['register_num'])*$config['register']);
                $data['recharge_num'] += $temp['recharge_num'];
                $data['recharge_member_num'] += $temp['recharge_member_num'];
            }
            $data['id'] = $user_id;
            $data_list[] = $data;
        }
        $data = ['list'=> $data_list, 'detail'=> ''];
        // 详情页参数
        $startDate = $startDate == 0 ? date('Y-m-d') : $startDate;
        $endDate = $endDate == 0 ? date('Y-m-d') : $endDate;
        $data['detail'] = ['startDate'=>$startDate, 'endDate'=>$endDate, 'device'=>$device];
        $data['zong'] = ['install_num'=>0, 'register_num'=>0, 'recharge_num'=>0, 'recharge_member_num'=>0];
        foreach($data['list'] as $v) {
             $data['zong']['install_num'] += $v['install_num'];
             $data['zong']['register_num'] += $v['register_num'];
             $data['zong']['recharge_num'] += $v['recharge_num'];
             $data['zong']['recharge_member_num'] += $v['recharge_member_num'];
        }
        
    	return view('table', $data);
    }
    public function table2() {
        set_time_limit(0);
        // 接受搜索条件
        $startDate = $start = isset($_GET['startDate']) ? $_GET['startDate'] : 0;
        $endDate = $end = isset($_GET['endDate']) ? $_GET['endDate'] : 0;
        $device = isset($_GET['device']) ? $_GET['device'] : 0;
        if(!empty($start)) {
            $start = strtotime($start);
        }else {
            $start = strtotime(date('Y-m-d'));
        }
        if(!empty($end)) {
            $end = strtotime($end);
        }else {
            $end = $start;
        }
        // 是否为子账户查询
        if(isset($_GET['son']) && $_GET['son'] == 1) {
            $id = isset($_GET['id']) ? $_GET['id'] : '';
            if(empty($id)) {
                exit();
            }
            $user_id = $id;
            $SHIIHDSUIBSB = true;
        }else {
            $user_id = $this->user_id;
        }
        $userinfo = db('manager')->where('id', $user_id)->find();
        $username = $userinfo['username'];

        // 判断是否有子账户
        if($userinfo['id'] == '1') {
            $user_list = db('manager')->where('status', 4)->field('id,username')->select();
        }else {
            $user_list = db('manager')->where('parent_id', $user_id)->field('id,username')->select();
        }
        if(isset($SHIIHDSUIBSB)) {
            $user_list = [['id'=>$user_id, 'username'=>$username]];
        }else {
            array_push($user_list, ['id'=>$user_id, 'username'=>$username]);
        }

        $data_list = [];
      	$config = config('kouliang2');
        foreach($user_list as $user) {
            $user_id = $user['id'];
            $username = $user['username'];
            // 查询条件内数据
            if($start > 0) {
                //$model = new StaticModel();
                //$temp = $model->getStatistics($user_id, $start, $end, $device);
              	$model = new CpsStaticModel();
                $temp = $model->getStatisticskouliang2($user_id, $start, $end, $device);
            }
            $countModel = new CountModel();
            // 是否需要查询今日数据
            // if($end < time() && $end > 0) {
            //     $data = ['manager_name'=>$username, 'click_num'=>0, 'install_num'=>0, 'register_num'=>0, 'recharge_num'=>0, 'recharge_member_num'=>0];
            // }else {
            //     $data = $countModel->getToday($user_id, $device);
            // }
            if($end > 0 && $end >= strtotime(date('Y-m-d'))) {
                $data = $countModel->getToday($user_id, $device);
            }else {
                $data = ['manager_name'=>$username, 'click_num'=>0, 'install_num'=>0, 'register_num'=>0, 'recharge_num'=>0, 'recharge_member_num'=>0];
            }
            if(isset($temp)) {
                $data['click_num'] += $temp['click_num'];
                $data['install_num'] =$data['install_num'] + $temp['install_num'] - intval(($data['install_num']+$temp['install_num'])*$config['install']);
                $data['register_num'] = $data['register_num'] +$temp['register_num'] - intval(($data['register_num']+$temp['register_num'])*$config['register']);
                $data['recharge_num'] += $temp['recharge_num'];
                $data['recharge_member_num'] += $temp['recharge_member_num'];
            }
            $data['id'] = $user_id;
            $data_list[] = $data;
        }
        $data = ['list'=> $data_list, 'detail'=> ''];
        // 详情页参数
        $startDate = $startDate == 0 ? date('Y-m-d') : $startDate;
        $endDate = $endDate == 0 ? date('Y-m-d') : $endDate;
        $data['detail'] = ['startDate'=>$startDate, 'endDate'=>$endDate, 'device'=>$device];
        $data['zong'] = ['install_num'=>0, 'register_num'=>0, 'recharge_num'=>0, 'recharge_member_num'=>0];
        foreach($data['list'] as $v) {
             $data['zong']['install_num'] += $v['install_num'];
             $data['zong']['register_num'] += $v['register_num'];
             $data['zong']['recharge_num'] += $v['recharge_num'];
             $data['zong']['recharge_member_num'] += $v['recharge_member_num'];
        }
        
    	return view('table', $data);
    }
    
    /**
     * 详情
     */
    public function detail() {
        // 接受搜索条件
        $start = isset($_GET['startDate']) ? $_GET['startDate'] : 0;
        $end = isset($_GET['endDate']) ? $_GET['endDate'] : 0;
        $device = isset($_GET['device']) ? $_GET['device'] : 0;
        $debug = isset($_GET['debug']) ? $_GET['debug'] : 0;

        if(!empty($start)) {
            $start = strtotime($start);
        }else {
            $start = strtotime(date('Y-m-d'));
        }
        if(!empty($end)) {
            $end = strtotime($end);
        }else {
            $end = $start;
        }
        // 是否为子账户查询
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $this->user_id;
        // 查询条件内数据
        // 查询条件内数据
        if($start > 0) {
            if($end == 0) $end = time();
            if(strtotime(date('Y-m-d')) == $end) $end += 86400;
            $where = "manager_id = $user_id and count_time >= $start and count_time <= $end";
            $model = new StaticModel();
            $iosField = 'count_time, ios_click_num as click_num, ios_install_num as install_num, ios_register_num as register_num, ios_recharge_num as recharge_num, ios_recharge_member_num as recharge_member_num, kouliang';
            $androidField = 'count_time, android_click_num as click_num, android_install_num as install_num, android_register_num as register_num, android_recharge_num as recharge_num, android_recharge_member_num as recharge_member_num, kouliang';
            $allField = 'count_time, (ios_click_num + android_click_num) as click_num, (ios_install_num + android_install_num) as install_num, (ios_register_num + android_register_num) as register_num, (ios_recharge_num + android_recharge_num) as recharge_num, (ios_recharge_member_num + android_recharge_member_num) as recharge_member_num, kouliang';
            if($device == 1) {
                $field = $iosField;
            }elseif($device == 2) {
                $field = $androidField;
            }else {
                $field = $allField;
            }
            $temp = (array)$model->where($where)->field($field)->order('id DESC')->select();
            if ($debug == 1){
                var_dump($where);
                var_dump("***********");
                var_dump($field);
                var_dump("***********");
                var_dump($temp);
            }
        }
        $countModel = new CountModel();
        // 是否需要查询今日数据
        if($end >= strtotime(date('Y-m-d'))) {
            $data = $countModel->getToday($user_id, $device);
            $data['count_time'] = strtotime(date('Y-m-d'));
        }

        $config = config('kouliang2');

        $username = db('manager')->where('id', $user_id)->cache(true)->value('username');
        if(isset($temp) and !empty($temp)) {
            foreach ($temp as $key => $value) {
              	if($value['kouliang']>0){
                  	$tt = intval(($value['recharge_num']*$value['kouliang']/100)/50);
                    $temp[$key]['click_num'] -= intval($value['click_num']*$value['kouliang']/100);
                    $temp[$key]['install_num'] -= intval($value['install_num']*($value['kouliang']/100+$config['install']));
                    $temp[$key]['register_num'] -= intval($value['register_num']*($value['kouliang']/100+$config['register']));
                    $temp[$key]['recharge_num'] -= 50*$tt;
                    $temp[$key]['recharge_member_num'] -= $tt;
                }else{
                    $temp[$key]['install_num'] -= intval($value['install_num']*$config['install']);
                    $temp[$key]['register_num'] -= intval($value['register_num']*$config['register']);
                }
            }
        }else{
            $temp = [];
        }
      	
        if(isset($data)) {
            $data['install_num'] = $data['install_num'] - intval($data['install_num']*$config['install']);
            $data['register_num'] = $data['register_num'] - intval($data['register_num']*$config['register']);
            // array_push($temp, $data);
            array_unshift($temp, $data);
        }
        $url = $_SERVER['HTTP_REFERER'];
        return view('detail', ['list'=>$temp, 'manager_name'=>$username, 'url'=>$url]);
    }
}
