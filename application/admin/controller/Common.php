<?php 
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\facade\View;
use think\facade\Session;;
use Log;
class Common extends Controller
{
	protected $request;
	protected $user_id;
	protected $userInfo;
    protected $middleware = ['CheckAuth'];
    public $limit = 10;

	public function __construct(Request $request) {

		$this->request = $request;
        define('MODULE_NAME', $this->request->module()); 
        define('CONTROLLER_NAME', $this->request->controller()); 
        define('ACTION_NAME', $this->request->action());

        $this->checkLogin();

	}



	protected function checkLogin() {

		$session = Session::get('user');
        $data = unserialize($session);

        $this->user_id = $data['id'];
        $this->userInfo = $data;

        View::share([
            'user_type' =>$this->userInfo['user_type'],
            'user_id' => $this->user_id
        ]);
	}



	
}

?>