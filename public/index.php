<?php
require_once 'env.php';
require_once APP_DIR . '/vendor/autoload.php';

$app = new \Slim\Slim(
    array(
        'debug' => false,
        'cookies.encrypt' => true,
        'cookies.secret_key' => 'd3@SD#@!TXZE@',
        'cookies.cipher' => MCRYPT_RIJNDAEL_256,
        'cookies.cipher_mode' => MCRYPT_MODE_CBC,

        'log.enabled' => true,
        'log.writer' => new src\common\LogWriter(),
        'log.level' => \Slim\Log::DEBUG
    )
);

//处理request数据类型
$app->add(new \Slim\Middleware\ContentTypes());

$app->notFound(function () use ($app) {
    $app->getLog()->warning('url not found:' . $app->request()->getResourceUri());
    $app->render('404.html');
});

//处理所有未catch exception
$app->error(function(Exception $e) use($app) {
    $app->getLog()->critical('server error: ' . $e->getMessage());
    $app->halt(500, "sorry! server error");
});

$request = $app->request();
$paths = explode('/', $request->getResourceUri());

if (count($paths) < 4 || strtolower($paths[1]) != 'api') {
    $app->getLog()->error('bad request:' . $request->getResourceUri());
    $app->status(400);
}

$app->group('/api', function() use($app, $paths) {
    $router = ucfirst(strtolower($paths[2]));
    if (!file_exists(APP_DIR . "/src/routers/$router.php")) {
        return;
    }
    $app->group("/$paths[2]", function() use($app, $router) {
        $routerClass = "src\\routers\\$router";
        new $routerClass($app);
    });
});

$app->run();
