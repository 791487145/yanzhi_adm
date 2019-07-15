<?php
namespace app\admin\model;
use think\Model;

class DownLog extends Model
{
    //安装
    const INS = array(
        'no' => 0,
        'yes' => 1
    );
    //注册
    const REG = array(
        'no' => 0,
        'yes' => 1
    );

    /**
     * 获取数据
     * @return \think\db\Query
     */
    public function getAll()
    {
        return $this->with(['user' => function($query){
            $query->field('id,user_login');
        },'channel' => function($query){
            $query->field('id,name');
        }]);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class,'channel_id','id');
    }

    public function payment()
    {
        return $this->hasMany(PayPayment::class,'user_id','user_id');
    }

    public function scopeChannelId($query,$value)
    {
        return $query->whereIn('channel_id',$value);
    }

    public function scopeIns($query,$value)
    {
        return $query->where('is_ins',$value);
    }

    public function scopeReg($query,$value)
    {
        return $query->where('is_reg',$value);
    }

    public function searchInsAttr($query, $value)
    {
        $query->where('is_ins', $value);
    }

    /**
     * 数据处理
     * @param $param
     * @return mixed
     */
    public function datas($param)
    {
        foreach ($param as $value){
            $value->payment_sum = (int)$value->payment()->where('status',PayPayment::STATUS['pay_success'])->sum('money');
        }
        return $param;
    }

    /**
     * 设备and渠道
     * @param $param
     * @return $this
     */
    public static function log_count($param)
    {
        $model = new self();
        if($param['channel_id'] != ''){
            $model = $model->channelId($param['channel_id']);
        }
        if(!empty($param['device'])) $model = $model->where('device',$param['device']);
        $model = self::act($param,$model);
        if(isset($param['param_time'])){
            $model = self::param_time($param,$model);
        }

        return $model;
    }

    public static function act($param,$model)
    {
        if($param['act'] == 1){
            $model = $model->ins(self::INS['yes']);
        }
        if($param['act'] == 2){
            $model = $model->reg(self::REG['yes']);
        }
        return $model;
    }

    /**
     * 时间比较
     * @param $param
     * @return $this
     */
    public static function param_time($param,$model)
    {
        if(!empty($param['start_time'])) $model = $model->where($param['param_time'],'>=',strtotime($param['start_time']));
        if(!empty($param['end_time'])) $model = $model->where($param['param_time'],'<=',(strtotime($param['end_time']) + 86400));
        return $model;
    }


}

?>