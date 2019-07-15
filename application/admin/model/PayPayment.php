<?php
namespace app\admin\model;
use think\Model;
use app\admin\model\PayProfit;

class PayPayment extends Model
{

    const STATUS = array(
        'not_pay' => 0,
        'pay_success' => 1
    );

    const CHANNEL_STATUS = array(
        'no' => 0,
        'yes' => 1
    );

    const TYPE = array(
        'vip' => 2,
        'balance' => 1
    );

    public function scopeChannelId($query,$value)
    {
        return $query->whereIn('channel_id',$value);
    }

    public function scopeStatus($query,$value)
    {
        return $query->where('status',$value);
    }

    public function scopeChannelStatis($query,$value)
    {
        return $query->where('channel_statis',$value);
    }

    public function scopePayTime($query,$value)
    {
        return $query->whereBetweenTime('pay_time',$value);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class,'channel_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }


    /**
     * 设备and渠道
     * @param $param
     * @return $this
     */
    public static function log_count($param)
    {
        $model = new self();
        if(!empty($param['channel_id']))  $model = $model->channelId($param['channel_id']);

        if(isset($param['param_time'])){
            $model = self::param_time($param,$model);
        }
        if(isset($param['money_cat'])){
            $model = $model->where('type',$param['money_cat']);
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

    /**
     * 充值类型
     * @param $param
     * @return $this
     */
    public static function type($type,$model)
    {
        return $model->where('type',$type);
    }

    public static function getAll($data,$limit)
    {
        $model = $data['type'] == '' ? new self() : self::where('type',$data['type']);
        $model = $model->status(self::STATUS['pay_success']);
        if($data['channel_statis'] != '')  $model = $model->channelStatis($data['channel_statis']);
        if($data['channel_id'] != '')  $model = $model->ChannelId($data['channel_id']);

        return $model->with(['user' => function($query){
            $query->field('id,user_login');
        }],['channel' => function($query){
            $query->field('id,name');
        }])->order('add_time','desc')->paginate($limit);
    }




}

?>