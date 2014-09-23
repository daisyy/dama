<?php
/**
 * @author: Jackong
 * Date: 14-9-22
 * Time: 下午5:27
 */

namespace src\routers;


use Slim\Slim;

require_once APP_DIR . '/libs/dama2/Dama2CurlApi.php';

class Code {
    public function __construct(Slim $app)
    {
        $app->post('/image/data', array($this, 'requestByImageData'));
        $app->get('/value', array($this, 'getValue'));
    }

    public function requestByImageData() {
        $app = Slim::getInstance();
        $req = $app->request();
        $account = $req->post('account');
        $password = $req->post('password');
        if (strlen($account) < 4 || strlen($password) < 4) {
            return $this->response(array(
                'ok' => false,
                'msg' => '账号或密码有误'
            ));
        }
        $data = base64_decode($req->post('image'));
        $file = "/tmp/$account";
        if (false === file_put_contents($file, $data)) {
            return $this->response(array(
                'ok' => false,
                'msg' => '图片录入失败'
            ));
        }

        $api = new \Dama2Api($account, $password);
        $result = $api->decode($file, 42);
        if (!isset($result['ret']) || $result['ret'] || !isset($result['id'])) {
            return $this->response(array(
                'ok' => false,
                'msg' => '无法打码'
            ));
        }
        return $this->response(array('ok' => true, 'id' => $result['id']));
    }

    public function getValue() {
        $app = Slim::getInstance();
        $req = $app->request();
        $account = $req->get('account');
        $password = $req->get('password');
        if (strlen($account) < 4 || strlen($password) < 4) {
            return $this->response(array(
                'ok' => false,
                'msg' => '账号或密码有误'
            ));
        }
        $id = $req->get('id');
        if (is_null($id)) {
            return $this->response(array(
                'ok' => false,
                'msg' => '错误的ID'
            ));
        }
        $api = new \Dama2Api($account, $password);
        $result = $api->get_result($id);
        if (isset($result['ret'])) {
            if ($result['ret'] != '0' && $result['ret'] == '-303') {
                return $this->response(array(
                    'ok' => false,
                    'msg' => '结果未出',
                    'id' => $id,
                ));
            }

            if ($result['ret'] == '0' && $result['result']) {
                return $this->response(array(
                    'ok' => true,
                    'code' => $result['result']
                ));
            }
        }

        return $this->response(array(
            'ok' => true,
            'msg' => '无法获取结果'
        ));
    }

    private function response($data) {
        $app = Slim::getInstance();
        if (!isset($data['ok']) || !$data['ok']) {
            $app->getLog()->error($data['msg']);
            return true;
        }
        $app->response()->setBody(json_encode($data));
        return false;
    }
} 