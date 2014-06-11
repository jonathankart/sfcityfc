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
		$name = $request->get('name');
		$phone = $request->get('phone');
		file_put_contents($app->config->mailchimp->log,date('Y-m-d H:i:s').",$email,$name,$phone\n",FILE_APPEND);
		try{
			$mc = new Mailchimp($app->config->mailchimp->api_key);
			$mc->lists->subscribe($app->config->mailchimp->lists->newsletter, array('email'=>$email),array('name'=>$name,'phone'=>$phone),'html',false,true,true,true);
			$results['success'] = true;
		}catch (Mailchimp_Error $e){
			$results['success'] = false;
			if ($e->getMessage()) {
				$results['msg'] = $e->getMessage();
			} else {
				$results['msg'] = 'An unknown error occurred';
			}
		}
	}else{
		$results['success'] = false;
		$results['msg'] = 'invalid email';
	}

	return $app->json($results);
});


$app->run();