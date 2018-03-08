<?php

/*if(!isset($_COOKIE['subject'])) {
	require_once 'index.html';
	exit;
}*/

session_start();

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require_once __DIR__ . '/framework/SBJ.class.php';


if(isset($_COOKIE['switch'])) {

	define('IS_MOBILE', ($_COOKIE['switch'] == 'mobile' ? true : false) );
}
else if(SBJ::isMobile()) define('IS_MOBILE', true);
else define('IS_MOBILE', false);

SBJ::start(__DIR__ . '/app/config/index.php');

?>