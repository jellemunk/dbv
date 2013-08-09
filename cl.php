<?php

/**
*    Call this file from the command line to jump to specific revision.
*    Specify 'last' as its first argument to jump to the last revision. 
*    You can optionally specify the current commit, example:
*    $ php cl.php last 53f03e596f2ce6517d4c91e4fa379e6bbf37ca4c
*
*    Or specify 'rev' as a first argument and a specific revision as the second, example:
*    $ php cl.php rev 4 
*
*    Or specify 'commit' as the first argument and a commit as the second to jump to the associated revision.
*    This only works if you have the db log enabled, example:
*    $ php cl.php commit 53f03e596f2ce6517d4c91e4fa379e6bbf37ca4c
*/

$_GET['a'] = 'jumpto';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_GET['project'] = $argv[1];

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.nooku.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/functions.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DBV.php';

$dbv = DBV::instance();
if(!file_exists(DBV_ROOT_PATH . DS . DBV_BEFORE_PROJECT_PATH . $_GET['project'] . DS . DBV_AFTER_PROJECT_PATH . 'configuration.php')){
	die("No valid project name\n");
}

if($argv[2] === 'last'){
	$_POST['revision'] = $dbv->findLastRevision();
	$_POST['commit'] = $argv[3];
}elseif($argv[2] === 'rev' && $argv[3]){
	$_POST['revision'] = $argv[3];
}elseif($argv[2] === 'commit' && $argv[3]){
	$rev = $dbv->findRevisionFromCommit($argv[3]);
    if($rev){
    	$_POST['revision'] = $rev;
    }else{
    	die("Could not find revision\n");
    }
}else{
	die("No valid arguments found.  Please use 'last', 'rev' or 'commit'\n");
}

$dbv->authenticate();
$dbv->dispatch();