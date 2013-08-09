<?php

$projectname = ($_POST['project'])? $_POST['project'] : $_GET['project'];

/**
 * Only edit this lines if you want to place your schema files in custom locations
 * @see http://dbv.vizuina.com/documentation/#optional-settings
 */
define('DBV_DATA_PATH', DBV_ROOT_PATH . DS . DBV_BEFORE_PROJECT_PATH . $projectname . DS . DBV_AFTER_PROJECT_PATH . 'data');
define('DBV_SCHEMA_PATH', DBV_DATA_PATH . DS . 'schema');
define('DBV_REVISIONS_PATH', DBV_DATA_PATH . DS . 'revisions');
define('DBV_META_PATH', DBV_DATA_PATH . DS . 'meta');

if(file_exists(DBV_ROOT_PATH . DS . DBV_BEFORE_PROJECT_PATH . $projectname . DS . DBV_AFTER_PROJECT_PATH . 'configuration.php')){
	
	require_once DBV_ROOT_PATH . DS . DBV_BEFORE_PROJECT_PATH . $projectname . DS . DBV_AFTER_PROJECT_PATH . 'configuration.php';
	$config = new JConfig();

	/**
	 * Your database authentication information goes here
	 * @see http://dbv.vizuina.com/documentation/
	 */
	define('DB_HOST', $config->host);
	define('DB_PORT', 3306);
	define('DB_USERNAME', $config->user);
	define('DB_PASSWORD', $config->password);
	define('DB_NAME', $config->db);
}