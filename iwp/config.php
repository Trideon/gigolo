<?php 
#Show Error
define('APP_SHOW_ERROR', true);

ini_set('display_errors', (APP_SHOW_ERROR) ? 'On' : 'Off');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
if(defined('E_DEPRECATED')) {
error_reporting(error_reporting() & ~E_DEPRECATED);
}
define('SHOW_SQL_ERROR', APP_SHOW_ERROR);

define('APP_VERSION', '2.5.1');
define('APP_INSTALL_HASH', '3c161c45dc9afc1c70a27f578203a92eff44d702');

define('APP_ROOT', dirname(__FILE__));
define('APP_DOMAIN_PATH', 'gmagigolo.com/iwp/');

define('EXECUTE_FILE', 'execute.php');
define('DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT', 180);//Request to client wp

$config = array();
$config['SQL_DATABASE'] = 'gmagigol_iwp822';
$config['SQL_HOST'] = 'localhost';
$config['SQL_USERNAME'] = 'gmagigol_iwp822';
$config['SQL_PASSWORD'] = 'S7K(i18UP(';
$config['SQL_PORT'] = '3306';
$config['SQL_TABLE_NAME_PREFIX'] = 'iwp_';

define('SQL_DRIVER', 'mysqli');

