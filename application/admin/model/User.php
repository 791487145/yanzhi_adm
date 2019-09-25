<?php  
namespace app\admin\model;
use think\Model;
use think\facade\Session;
use think\facade\Log;
class User extends Model
{
    const USER_TYPE_ADMIN = 1;
    const USER_TYPE_USER = 2;
    const USER_TYPE_CHANNEL = 3;
    const USER_TYPE_AGENT = 5;

    const USER_TYPE = array(
        'admin' => 1,
        'user'  => 2,
        'channel' => 3,
        'agent' => 5
    );

    const USER_STATUS = array(
        'forbidden' => 0,
        'normal'    => 1,
        'not_verification' => 2
    );


    public function scopeUserType($query, $user_type)
    {
        $query->where('user_type', $user_type);
    }

    public function scopeUserStatus($query, $user_status)
    {
        $query->where('user_status', $user_status);
    }

    public function token()
    {
        return $this->hasMany(UserToken::class,'user_id','id');
    }


    /**
     * 登录
     * @param $data
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
	public function login($data) {
        $data['user_pass'] = cmf_password($data['user_pass']);

		$user = $this->where($data)->find();

		if($user) {
			if($user['user_status'] != 1 || ($user['user_type'] != 1 && $user['user_type'] != 5)) {
				return false;
			}
            $user['last_login_time'] = time();

			Session::set('user',serialize($user));

			return $user;
		}else {
			return false;
		}
	}

    /**
     * 创建数据
     * @param $data
     * @return User
     */
	public static function create_one($data,$type = self::USER_TYPE['channel'])
    {
        $data['user_type'] = $type;
        $data['user_pass'] = cmf_password($data['user_pass']);
        return self::create($data);
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class,'id','user_id');
    }

    /**
     * 获取数据
     * @return array
     */
    public static function getAll($limit)
    {
        $users_all = self::with(['token' => function($query){
            $query->order('id','desc');
        }])->userType(self::USER_TYPE['user'])->field('id,user_login,province,sex')->fetchSql(false)->select();

        $users = self::with(['token' => function($query){
            $query->order('id','desc');
        }])->userType(self::USER_TYPE['user'])->field('id,user_login,province,sex')->with('token')->paginate($limit);
        //halt($users_all);

        $province = [];

        foreach ($users_all as $k=>$user){
            if(!empty($user['province'])){
                if(isset($province['province'][$user['province']])){
                    $province['province'][$user['province']] = $province['province'][$user['province']] + 1;
                }else{
                    $province['province'][$user['province']] = 1;
                }
            }

            if(!empty($user['token'][0])) {
                if (isset($province['device_type'][$user['token'][0]['device_type']])) {
                    $province['device_type'][$user['token'][0]['device_type']] = $province['device_type'][$user['token'][0]['device_type']] + 1;
                } else {
                    if (!empty($user['token'][0]) && !empty($user['token'][0]['device_type'])) {
                        $province['device_type'][$user['token'][0]['device_type']] = 1;
                    }
                }
            }

        }
        //dump($users->toArray());
        return compact('users','province');
    }
}

?>