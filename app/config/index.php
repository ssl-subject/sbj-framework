<?php

return [
	'projectName' => 'SBJ Platform',
	'appDir' => __DIR__ .'/../',
	'controllerDir' => 'controller',
	'controllerPrefix' => '',
	'controllerSuffix' => 'Controller',
	'baseURL' => '',
	'defaultController' => 'Index',
	'defaultAction' => 'index',
	'widgetPrefix' => '',
	'widgetSuffix' => 'Widget',
	'modelDir' => 'model',
	'modelPrefix'=> '',
	'modelSuffix'=> '',
	'viewDir' => 'view',
	'runtimeDir' => 'runtime',
	'db' => include_once __DIR__ . '/db.php',
	'error_404' => 'Index/notFoundError',
	'error'=>'Base/errorHandler',
	'assets' => 'public',
	'commonDir' => 'common',
	'defaultLang' => 'en'
];
