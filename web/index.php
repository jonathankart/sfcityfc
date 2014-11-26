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

			$content = $e->get('content');

			preg_match('/when:(.*)/i',$content,$when);
			preg_match('/where:(.*)/i',$content,$where);
			preg_match('/description:(.*)/i',$content,$description);
			$when = explode(' to ',$when[1]);
			$start_time = strtotime(trim($when[0]));
			$events[$start_time] = array(
				'title' => $e->get('title'),
				'description'=>trim($description[1]),
				'when' => $start_time,
				'where' => trim($where[1]),
			);
		}
	}
	
	ksort($events);
	return array_values($events);
};


$updates = function () use ($app){

	$posts = array();

	if($app->config->tumblr->enabled){
		$tumblr = new Tumblr\API\Client($app->config->tumblr->api_key, $app->config->tumblr->api_secret);

		$options['limit'] = 3;
		$options['type'] = 'text';
		$posts['text'] = $tumblr->getBlogPosts($app->config->tumblr->blog_name,$options)->posts;

		$options['limit'] = 9;
		$options['type'] = 'photo';
		$posts['photo'] = $tumblr->getBlogPosts($app->config->tumblr->blog_name,$options)->posts;


		foreach($posts['photo'] as &$post){
			foreach($post->photos as &$p){
				$p->url = $p->alt_sizes[count($p->alt_sizes)-1]->url;
			}
		}
	}

	return $posts;
};



$app->get('/', function (Request $request) use ($app,$events,$updates) {
	return $app['twig']->render('home.twig',array(
		'events'=>$events($request->get('nocache')),
		'updates'=> $updates()
	));
});

//$app->get('/tumblr-test', function(Request $request) use ($app){
//	$tumblr = new Tumblr\API\Client($app->config->tumblr->api_key, $app->config->tumblr->api_secret);
//
//	$options['limit'] = 3;
//	$options['type'] = 'text';
//	$posts['text'] = $tumblr->getBlogPosts($app->config->tumblr->blog_name,$options)->posts;
//
//	return '<pre>'.var_export($posts,true).'</pre>';
//
//});

$app->get('/npsl-application', function (Request $request) use ($app,$events) {
	$file = $app->config->documents_dir."sfcityfc-npsl-application-2015.pdf";
	return $app->sendFile($file,200, array('Content-type' => 'application/pdf'), 'attachment');
});

$app->get('/ussf-appeal', function (Request $request) use ($app,$events) {
	$file = $app->config->documents_dir."sfcity-ussf-appeal-2015.pdf";
	return $app->sendFile($file,200, array('Content-type' => 'application/pdf'), 'attachment');
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