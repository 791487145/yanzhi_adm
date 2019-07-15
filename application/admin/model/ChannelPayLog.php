<?php
namespace app\admin\model;

use service\PHPExcelService;
use think\Model;
use service\ExportService as Export;

class ChannelPayLog extends Model
{
    //渠道
    const TYPE = array(
        1 => '渠道'
    );
    //打款状态
    const STATUS = array(
        'not_pay' => 1,
        'pay_success' => 2
    );

    public function getTypeAttr($value)
    {
        return self::TYPE[$value];
    }

    public function getRedioAttr($value)
    {
        return $value.'%';
    }

    public function scopeStatus($query,$status)
    {
        return $query->where('status',$status);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_store_id','id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class,'channel_id','id');
    }

    /**
     * 获取数据
     * @param $param
     * @param $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getAll($param,$limit = 10)
    {
        $model = empty($param['status']) ? new self(): self::status($param['status']);
        if(!empty($param['start_time'])){
            $model = $model->whereTime('created_time','>',strtotime($param['start_time']));
        }
        if(!empty($param['end_time'])){
            $model = $model->whereTime('created_time','<=',(strtotime($param['end_time']) + 86400));
        }

        if(isset($param['export'])){
           self::export($model);
        }

        return self::getAllData($model,$limit,$param);
    }

    static function export($model)
    {
        $channel_logs = $model->select();
        $export = [];

        foreach ($channel_logs as $channel_log){
            $export[] = array(
                $channel_log->id,
                date('Y-m-d',$channel_log->created_time),
                $channel_log->channel_id,
                $channel_log->channel()->value('name'),
                $channel_log->remark,
                $channel_log->user->user_login,
                '渠道',
                $channel_log->pay_info,
                ($channel_log->status ==  self::STATUS['not_pay']) ? '已打款' : '未打款',
                $channel_log->payed_payment_num_vip,
                $channel_log->payed_payment_num_vip_ratio,
                $channel_log->payed_payment_num,
                $channel_log->payed_payment_num_ratio,
                $channel_log->channel_num,
                $channel_log->pay_num
            );
        }

        PHPExcelService::setExcelHeader(['账单ID','账单日期','渠道ID','渠道名称','备注','所属商务','类型','打款信息','支付状态','vip充值金额','vip分成比率','趣币充值金额','趣币分成比率','渠道扣量','应打款金额'])
            ->setExcelTile('打款导出',' ',' 生成时间：'.date('Y-m-d H:i:s',time()))
            ->setExcelContent($export)
            ->ExcelSave();
    }

    public static function getAllData($model,$limit,$param)
    {
        return $model->with(['user' => function($query){
            $query->field('id,user_login');
        }],['channel' => function($query){
            $query->field('id,name');
        }])->paginate($limit,false,['query' => $param]);
    }

}

?>