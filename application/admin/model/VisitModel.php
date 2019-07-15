<?php
namespace app\admin\model;
use think\Model;

class VisitModel extends Model
{
	protected $table = 'app_visit_log';
	public function getlist() {
		$list = $this->order('id', 'DESC')->paginate(15);
		$page = $list->render();
		return ['list'=>$list, 'page'=>$page];
	}

	public function getlist2($where=[]) {
		$model = $this->where($where);
		$list = $model->alias('v')->join('app_member u','v.user_id=u.user_id','LEFT')->order('v.id', 'DESC')->field('v.visit_time,v.visit_ip,u.user_name,u.nick_name,u.headimg')->group('v.user_id')->paginate(15);
		$page = $list->render();
		return ['list'=>$list, 'page'=>$page];
	}
}

?>