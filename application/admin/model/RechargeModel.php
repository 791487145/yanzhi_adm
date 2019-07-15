<?php
namespace app\admin\model;
use think\Model;
class RechargeModel extends Model
{
	protected $table = 'app_recharge';

	public function getDayOrder($starttime, $endtime, $manager_id) {
		// $where = ['o.manager_id'=>$manager_id, 'o.status'=>1, 'o.kouliang'=>0];
		$where = ['o.manager_id'=>$manager_id, 'o.status'=>1];
		$field = 'o.*,m.device';
		$list = $this->alias('o')->join('app_member m','o.user_id = m.user_id','left')
		->where('o.end_time', '>=', $starttime)->field($field)->where('o.end_time', '<', $endtime)
		->where($where)->select();
		return $list;
	}

	public function getlist($mid=null, $start=0, $end=0, $status=null) {
		// $_w = ['mid'=>$mid, 'startDate'=>date('Y-m-d',$start), 'endDate'=>date('Y-m-d',$end)];
		$model = $this;
		$where = [];
		if($mid !== null) {
			$where['o.manager_id'] = $mid;
		}
		if($status !== null) {
			if($status === '0' || $status === '1') {
				$where['o.kouliang'] = $status;
			}
		}
		$where['o.status'] = 1;
		if($start > 0 && $end > 0) {
			$end += 86400;
			$model->where("o.end_time >= $start and o.end_time <= $end");
		}
		$model->alias('o')->join('app_manager m','o.manager_id=m.id','left')->join('app_member u','o.user_id=u.user_id','left');
		$list = $model->where($where)->order('o.id', 'DESC')->field('o.*,u.nick_name, u.user_name,u.device,m.username,m.remarks')->paginate(15, false, ['query'=>request()->param()]);
		
		$page = $list->render();
		return ['list'=>$list, 'page'=>$page];
	}

	public function getlist2($user_id) {
		$where['o.status'] = 1;
		$where['o.user_id'] = $user_id;
		$model = $this->alias('o')->join('app_manager m','o.manager_id=m.id','left')->join('app_member u','o.user_id=u.user_id','left');
		$list = $model->where($where)->order('o.id', 'DESC')->field('o.*,u.user_name,m.username,m.remarks')->select();
		return ['list'=>$list];
	}

	public function getlist3($mid=null, $start=0, $end=0) {
		$where = [];
		if($mid !== null) {
			$where['o.manager_id'] = $mid;
		}
		$where['o.status'] = 1;
		// if($start > 0 && $end > 0) {
			$end += 86400;
			// $this->where("o.end_time >= $start and o.end_time <= $end");
		// }
		$list = $this->alias('o')->join('app_manager m','o.manager_id=m.id','left')->join('app_member u','o.user_id=u.user_id','left')->where($where)->where("o.end_time >= $start and o.end_time <= $end")->order('o.id', 'DESC')->field('o.*,u.nick_name,m.username,m.remarks')->select();
		return $list;
	}
}

?>