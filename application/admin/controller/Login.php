<?php
namespace app\admin\controller;

use app\admin\model\User;
use service\HttpService;
use think\Controller;
use service\UtilService as Util;
use think\View;
use think\Log;
class Login extends Controller
{
    public function post_login(User $userModel)
    {
        $data = Util::postMore([
            ['user_login'],
            ['user_pass']
        ],$this->request);

        $user = $userModel->login($data);

        if($user) {
           return redirect('admin/index/index');
        }else {
            $this->error('用户名或密码错误', url('admin/login/login'));
        }
    }

    public function login()
    {
        return view('user_login');
    }

    //错误信息
    private static $curlError;
    //header头信息
    private static $headerStr;
    //请求状态
    private static $status;

    public function test_order(Log $log)
    {
        $config = array(
            'appid' => 'wx1c8352db69481563',
            'mch_id' => '1518356971',
            'key' => 'Cu4zloMTzwkOSXrPWFid6Ts5wT6BMqJy',
        );
        $unified = array(
            'appid' => $config['appid'],
            'attach' => '2342',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => '会员充值',
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::nonce_str(),//随机字符串
            'notify_url' => 'http://adm.miliaoapp.cn/admin/login/wechat_h',
            'out_trade_no' => date('YmdHis').mt_rand(1000, 9999),
            'spbill_create_ip' => self::get_server_ip()?:'127.0.0.1',//终端的ip
            'total_fee' => 1,       //单位 转为分
            'trade_type' => 'MWEB',//交易类型 默认
            'scene_info' => '{"h5_info": {"type":"IOS","app_name": "趣约视频","bundle_id": "com.shenlanhuanbao.goodlife"}}'
        );
        $unified['sign'] = self::getSign($unified, $config['key']);//签名
        $log->alert('$unified_'.print_r($unified,true));
        $responseXml = HttpService::postRequest('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));

        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder->return_code != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }

        $log->alert('$unifiedOrder_'.print_r($unifiedOrder,true));

        $data = array(
            'mweb_url' => $unifiedOrder->mweb_url
        );
        return view('order',$data);

    }

    public function wechat_h(Log $log)
    {
        $log->alert('success_pay');
        $postStr = file_get_contents('php://input');
        $log->alert('$postStr_'.print_r($postStr,true));
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) die('parse xml error');
        if ($postObj->return_code != 'SUCCESS') die($postObj->return_msg);
        if ($postObj->result_code != 'SUCCESS') die($postObj->err_code);
        $arr = (array)$postObj;
        $log->alert('$arr_'.print_r($arr,true));
        unset($arr['sign']);
        if ($this->getSign($arr, 'Cu4zloMTzwkOSXrPWFid6Ts5wT6BMqJy') == $postObj->sign) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return $arr;
        }

    }

    //随机32位字符串
    public static function nonce_str(){
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i=0;$i<32;$i++){
            $result .= $str[rand(0,48)];
        }
        return $result;
    }
    //数组转XML
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    /**
     * 获取签名
     */
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    //获取IP
    public static function get_server_ip()
    {
        $ip = '';
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $ip_arr = explode(',', $ip);
        return $ip_arr[0];
    }

    public static function request($url, $method = 'get', $data = array(), $header = false, $timeout = 15)
    {
        self::$status = null;
        self::$curlError = null;
        self::$headerStr = null;

        $curl = curl_init($url);
        $method = strtoupper($method);
        //请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        //post请求
        if ($method == 'POST') curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        //设置header头
        if ($header !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        //返回抓取数据
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //输出header头信息
        curl_setopt($curl, CURLOPT_HEADER, true);
        //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //https请求
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        self::$curlError = curl_error($curl);

        list($content, $status) = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        self::$status = $status;
        self::$headerStr = trim(substr($content, 0, $status['header_size']));
        $content = trim(substr($content, $status['header_size']));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    public static function postRequest($url, $data = array(), $header = false, $timeout = 10)
    {
        return self::request($url, 'post', $data, $header, $timeout);
    }


}
