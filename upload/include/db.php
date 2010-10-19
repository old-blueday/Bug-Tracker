<?php
/* Copyright 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: db.php,v 1.20 2010/07/27 09:24:17 alex Exp $
 *
 */
ini_set('include_path', ".".PATH_SEPARATOR."include".PATH_SEPARATOR."../include".PATH_SEPARATOR.ini_get('include_path'));
include("config.php");
include($GLOBALS["SYS_PROJECT_PATH"]."/adodb/adodb.inc.php"); 

/* connect to database */
$ADODB_FETCH_MODE = ADODB_FETCH_BOTH;

$GLOBALS['connection'] = &ADONewConnection($GLOBALS['BR_dbtype']);
$GLOBALS['connection']->debug = $GLOBALS['BR_dbdebug'];
$GLOBALS['connection']->Connect($GLOBALS['BR_dbserver'], $GLOBALS['BR_dbuser'], $GLOBALS['BR_dbpwd'], $GLOBALS['BR_dbname']) or die($GLOBALS['connection']->ErrorMsg());

if (strstr($GLOBALS['BR_dbtype'], "mysql")) {
	$GLOBALS['connection']->Execute("SET CHARACTER SET utf8");
}

$system_sql = "select * from ".$GLOBALS['BR_sysconf_table'];
$system_result = $GLOBALS['connection']->Execute($system_sql) or die(__FILE__.":".__LINE__.":".$GLOBALS['connection']->ErrorMsg());
if (!$system_result) {
    print $GLOBALS['connection']->ErrorMsg();
	die("Failed to get system configuration.");
}
$SYSTEM = $system_result->FetchRow();
if (!preg_match("/^[_\.0-9A-Za-z-]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,4}$/", $SYSTEM['mail_from_email'])) {
	$SYSTEM['mail_from_email'] = "root@".$_SERVER['SERVER_NAME'];
}

$magic_quotes_gpc = ini_get('magic_quotes_gpc');
if (!$magic_quotes_gpc) {
	foreach ($_GET as $key => $value) {
		$_GET[$key] = addslashes($value);
	}
	foreach ($_POST as $key => $value) {
		$_POST[$key] = addslashes($value);
	}
}
?>
