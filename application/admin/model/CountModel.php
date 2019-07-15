<?php 
namespace app\admin\model;
use think\Model;
class CountModel extends Model 
{
	protected $table = 'app_count';
	public function addOne($manager_id, $ip, $device) {
		$this->manager_id = $manager_id;
		$model = new User;
		$manager_name = $model->where('id', $manager_id)->value('username');
		$this->manager_name = $manager_name ? $manager_name : '';
		$this->log_ip = $ip;
		$this->device = $device;
		$this->is_install = 0;
		$this->is_register = 0;
		$this->add_time = time();
		$this->save();
		return $this->id > 0 ? true : false;
	}
	public function getToday2($manager_id, $device) {
		$time = strtotime(date('Y-m-d'));
		$where['manager_id'] = $manager_id;
		if($device>0) {
			$where['device'] = $device;
		}
		$list = db('recharge')->where(['manager_id'=>$manager_id, 'status'=>1, 'kouliang'=>0])->where('add_time', '>', $time)->field('user_id,money')->select();
		if(empty($list)){
			return false;
		}
		$list1 = $this->where($where)->where("add_time > $time and is_install = 1")->select();
		$data['manager_id'] = $manager_id;
		$model = new User;
      	$dmans = $model->where('id', $manager_id)->field('username,kouliang')->find();
		$data['manager_name'] = $dmans['username'];
		$data['click_num'] = 0;
		$data['install_num'] = 0;
		$data['register_num'] = 0;
		foreach($list1 as $v) {
			if($v['is_install'] == 1) {
				$data['install_num'] += 1;
			}
			if($v['is_register'] == 1) {
				$data['register_num'] += 1;
			}
		}
		if($device == 1) {
            $_wh = "manager_id = $manager_id and create_time >= $time and device = 1";
            $_fie2 = 'ios_install_num';
        }else if($device == 2) {
            $_wh = "manager_id = $manager_id and create_time >= $time and device = 2";
            $_fie2 = 'android_install_num';
        }else {
            $_wh = "manager_id = $manager_id and create_time >= $time";
            $_fie2 = 'sum(ios_install_num + android_install_num)';
        }
		$data['register_num'] = db('member')->where($_wh)->count();
        $data['install_num'] += db('count2')->where("manager_id = $manager_id and create_time >= $time")->value($_fie2);
		//$list = db('recharge')->where(['manager_id'=>$manager_id, 'status'=>1, 'kouliang'=>0])->where('add_time', '>', $time)->field('user_id,money')->select();
		$data['recharge_num'] = 0;
		$data['recharge_member_num'] = 0;
		$member = [];
		foreach($list as $v) {
			$data['recharge_num'] += $v['money'];
			if(!in_array($v['user_id'], $member)) {
				$member[] = $v['user_id'];
				$data['recharge_member_num'] += 1;
			}
		}
      	if($dmans['kouliang']>0){
          	$tt = intval(($data['recharge_num']*$dmans['kouliang']/100)/50);
			$data['click_num'] -= intval($data['click_num']*$dmans['kouliang']/100);
            $data['install_num'] -= intval($data['install_num']*$dmans['kouliang']/100);
            $data['register_num'] -= intval($data['register_num']*$dmans['kouliang']/100);
            $data['recharge_num'] -= 50*$tt;
            $data['recharge_member_num'] -= $tt;
		}
		return $data;
	}
	public function getToday($manager_id, $device) {
		$time = strtotime(date('Y-m-d'));
		$where['manager_id'] = $manager_id;
		if($device>0) {
			$where['device'] = $device;
		}
		
		$list = $this->where($where)->where("add_time > $time and is_install = 1")->select();
		$data['manager_id'] = $manager_id;
		$model = new User;
		//$data['manager_name'] = $model->where('id', $manager_id)->value('username');
      	$dmans = $model->where('id', $manager_id)->field('username,kouliang')->find();
		$data['manager_name'] = $dmans['username'];
		// $data['click_num'] = count($list);
		$data['click_num'] = 0;
		$data['install_num'] = 0;
		$data['register_num'] = 0;
		foreach($list as $v) {
			if($v['is_install'] == 1) {
				$data['install_num'] += 1;
			}
			if($v['is_register'] == 1) {
				$data['register_num'] += 1;
			}
		}
		if($device == 1) {
            $_wh = "manager_id = $manager_id and create_time >= $time and device = 1";
            $_fie2 = 'ios_install_num';
        }else if($device == 2) {
            $_wh = "manager_id = $manager_id and create_time >= $time and device = 2";
            $_fie2 = 'android_install_num';
        }else {
            $_wh = "manager_id = $manager_id and create_time >= $time";
            $_fie2 = 'sum(ios_install_num + android_install_num)';
        }
		$data['register_num'] = db('member')->where($_wh)->count();
        $data['install_num'] += db('count2')->where("manager_id = $manager_id and create_time >= $time")->value($_fie2);
		$list = db('recharge')->where(['manager_id'=>$manager_id, 'status'=>1, 'kouliang'=>0])->where('add_time', '>', $time)->field('user_id,money')->select();
		$data['recharge_num'] = 0;
		$data['recharge_member_num'] = 0;
		$member = [];
		foreach($list as $v) {
			$data['recharge_num'] += $v['money'];
			if(!in_array($v['user_id'], $member)) {
				$member[] = $v['user_id'];
				$data['recharge_member_num'] += 1;
			}
		}
      	if($dmans['kouliang']>0){
          	$tt = intval(($data['recharge_num']*$dmans['kouliang']/100)/50);
			$data['click_num'] -= intval($data['click_num']*$dmans['kouliang']/100);
            $data['install_num'] -= intval($data['install_num']*$dmans['kouliang']/100);
            $data['register_num'] -= intval($data['register_num']*$dmans['kouliang']/100);
            $data['recharge_num'] -= 50*$tt;
            $data['recharge_member_num'] -= $tt;
		}
		return $data;
	}


	public function getList($where, $value='') {
		if(is_array($where)) {
			$model = $this->where($where);
		}else {
			$model = where($where, $value);
		}
		$list = $model->alias('c')->join('app_member m','c.user_id=m.user_id','LEFT')->order('id DESC')->field('c.*,m.user_name')->paginate(15);
		foreach($list as $k=>$v) {
			$v['recharge_money'] = db('recharge')->where(['user_id'=>$v['user_id'], 'status'=>1])->sum('money');
		}
		$page = $list->render();
		return ['list'=>$list, 'page'=>$page];
	}

	public function getList2($where, $value='') {
		$model = $this->where($where)->where($value);
		$list = $model->alias('c')->join('app_member m','c.user_id=m.user_id','LEFT')->order('id DESC')->field('c.*,m.nick_name')->paginate(15);
		$page = $list->render();
		return ['list'=>$list, 'page'=>$page];
	}

	public function cacheToday($manager_id) {
		$time = strtotime(date('Y-m-d'));
		$where['manager_id'] = $manager_id;

		$list = $this->where($where)->where("add_time > $time and is_install = 1")->select();
		$data['manager_id'] = $manager_id;
		$model = new User;
		$data['manager_name'] = $model->where('id', $manager_id)->value('username');
		$data['click_num'] = count($list);
		$data['install_num'] = 0;
		$data['register_num'] = 0;
		foreach($list as $v) {
			if($v['is_install'] == 1) {
				$data['install_num'] += 1;
			}
			if($v['is_register'] == 1) {
				$data['register_num'] += 1;
			}
		}
		if($device == 1) {
            $_wh = "manager_id = $manager_id and create_time >= $time and device = 1";
            $_fie2 = 'ios_install_num';
        }else if($device == 2) {
            $_wh = "manager_id = $manager_id and create_time >= $time and device = 2";
            $_fie2 = 'android_install_num';
        }else {
            $_wh = "manager_id = $manager_id and create_time >= $time";
            $_fie2 = 'sum(ios_install_num + android_install_num)';
        }
		$data['register_num'] = db('member')->where($_wh)->count();
        $data['install_num'] += db('count2')->where("manager_id = $manager_id and create_time >= $time")->value($_fie2);
		$list = db('recharge')->where(['manager_id'=>$manager_id, 'status'=>1, 'kouliang'=>0])->where('add_time', '>', $time)->field('user_id,money')->select();
		$data['recharge_num'] = 0;
		$data['recharge_member_num'] = 0;
		$member = [];
		foreach($list as $v) {
			$data['recharge_num'] += $v['money'];
			if(!in_array($v['user_id'], $member)) {
				$member[] = $v['user_id'];
				$data['recharge_member_num'] += 1;
			}
		}
	}

}

?>