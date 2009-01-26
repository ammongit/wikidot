<?php

chdir(dirname(__FILE__));
require_once('../php/setup.php');

// map errors to exceptions
function errorHandler($errno, $errstr, $errfile, $errline) {
	if (error_reporting()) {
		throw new Exception($errstr); // internal error not to be mapped to fault
	}
	return true;
}
error_reporting(E_ALL & ~E_NOTICE);
set_error_handler('errorHandler', E_ALL & ~E_NOTICE);

$user = null;

if (isset($_SERVER['PHP_AUTH_USER'])) {
	$app = $_SERVER['PHP_AUTH_USER'];
	$key = $_SERVER['PHP_AUTH_PW'];
	$user = DB_ApiKeyPeer::instance()->getUserByKey($key);
}

if (! $user) {
	header('HTTP/1.1 401 Unauthorized');
	header('Content-type: text/plain');
    header('WWW-Authenticate: Basic realm="Wikidot API. Please support application name (as user) and API key (as password)."');
    header('HTTP/1.0 401 Unauthorized');
	echo 'Login failed';
    exit();
}

// construct facade objects
$server = new Zend_XmlRpc_Server();
$server->setClass(new Wikidot_Facade_Site($user, $app), 'site');
$server->setClass(new Wikidot_Facade_Page($user, $app), 'page');
$server->setClass(new Wikidot_Facade_Forum($user, $app), 'forum');
$server->setClass(new Wikidot_Facade_User($user, $app), 'user');

// map Wikidot_Facade_Exception to XML-RPC faults
Zend_XmlRpc_Server_Fault::attachFaultException('Wikidot_Facade_Exception');
Zend_XmlRpc_Server_Fault::attachFaultException('WDPermissionException');

// run XML-RPC server
header("Content-type: text/xml");
echo $server->handle();