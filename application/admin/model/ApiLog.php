<?php
namespace app\admin\model;
use think\Model;
use Log;

class ApiLog extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }


}

?>