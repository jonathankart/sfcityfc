<?php

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__."/../conf/conf.php";
require_once __DIR__."/../conf/pw.php";

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app->config = $SFCITY_CONFIG;
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
	'twig.debug'	=>	true
));


$app->get('/', function () use ($app) {
		return $app['twig']->render('home.twig');
});

$app->post('/subscribe',function (Request $request) use ($app){
	$email = $request->get('email');
	$results = array();
	if($email && count($app['validator']->validateValue($email, new Assert\Email()))===0){
		$mc = new Mailchimp($app->config->mailchimp->api_key);
		$mc->lists->subscribe($app->config->mailchimp->lists->newsletter, array('email'=>$email),array('name'=>$request->get('name'),'phone'=>$request->get('phone')),'html',false,true,true,true);
		$results['success'] = true;
	}else{
		$results['success'] = false;
		$results['msg'] = 'invalid email';
	}

	return $app->json($results);
});

try{
	$app->run();
}catch (Exception $e){
	var_dump($e->getMessage());
}

