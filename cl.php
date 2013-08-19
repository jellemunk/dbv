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
if(!file_exists(DBV_ROOT_PATH . DS . DBV_BEFORE_PROJECT_PATH . $_GET['project'] . DS . DBV_AFTER_PROJECT_PATH . 'configuration.php') && !file_exists($_GET['project'] . DS . 'configuration.php') ){
	die("No valid project name\n");
}

if($argv[2] === 'last'){
	$final_revision = $dbv->findLastRevision();
	$commit = $argv[3];
}elseif($argv[2] === 'rev' && $argv[3]){
	$final_revision = $argv[3];
}elseif($argv[2] === 'commit' && $argv[3]){
	$rev = $dbv->findRevisionFromCommit($argv[3]);
    if($rev){
    	$final_revision = $rev;
    }else{
    	$final_revision = $dbv->findLastRevision();
		$commit = $argv[3];
    }
}else{
	die("No valid arguments found.  Please use 'last', 'rev' or 'commit'\n");
}


    
    $current_revision = $dbv->_getCurrentRevision();
    $all_revisions = $dbv->_getRevisions();
    $revisions = array();
    foreach($all_revisions as $revision){
    	if(($revision > $current_revision && $revision <= $final_revision) || ($revision <= $current_revision && $revision > $final_revision) ){
    		array_push($revisions, $revision);
    	}
    }
    if(count($revisions) === 0){
    	die('Database is uptodate at revision:' . $current_revision . '. No new revisions to execute.');
    }    
    if($current_revision > $final_revision){
        $rollback = true;
        rsort($revisions, SORT_NUMERIC);
    }else{
        $rollback = false;
        sort($revisions, SORT_NUMERIC);
    }

    foreach($revisions as $revision){
        
        if (count($files)) {
        	$error = false;
            if($rollback){
            	$files = $dbv->_getRevisionRollbackFiles($revision);
                $path = DBV_REVISIONS_PATH . DS . $revision . DS . 'rollback';
                $type = 'executing rollback of';
            }else{
            	$files = $dbv->_getRevisionFiles($revision);
            	$path = DBV_REVISIONS_PATH . DS . $revision;
            	$type = 'executing';
            }
        	
        	//execute files
            foreach ($files as $file) {
                $file = $path . DS . $file;
                if (!$dbv->_runFile($file)) {
                    $dbv->error('Error ' . $type . ' revision:' . $revision . ' in file' . $file);
                	$error = true;
                }
            }
            if(!$error){
            	$dbv->confirm('Succes ' .  $type . ' revision:' . $revision);
            }
        }
    }    
    
    $dbv->_setCurrentRevision($final_revision);
    $return = array(
            'messages' => array(),
            'revision' => $dbv->_getCurrentRevision()
    );
        
    foreach ($dbv->_log as $message) {
        $return['messages'][$message['type']][] = $message['message'];
    }
    $dbv->_json($return);
