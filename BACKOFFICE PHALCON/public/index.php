<?php
if (($_SERVER['REMOTE_ADDR'] != '109.190.131.99') && ($_SERVER['REMOTE_ADDR'] != '90.105.201.0'))
{
	//echo "Remise en place du front-office en cours depuis 18h30 (+ premiers écrans de l'espace client). SMS comme prévu dès mise à disposition.";
	//echo "Bonjour Nicolas, j'espère que vous avez pu voir les modifications de la veille, je recoupe 2h ou 3h ce jour pour mettre quelques nouveautés de plus et surtout un nouveau système pour les mises à jour. Après, elles prendront 2 minutes maxi, au lieu de plusieurs heures ! :-) Cordialement. David.";
	//mail('david@warmbee.com', 'HB '.$_SERVER['REMOTE_ADDR'], date('H:i:s', time()));
	//header("Location: http://admin.home-busters.com");
	//exit();
}
/**
 * Author: Allemandou David
 * Company: Warmbee (Deliv's SARL)
 * Date: 2015
 * Desc: Autoloader
 */
error_reporting(E_ALL);

//Allowing empty strings
\Phalcon\Mvc\Model::setup(array(    
    'notNullValidations' => false
));

try {
	/**
	 * Read the configuration from an external file
	 */
	require __DIR__.'/../app/config/config.php';
	require __DIR__.'/../app/config/init.php';

	$loader = new \Phalcon\Loader();

	/**
	 * We're a registering a set of directories taken from the configuration file
	 */
	$loader->registerDirs(
		array(
			__DIR__.$config->phalcon->controllersDir,
			__DIR__.$config->phalcon->libraryDir,
			__DIR__.$config->phalcon->modelsDir
		)
	)->register();

	/**
	 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
	 */
	$di = new \Phalcon\DI\FactoryDefault();

	/**
	 * Share init
	 */
	$di->setShared('init', $init);
	
	/**
	 * Load router from external file
	 */
	$di->set('router', function(){
		require __DIR__.'/../app/config/routes.php';
		return $router;
	});

	/**
	 * The URL component is used to generate all kind of urls in the application
	 */
	$di->set('url', function() use ($config){
		$url = new \Phalcon\Mvc\Url();
		$url->setBaseUri($config->phalcon->baseUri);
		return $url;
	});

	/**
	 * Setup the view service
	 */
	$di->set('view', function() use ($config) {
		$view = new \Phalcon\Mvc\View();
		$view->setViewsDir(__DIR__.$config->phalcon->viewsDir);
		return $view;
	});

	//Set the views cache service
	/*$di->set('viewCache', function(){

		//Cache data for one day by default
		$frontCache = new Phalcon\Cache\Frontend\Output(array(
			"lifetime" => 2592000
		));

		//File backend settings
		$cache = new Phalcon\Cache\Backend\File($frontCache, array(
			"cacheDir" => __DIR__."/../app/cache/",
			"prefix" => "php"
		));

		return $cache;
	});*/
	
	$di->set('modelsMetadata', function() {
		$metaData = new \Phalcon\Mvc\Model\MetaData\Files(
		array(
			"lifetime" => 86400,
			"metaDataDir" => __DIR__."/../app/cache/metadata/" 
			)
		);
		return $metaData;
		});

	/**
	 * Database connection is created based in the parameters defined in the configuration file
	 */
	$di->set('db', function() use ($config) {
		return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
			"host" => $config->database->host,
			"username" => $config->database->username,
			"password" => $config->database->password,
			"dbname" => $config->database->dbname,
			"options" => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
		));
	});

	/**
	 * Start the session the first time some component request the session service
	 */
	$di->set('session', function(){
		$session = new Phalcon\Session\Adapter\Files();
		$session->start();
		return $session;
	});

	/**
	 * Register the flash service with custom CSS classes
	 */
	$di->set('flash', function(){
		$flash = new Phalcon\Flash\Direct(array(
			'error' => 'alert alert-error',
			'success' => 'alert alert-success',
			'notice' => 'alert alert-info',
		));
		return $flash;
	});
	
	$application = new \Phalcon\Mvc\Application();
	$application->setDI($di);
	echo $application->handle()->getContent();

} catch (Phalcon\Exception $e) {
	echo $e->getMessage();
} catch (PDOException $e){
	echo $e->getMessage();
}
