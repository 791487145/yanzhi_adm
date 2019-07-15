<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class StaticModel extends Model
{
    protected $table = 'app_statistics';
    protected $model = null;
  	public function getOrdersExcle($start=0) {
      	$end = $start+86400;
        $list = $this->alias('o')
          	->join('app_manager u','o.manager_id = u.id','left')
          	->where("o.count_time >= $start and o.count_time <= $end")
          	->where('o.all_recharge_num > 0')
          	->field('o.*,u.username, u.parent_id,u.pay_name, u.pay_type, u.pay_account, u.ratio, u.superior, u.remarks, u.is_agent, u.id as mid')
          	->order('o.id DESC')
          	->select();
		if($list){
        	$list = collection($list)->toArray();
        }else{
        	return false;
        }
      	foreach($list as $k=>$v) {
            if($v['superior'] > 0 && $v['superior'] != '1') {
                $list[$k]['p_ratio'] = db('manager')->where('id = '.$v['superior'])->value('ratio');
            }else {
                $list[$k]['p_ratio'] = 0;
            }
          	$list[$k]['shangwu'] = db('manager')->where('id = '.$v['parent_id'])->cache(true)->value('username');
          	$list[$k]['kouqian'] = 50*intval(($v['all_recharge_num']*$v['kouliang']/100)/50);
        }
        return $list;
	}

	public function getOrders($start=0, $end=0, $status=null, $is_agent=null, $gt0=0) {
        $model = $this->alias('o')->join('app_manager u','o.manager_id = u.id','left');
        if($start > 0 && $end > 0) {
            $end += 86400;
            $model->where("o.add_time >= $start and o.add_time <= $end");
        }
        if($gt0 > 0) {
            $model->where('o.all_recharge_num > 0');
        }
        if($is_agent !== null) {
            $model->where('u.is_agent', $is_agent);
        }
        if($status !== null) {
            $model->where('o.status', $status);
        }
        $list = $model->field('o.*,u.username, u.parent_id,u.pay_name, u.pay_type, u.pay_account, u.ratio, u.superior, u.remarks, u.is_agent, u.id as mid')->order('o.id DESC')->paginate(10);
        
      	foreach($list as $k=>$v) {
            if($v['superior'] > 0 && $v['superior'] != '1') {
                $list[$k]['p_ratio'] = db('manager')->where('id = '.$v['superior'])->value('ratio');
            }else {
                $list[$k]['p_ratio'] = 0;
            }
          	$list[$k]['shangwu'] = db('manager')->where('id = '.$v['parent_id'])->cache(true)->value('username');
          	$list[$k]['kouqian'] = 50*intval(($v['all_recharge_num']*$v['kouliang']/100)/50);
        }
        $page = $list->render();
        return ['list'=>$list, 'page'=>$page];
	}

	public function getStatistics($user_id, $start, $end, $device) {
		if($end == 0) $end = time();
        if(strtotime(date('Y-m-d')) == $end) $end += 86400;
        $where = "manager_id = $user_id and count_time >= $start and count_time <= $end";
        $iosField = 'sum(ios_click_num) as click_num, sum(ios_install_num) as install_num, sum(ios_register_num) as register_num, sum(ios_recharge_num) as recharge_num, sum(ios_recharge_member_num) as recharge_member_num';
        $androidField = 'sum(android_click_num) as click_num, sum(android_install_num) as install_num, sum(android_register_num) as register_num, sum(android_recharge_num) as recharge_num, sum(android_recharge_member_num) as recharge_member_num';
        $allField = 'sum(ios_click_num + android_click_num) as click_num, sum(ios_install_num + android_install_num) as install_num, sum(ios_register_num + android_register_num) as register_num, sum(ios_recharge_num + android_recharge_num) as recharge_num, sum(ios_recharge_member_num + android_recharge_member_num) as recharge_member_num';
        if($device == 1) {
            $field = $iosField;
           
        }else if($device == 2) {
            $field = $androidField;
           
        }else {
            $field = $allField;
            
        }
        $temp = $this->where($where)->field($field)->order('count_time', 'DESC')->find();
        
        return $temp;
	}
	public function setYesterdaytest() {
        //$userModel = new UserModel();
        //$countModel = new CountModel;
        $managers = Db::table('app_manager')->where(['status'=>4])->field('id,username,kouliang')->cache(3600)->select();
        $count_time = strtotime(date('Y-m-d')) - 86400;
        $count_date = date('Y-m-d', $count_time);
        $add_list = [];
      	$k=0;
        foreach($managers as $manager) {
            $manager_id = $manager['id'];
            $manager_name = $manager['username'];
            $where['manager_id'] = $manager_id;
            $add_data['manager_id'] = $manager_id;
            $add_data['manager_name'] = $manager_name;
            $add_data['ios_click_num'] = 0;
            $add_data['android_click_num'] = 0;
            $add_data['ios_install_num'] = 0;
            $add_data['android_install_num'] = 0;
            $add_data['ios_register_num'] = 0;
            $add_data['android_register_num'] = 0;
            $add_data['ios_recharge_num'] = 0;
            $add_data['android_recharge_num'] = 0;
            $add_data['ios_recharge_member_num'] = 0;
            $add_data['android_recharge_member_num'] = 0;
            $add_data['all_recharge_num'] = 0;
            $add_data['ios_kouliang'] = 0;
            $add_data['android_kouliang'] = 0;
            $add_data['all_kouliang'] = 0;
            $add_data['status'] = 0;
            $add_data['count_time'] = $count_time;
            $add_data['count_date'] = $count_date;
            $add_data['add_time'] = time();
          	$add_data['kouliang'] = $manager['kouliang'];
          	//先检查是否重复
          	$bid = db('statistics')->where('manager_id',$manager_id)->where('count_date',$count_date)->value('id');
            if($bid>0){
                continue;
            }
          	$temp = $this->getRechargeNum($manager_id, $count_time);
            foreach($temp as $key=>$value) {
                $add_data[$key] = $value;
            }
          	//if($add_data['all_recharge_num']==0){
            	//continue;
            //}
            $list = db('count')->where('add_time', '>=', $count_time)->where('add_time', '<', $count_time + 86400)->where('is_install = 1')->field('device,is_install,is_register')->where($where)->select();
            
            foreach($list as $v) {
                if($v['device'] == 1) { // IOS
                    $add_data['ios_click_num'] += 1;
                    $add_data['ios_install_num'] += $v['is_install'] == 1 ? 1 : 0;
                    $add_data['ios_register_num'] += $v['is_register'] == 1 ? 1 : 0;
                    
                }else { // 安卓
                    $add_data['android_click_num'] += 1;
                    $add_data['android_install_num'] += $v['is_install'] == 1 ? 1 : 0;
                    $add_data['android_register_num'] += $v['is_register'] == 1 ? 1 : 0;
                }
            }
            $start = $count_time;
            $end = $count_time + 86400;
            
            $add_data['ios_register_num'] = db('member')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end and device = 1")->count();
            $add_data['ios_install_num'] += (int)db('count2')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end")->value('ios_install_num');
            $add_data['android_register_num'] = db('member')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end and device = 2")->count();
            $add_data['android_install_num']+=(int)db('count2')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end")->value('android_install_num');
			//$add_list[] = $add_data;
           	db('statistics')->insert($add_data);
            $k++;
          	
        }
      	//$this->saveAll($add_list);
        return $k;
    }
    public function setYesterday() {
        //$userModel = new UserModel();
        $countModel = new CountModel;
        //$managers = $userModel->getAllCps2();
      	$managers = Db::table('app_manager')->where(['status'=>4])->field('id,username,kouliang')->cache(3600)->select();
        $count_time = strtotime(date('Y-m-d')) - 86400;
        $count_date = date('Y-m-d', $count_time);
        $add_list = [];
      	
        foreach($managers as $manager) {
            $manager_id = $manager['id'];
            $manager_name = $manager['username'];
            $where['manager_id'] = $manager_id;
            $add_data['manager_id'] = $manager_id;
            $add_data['manager_name'] = $manager_name;
            $add_data['ios_click_num'] = 0;
            $add_data['android_click_num'] = 0;
            $add_data['ios_install_num'] = 0;
            $add_data['android_install_num'] = 0;
            $add_data['ios_register_num'] = 0;
            $add_data['android_register_num'] = 0;
            $add_data['ios_recharge_num'] = 0;
            $add_data['android_recharge_num'] = 0;
            $add_data['ios_recharge_member_num'] = 0;
            $add_data['android_recharge_member_num'] = 0;
            $add_data['all_recharge_num'] = 0;
            $add_data['ios_kouliang'] = 0;
            $add_data['android_kouliang'] = 0;
            $add_data['all_kouliang'] = 0;
            $add_data['status'] = 0;
            $add_data['count_time'] = $count_time;
            $add_data['count_date'] = $count_date;
            $add_data['add_time'] = time();
          	$add_data['kouliang'] = $manager['kouliang'];
          
          	$temp = $this->getRechargeNum($manager_id, $count_time);
            foreach($temp as $key=>$value) {
                $add_data[$key] = $value;
            }
          	if($add_data['all_recharge_num']==0){
            	continue;
            }
          
            //$list = $countModel->where('add_time', '>=', $count_time)->where('add_time', '<', $count_time + 86400)->where('is_install = 1')->field('device,is_install,is_register')->where($where)->select();
          	$list = db('count')->where('add_time', '>=', $count_time)->where('add_time', '<', $count_time + 86400)->where('is_install = 1')->field('device,is_install,is_register')->where($where)->select();
            
            foreach($list as $v) {
                if($v['device'] == 1) { // IOS
                    $add_data['ios_click_num'] += 1;
                    $add_data['ios_install_num'] += $v['is_install'] == 1 ? 1 : 0;
                    $add_data['ios_register_num'] += $v['is_register'] == 1 ? 1 : 0;
                    
                }else { // 安卓
                    $add_data['android_click_num'] += 1;
                    $add_data['android_install_num'] += $v['is_install'] == 1 ? 1 : 0;
                    $add_data['android_register_num'] += $v['is_register'] == 1 ? 1 : 0;
                }
            }
            $start = $count_time;
            $end = $count_time + 86400;
            
            $add_data['ios_register_num'] = db('member')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end and device = 1")->count();
            $add_data['ios_install_num'] += (int)db('count2')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end")->value('ios_install_num');
            $add_data['android_register_num'] = db('member')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end and device = 2")->count();
            $add_data['android_install_num']+=(int)db('count2')->where("manager_id = $manager_id and create_time >= $start and create_time <= $end")->value('android_install_num');

            $add_list[] = $add_data;
        }
      	//dump($add_list);
        $this->saveAll($add_list);
        
    }
    public function setYesterday2($ddtt) {
        $ddtt = $ddtt * 86400;
        $userModel = new User();
        $countModel = new CountModel;
        $managers = $userModel->getAllCps();
        $count_time = strtotime(date('Y-m-d'));
        $count_date = date('Y-m-d', $count_time);
        $add_list = [];
        var_dump($count_time,$count_date);
        die;
        foreach($managers as $manager) {
            $manager_id = $manager['id'];
            $manager_name = $manager['username'];
            $where['manager_id'] = $manager_id;
            $add_data['manager_id'] = $manager_id;
            $add_data['manager_name'] = $manager_name;
            $add_data['ios_click_num'] = 0;
            $add_data['android_click_num'] = 0;
            $add_data['ios_install_num'] = 0;
            $add_data['android_install_num'] = 0;
            $add_data['ios_register_num'] = 0;
            $add_data['android_register_num'] = 0;
            $add_data['ios_recharge_num'] = 0;
            $add_data['android_recharge_num'] = 0;
            $add_data['ios_recharge_member_num'] = 0;
            $add_data['android_recharge_member_num'] = 0;
            $add_data['all_recharge_num'] = 0;
            $add_data['ios_kouliang'] = 0;
            $add_data['android_kouliang'] = 0;
            $add_data['all_kouliang'] = 0;
            $add_data['status'] = 0;
            $add_data['count_time'] = $count_time;
            $add_data['count_date'] = $count_date;
            $add_data['add_time'] = time();
            $list = $countModel->where('add_time', '>=', $count_time)->where('add_time', '<', $count_time + 86400)->where($where)->select();
            
            foreach($list as $v) {
                if($v['device'] == 1) { // IOS
                    $add_data['ios_click_num'] += 1;
                    $add_data['ios_install_num'] += $v['is_install'] == 1 ? 1 : 0;
                    $add_data['ios_register_num'] += $v['is_register'] == 1 ? 1 : 0;
                    
                }else { // 安卓
                    $add_data['android_click_num'] += 1;
                    $add_data['android_install_num'] += $v['is_install'] == 1 ? 1 : 0;
                    $add_data['android_register_num'] += $v['is_register'] == 1 ? 1 : 0;
                }
            }
            $temp = $this->getRechargeNum($manager_id, $count_time);
            foreach($temp as $key=>$value) {
                $add_data[$key] = $value;
            }
            $add_list[] = $add_data;
        }
        $this->saveAll($add_list);
        
    }
    public function getRechargeNum($manager_id, $starttime) {
        if(!is_object($this->model)) {
            $this->model = new RechargeModel;
        }
        $model = $this->model;
        $list = $model->getDayOrder($starttime, $starttime+86400, $manager_id);

        $data = ['ios_recharge_num'=>0, 'android_recharge_num'=>0, 'ios_recharge_member_num'=>0, 'android_recharge_member_num'=>0, 'all_recharge_num'=>0, 'ios_kouliang'=>0, 'android_kouliang'=>0, 'all_kouliang'=>0];
        $ios_users = [];
        $android_users = [];
        foreach($list as $v) {
            if($v['kouliang'] == 0) {
                if($v['device'] == 1) {
                    if(!in_array($v['user_id'], $ios_users)) {
                        $ios_users[$v['user_id']] = '';
                    }
                    $data['ios_recharge_num'] += $v['money'];
                }else { // if($v['device'] == 2) 
                    if(!in_array($v['user_id'], $android_users)) {
                        $android_users[$v['user_id']] = '';
                    }
                    $data['android_recharge_num'] += $v['money'];
                }
                $data['all_recharge_num'] += $v['money'];
            }elseif($v['kouliang'] == 1) {
                if($v['device'] == 1) {
                    $data['ios_kouliang'] += $v['money'];
                }else {
                    $data['android_kouliang'] += $v['money'];
                }
                $data['all_kouliang'] += $v['money'];
            }
        }
        $data['ios_recharge_member_num'] = count($ios_users);
        $data['android_recharge_member_num'] = count($android_users);
        return $data;
    }
}
?>