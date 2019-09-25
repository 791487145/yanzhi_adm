<?php
namespace app\admin\model;
use think\Model;
use Log;

class ApiName extends Model
{

    public function apiLog()
    {
        return $this->hasMany(ApiLog::class,'uri','uri');
    }


    public static function getAll($data,$limit)
    {
        if(empty($data['start_time'])){
            $data['start_time'] = 0;
        }
        if(empty($data['end_time'])){
            $end_time = date('Y-m-d H:i:s');
        }else{
            $end_time = date('Y-m-d H:i:s',strtotime($data['end_time']) + 86400);
        }
        return ApiName::withCount(['apiLog' => function($query) use($data,$end_time){
            $query->whereBetweenTime('logtime',$data['start_time'],$end_time);
        }])->paginate($limit,false,['query' => $data]);
    }


}

?>