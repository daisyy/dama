<?php
/**
 * 打码兔web接口php实例
 * 
 * 首先要将如下class的APP_KEY,APP_ID分别替换为你的软件key和软件id
 *
 * 调用方法
 * $testApi = new Dama2Api('用户名', '密码');
 *
 * 1. 读取用户信息
 * $info = $testApi->read_info();
 *
 * 2. 获取用户余额
 * $balance = $testApi->get_balance();
 *
 * 3. 注册用户
 * $register = Dama2Api::register('用户名', '密码', '邮箱', 'qq', '电话');
 *
 * 4. POST文件打码, 通过文件上传打码
 * $decode = $testApi->decode('文件路径', '验证码类型', '验证码长度', '超时时间');
 *
 * 5. URL 打码
 * $decode_url = $testApi->decode_url('验证码url', '验证码类型', 'cookie', 'referer', '验证码长度', '超时时间');
 *
 * 6. 获取打码结果
 * $get_result = $testApi->get_result('验证码ID')
 *
 * 7. 报告错误
 * $report_error = $testApi->report_error('验证码ID')
 */
session_start();
require( dirname(__FILE__) . '/HttpClient.class.php');
require( dirname(__FILE__) . '/Dama2Encrypt.php');

class Dama2Api
{
    const APP_KEY = '替换成你的app_key';//替换成你的app_key
    const APP_ID = '替换你的app_id';//替换你的app_id
    const HOST = 'api.dama2.com';
    const PORT = 7788;
    private $client;
    private $prefix_sess = '_dama2_api_';
    private $expire_time = 540;

    public function __construct($username, $password){
        $this->client = new HttpClient(self::HOST, self::PORT);
        // $this->client->setDebug(true);
        $this->client->setHandleRedirects(false);//避免跳转问题,在不同网站之间
        $this->username = $username;
        $this->password = $password;
    }

    private function login(){
        if($this->is_auth_alive()){
            return true;
        }

        if( $this->client->get('/app/preauth')){
            $json = json_decode($this->client->getContent(), true);
            if($json['ret'] == 0){
                $password = md5($this->password);
                $encinfo = $json['auth'] . "\n" . $this->username . "\n" . $password ;
                $encinfo = Dama2Encrypt::encrypt($encinfo, self::APP_KEY);
                $this->client->get('/app/login', array(
                    'appID' => self::APP_ID,
                    'encinfo' => $encinfo
                    ));
                $res = @json_decode($this->client->getContent(), true);
                if(isset($res['ret']) && $res['ret'] == 0){
                    $this->set_auth($res['auth']);
                    return true;
                }
                if(isset($res['ret']) && $res['ret'] == -104)
                    throw new Exception("用户名或密码错误", 1);                    
            }
        }
        return false;
    }

    private function http_request($path, $params, $method='get'){
        if($this->client->$method($path, $params)){
            $json = json_decode($this->client->getContent(), true);
            if(isset($json['ret']) && ($json['ret'] == '-10001' || $json['ret'] == '-10003')) {
                unset($_SESSION[$this->prefix_sess . 'auth']);
                $this->login();
                $params['auth'] = $this->get_auth();
                if($this->client->$method($path, $params)){
                    $json = json_decode($this->client->getContent(), true);
                }else{
                    return false;
                }
            }
            if(isset($json['ret']) && isset($json['auth']) && $json['ret'] == 0){
                $this->set_auth($json['auth']);
            }
            return $json;
        }
        throw new Exception($this->client->errormsg, 1);
        
        return false;
    }

    private function set_auth($auth){
        $_SESSION[$this->prefix_sess . 'name'] = $this->username;
        $_SESSION[$this->prefix_sess . 'password'] = $this->password;
        $_SESSION[$this->prefix_sess . 'auth'] = urldecode($auth);
        $_SESSION[$this->prefix_sess . 'time'] = time();
    }

    private function is_auth_alive(){
        if(! isset($_SESSION[$this->prefix_sess . 'auth']) ||
            time() - $_SESSION[$this->prefix_sess . 'time'] > $this->expire_time ||
            $_SESSION[$this->prefix_sess . 'name'] !== $this->username ||
            $_SESSION[$this->prefix_sess . 'password'] !== $this->password){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 获取用户auth信息
     * @return mixed 成功返回auth, 失败返回false
     */
    private function get_auth(){
        if(! $this->is_auth_alive()){
            $this->login();
        }
        return @$_SESSION[$this->prefix_sess . 'auth'];
    }

    /**
     * 注册用户
     * 注册用户, 调用次数有限制, 每一秒只能调用一次
     * @param string 用户名
     * @param string 密码
     * @param string 邮箱, 用于接收打码兔的动态验证码
     * @param string qq
     * @param string 电话
     * @return array
     */
    public static function register($username, $password, $email, $qq='', $tel=''){
        $client = new HttpClient(self::HOST, self::PORT);
        if( $client->get('/app/preauth')){
            $json = json_decode($client->getContent(), true);
            if($json['ret'] == 0){
                $password = md5($password);
                $encinfo = $json['auth'] . "\n" . $username . "\n" . $password ;
                $encinfo = Dama2Encrypt::encrypt($encinfo, self::APP_KEY);
                $client->get('/app/register', array(
                    'appID' => self::APP_ID,
                    'encinfo' => $encinfo,
                    'qq' => $qq,
                    'email' => $email,
                    'tel' => $tel
                    ));
                $res = @json_decode($client->getContent(), true);
                // if(isset($res['ret']) && $res['ret'] == 0){
                //     $this->set_auth($res['auth']);
                // }
                return $res;
            }
        }
        return false;
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function read_info(){
        return $this->http_request('/app/readInfo', array('auth' => $this->get_auth()));
    }

    /**
     * 获取用户余额
     * @return array
     */
    public function get_balance(){
        return $this->http_request('/app/getBalance', array('auth' => $this->get_auth()));
    }

    /**
     * POST 文件打码
     * post上传文件打码
     * @param string 图片文件的路径
     * @param int 验证码类型, 参考打码兔wiki, http://wiki.dama2.com/index.php?n=ApiDoc.GetSoftIDandKEY1
     * @param int 验证码长度 [可选]
     * @param int 超时时间, 表示验证码多少会超时
     * @return array
     */
    public function decode($file, $type, $len='', $timeout=''){
        $params = array(
            '__file__' => $file,
            'type' => $type,
            'auth' => $this->get_auth()
            );
        if($len) $params['len'] = $len;
        if($timeout) $params['timeout'] = $timeout;
        return $this->http_request('/app/decode', $params, 'post');
    }

    /**
     * URL 打码
     * 通过传递验证码的url地址来打码
     * @param string url地址 
     * @param int 验证码类型, 参考打码兔wiki, http://wiki.dama2.com/index.php?n=ApiDoc.GetSoftIDandKEY1
     * @param string cookie值 [可选]
     * @param string referer [可选]
     * @param int 验证码的长度, 表示多少位验证码
     * @param int 超时时间, 表示验证码多少会超时
     * @return array
     */
    public function decode_url($url, $type, $cookie='', $referer='', $len='', $timeout=''){
        $params = array(
            'url' => $url,
            'type' => $type,
            'auth' => $this->get_auth()
            );
        $cookie ? $params['cookie'] = $cookie : '';
        $referer ? $params['referer'] = $referer : '';
        $len ? $params['len'] = $len : '';
        $timeout ? $params['timeout'] = $timeout : '';
        return $this->http_request('/app/decodeURL', $params, 'post');
    }

    /**
     * 查询打码结果
     * 由于打码的过程需要时间, 需要根据自己软件情况实现轮询来查询结果
     * @param int 验证码id,  由 decode 或 decode_url 函数打码成功后返回的id字段
     * @return array
     */
    public function get_result($id){
        $params = array(
            'auth' => $this->get_auth(),
            'id' => $id
            );
        return $this->http_request('/app/getResult', $params);
    }

    /**
     * 报告错误
     * 根据get_result获取到打码的结果后, 判断打码是否正确, 不正确调用此接口
     * @param int 验证码id,  由 decode 或 decode_url 函数打码成功后返回的id字段
     * @return array
     */
    public function report_error($id){
        $params = array(
            'auth' => $this->get_auth(),
            'id' => $id
            );
        return $this->http_request('/app/reportError', $params);
    }
}