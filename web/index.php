<?php

require_once __DIR__.'/../vendor/autoload.php';

if(stripos(__DIR__,'/Users/jonathankart')!==false){
	define('SFCITY_ENV','dev');
}else{
	define('SFCITY_ENV','prod');
}

require_once __DIR__."/conf/".SFCITY_ENV."/db.php";

$app = new Silex\Application();
use Symfony\Component\Validator\Constraints as Assert;

$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
	'db.options' => $sfcityconfig['db']
));


$schema = $app['db']->getSchemaManager();
//var_dump($schema);
if(!$schema->tablesExist('subscriptions')){
	$subscriptions = new Doctrine\DBAL\Schema\Table('subscriptions');
	$subscriptions->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
	$subscriptions->setPrimaryKey(array('id'));
	$subscriptions->addColumn('create_time','integer',array('unsigned'=>true));
	$subscriptions->addColumn('modify_time','integer',array('unsigned'=>true));
	$subscriptions->addColumn('email', 'string', array('length' => 255));
	$subscriptions->addUniqueIndex(array('email'));
	$subscriptions->addColumn('name', 'string', array('length' => 255));
	$subscriptions->addColumn('phone', 'string', array('length' => 16));

	$schema->createTable($subscriptions);
}
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
	'twig.debug'	=>	true
));


$app->get('/', function () use ($app) {
		return $app['twig']->render('home.twig');
});


use Symfony\Component\HttpFoundation\Request;

$app->post('/subscribe',function (Request $request) use ($app){
	/** @var Doctrine\DBAL\Query\QueryBuilder $sql */
	$email = $request->get('email');
	$results = array();

	if($email && count($app['validator']->validateValue($email, new Assert\Email()))===0){
		$existing_id = $app['db']->fetchColumn('SELECT id FROM subscriptions WHERE email = ?', array($email), 0);
		if(!$existing_id){
			$app['db']->insert('subscriptions',array(
				'create_time'=>time(),
				'modify_time'=>time(),
				'email'=>$email,
				'name'=>$request->get('name'),
				'phone'=>$request->get('phone'),
			));
			$results['success'] = true;
		}else{

			$update['modify_time'] = time();
			if($name = $request->get('name')){
				$update['name'] = $name;
			}
			if($phone = $request->get('phone')){
				$update['phone'] = $phone;
			}
			$app['db']->update('subscriptions',$update,array('id'=>$existing_id));
			$results['success'] = true;
			$results['msg'] = "already exists";
		}
	}else{
		$results['success'] = false;
		$results['msg'] = 'invalid email';
	}

	return $app->json($results);

});

$app->run();