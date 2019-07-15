<?php
namespace app\admin\model;
use think\Model;
use Log;

class Channel extends Model
{

    const STATUS = array(
        'wait_check' => 0,
        'success_check' => 1
    );

    public function scopeId($query,$id)
    {
        return $query->where('id',$id);
    }


    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public static function addChannel($data)
    {
        return self::create($data);
    }

    public function down_log()
    {
        return $this->hasMany(DownLog::class,'id','channel_id');
    }

    /**
     * 获取数据
     * @param $data
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function getAll($data)
    {
        $model = empty($data['user_store_id']) ? new  self() : self::where('user_store_id',$data['user_store_id']);
        $channels = $model->select()->toArray();


        foreach ($channels as $channel){
            if(!empty($data['not_effective'])) {
                self::where('id', $channel['id'])->update(['effective' => (100 - $data['not_effective'])]);
            }
            if(!empty($data['domain'])) {
                self::where('id', $channel['id'])->update(['domain' => $data['domain']]);
            }
        }

        return $channels;
    }

    /**
     * 数据报表处理
     * @param $param
     * @param DownLog $downLog
     * @param PayPayment $payment
     * @return array
     */
    public function tables($param)
    {
        if(isset($param['channel_ids'])){
            $param['channel_id'] = $param['channel_ids'];
        }
        $channel_id = $param['channel_id'];

        $data['fen'] = [];

        $down_log_account_num = 0;//安装总量
        $reg_log_account_num = 0;//注册总量
        $paymeny_account_num = 0;//总充值金额
        $paymeny_account_num_money = 0;//总充值余额
        $paymeny_account_num_vip = 0;//总充值vip
        $paymeny_account_people_num = 0;//总充值人数

        if(empty($channel_id)){
            $channel_id = array(1000000);
        }
        foreach ($channel_id as $val){
            unset($param['channel_id']);
            $param['channel_id'] = array($val);

            $channel_name = Channel::where('id',$val)->value('name');

            $down_log_num = DownLog::log_count(array_merge($param,array('act' => 1,'param_time' => 'ins_time')))->count();//安装量

            $reg_log_num = DownLog::log_count(array_merge($param,array('act' => 2,'param_time' => 'reg_time')))->count();//注册量

            $paymeny_num = PayPayment::log_count(array_merge($param,array('param_time' => 'pay_time')))->sum('money');//充值金额

            $paymeny_num_money = PayPayment::log_count(array_merge($param,array('money_cat' => PayPayment::TYPE['balance'],'param_time' => 'pay_time')))->sum('money');//充值余额

            $paymeny_num_vip = PayPayment::log_count(array_merge($param,array('money_cat' => PayPayment::TYPE['vip'],'param_time' => 'pay_time')))->sum('money');//充值vip

            $paymeny_people_num = PayPayment::log_count(array_merge($param,array('param_time' => 'pay_time')))->distinct(true)->field('user_id')->select()->count();//充值人数

            $reg_dowm_rate = $down_log_num == 0 ? 0 : (number_format($reg_log_num/$down_log_num,3) * 100).'%';//注册转化率

            $paymeny_people_reg_rate = $reg_log_num == 0 ? 0 : (number_format($paymeny_people_num/$reg_log_num ,3) * 100) .'%';//充值转化率

            $payment_reg_rate = $reg_log_num == 0 ? 0 : number_format($paymeny_num/$reg_log_num,1);//充值ARP

            $down_log_account_num = $down_log_account_num + $down_log_num;
            $reg_log_account_num = $reg_log_account_num + $reg_log_num;
            $paymeny_account_num = $paymeny_account_num + $paymeny_num;
            $paymeny_account_num_money = $paymeny_account_num_money + $paymeny_num_money;
            $paymeny_account_num_vip = $paymeny_account_num_vip + $paymeny_num_vip;
            $paymeny_account_people_num = $paymeny_account_people_num + $paymeny_people_num;

            $data['fen'][] = array(
                'channel_name' => $channel_name,
                'down_log_num' => $down_log_num,
                'reg_log_num' => $reg_log_num,
                'paymeny_num' => $paymeny_num,
                'paymeny_people_num' => $paymeny_people_num,
                'reg_dowm_rate' => $reg_dowm_rate,
                'paymeny_people_reg_rate' => $paymeny_people_reg_rate,
                'payment_reg_rate' => $payment_reg_rate,
                'paymeny_num_money' => $paymeny_num_money,
                'paymeny_num_vip' => $paymeny_num_vip
            );
        }

          $data['zong'] = array(
            'down_log_account_num' => $down_log_account_num,
            'reg_log_account_num' => $reg_log_account_num,
            'paymeny_account_num' => $paymeny_account_num,
            'paymeny_account_people_num' => $paymeny_account_people_num,
            'reg_dowm_account_rate' => $down_log_account_num == 0 ? 0 : (number_format($reg_log_account_num/$down_log_account_num ,3)*100).'%',////总注册转化率
            'paymeny_people_reg_account_rate' => $reg_log_account_num == 0 ? 0 :(number_format($paymeny_account_people_num/$reg_log_account_num ,3)*100) .'%',//总充值转化率
            'payment_reg_account_rate' => $reg_log_account_num == 0 ? 0 :number_format($paymeny_account_num/$reg_log_account_num,1),//总充值ARP
            'paymeny_account_num_money' => $paymeny_account_num_money,
            'paymeny_account_num_vip' => $paymeny_account_num_vip
        );
        return $data;
    }
}

?>