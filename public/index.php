<?php
require '../vendor/autoload.php';

$app = new \Slim\Slim();
$app->get('/foo', function () {
	echo "Foo!";
});
$app->run();
