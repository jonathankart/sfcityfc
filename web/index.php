<?php

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__."/../conf/conf.php";
require_once __DIR__."/../conf/pw.php";

require_once __DIR__."/../vendor/coreylib/coreylib.php";

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app->config = $SFCITY_CONFIG;
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
	'twig.debug'	=>	true
));


$events = function ($nocache=false) use ($app){
	$api = new clApi(
		$app->config->calendar->feed,
		new clFileCache($app->config->cache_dir));
	$events = array();

	if ($feed = $api->parse($nocache ? -1 : $app->config->calendar->cache_time)) {
		foreach($feed->get('entry') as $e){
			$start_time = $e->get('when@startTime');
			$events[strtotime($start_time)] = array(
				'title' => $e->get('title'),
				'description'=>$e->get('content'),
				'when' => $start_time,
				'where' => $e->get('where@valueString'),
			);
		}
	}

	ksort($events);
	return array_values($events);
};

$app->get('/', function (Request $request) use ($app,$events) {



	return $app['twig']->render('home.twig',array('events'=>$events($request->get('nocache'))));
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
			$mc->lists->subscribe($app->config->mailchimp->lists->newsletter, array('email'=>$email),array('name'=>$name,'phone'=>$phone),'html',false,true,true,false);
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