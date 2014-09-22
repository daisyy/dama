<?php
/**
 * @author: Jackong
 * Date: 14-9-22
 * Time: 下午5:27
 */

namespace src\routers;


use Slim\Slim;

class Code {
    public function __construct(Slim $app)
    {
        $app->post('/image', array($this, 'requestByImage'));
        $app->get('/value', array($this, 'getValue'));
    }

    public function requestByImage() {

    }

    public function getValue() {

    }

} 