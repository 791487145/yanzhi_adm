<?php

namespace app\admin\middleware;

use app\admin\model\User;
use think\facade\Session;
use Log;
class CheckAuth
{
    public function handle($request, \Closure $next)
    {
        $action = $request->controller();
        if($action == 'Login') return $next($request);
        $session = Session::get('user');

        if(!isset($session) || $session == '') return redirect('admin/login/login');

        return $next ($request);
    }
}
