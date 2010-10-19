<?php
/* Copyright (c) 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: feedback_report_doupdate.php,v 1.11 2008/11/28 10:35:34 alex Exp $
 *
 */
include("../include/header.php");
include("../include/user_function.php");
include("../include/project_function.php");
include("../include/customer_function.php");
include("../include/feedback_email_function.php");

AuthCheckAndLogin();

if (!($GLOBALS['Privilege'] & $GLOBALS['can_admin_feedback'])) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_privilege");
}

if (!isset($_POST['project_id']) || ($_POST['project_id'] == "")) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "project_id");
}

if (!isset($_POST['report_id']) || ($_POST['report_id'] == "")) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "report_id");
}

$return_page = "feedback_report_update.php?project_id=".$_POST['project_id']."&report_id=".$_POST['report_id'];

if (CheckProjectAccessable($_POST['project_id'], $_SESSION[SESSION_PREFIX.'uid']) == FALSE) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "project");
}

if (!trim($_POST['summary'])) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "no_empty", "summary");
}

if (!$_POST['version']) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "no_empty", "version");
}
	
if (utf8_strlen($_POST['version']) > 40) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "too_long", "version", "40");
}

$project_sql = "select * from ".$GLOBALS['BR_project_table']." where project_id='".$_POST['project_id']."'";
$project_result = $GLOBALS['connection']->Execute($project_sql) or DBError(__FILE__.":".__LINE__);
$line = $project_result->Recordcount();
if ($line != 1) {
	ErrorPrintOut("no_such_xxx", "project");
}
$project_name = $project_result->fields["project_name"];

// 去除html的標籤,讓標籤在網頁中無作用
$_POST['type'] = htmlspecialchars($_POST['type']);
$_POST['version'] = htmlspecialchars($_POST['version']);
$_POST['minor_area'] = htmlspecialchars($_POST['minor_area']);
$_POST['summary'] = htmlspecialchars($_POST['summary']);

$log = "<p>Set Priority:".$STRING[$GLOBALS['priority_array'][$_POST['priority']]].",";
$log .= "Status: ".$GLOBALS['feedback_status'][$_POST['status']];
$log = $log."</p><p>Description:</p><p>".$_POST['description']."</p>";

// 上傳附加檔案資料
if(!$_FILES['file']['tmp_name']) {
	$filename = "";
} else {
	if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
		ErrorPrintBackFormOut("GET", $return_page, $_POST, 
							  "wrong_format", "file_upload");
	}

	$org_filename = $_FILES['file']['name'];
	if (utf8_strlen($org_filename) > 252) { /* 100_filename  256-strlen("100_")=252 */
		$subname = strrchr($org_filename, ".");
		if (utf8_strlen($subname) > 251) {
			$filename = utf8_substr($org_filename, 0, 251);
		} else {
			$filename = utf8_substr($org_filename, 0, (251 - utf8_strlen($subname)) ).$subname;
		}
	} else {
		$filename = $org_filename;
	}
		
	$filedata = $GLOBALS['connection']->BlobEncode(fread(fopen($_FILES['file']['tmp_name'], "r"), $_FILES['file']['size']));
}
   
$update_report_sql = "update proj".$_POST['project_id']."_feedback_table set
		summary='".$_POST['summary']."', type='".$_POST['type']."', 
		status='".$_POST['status']."', priority='".$_POST['priority']."', 
		version='".$_POST['version']."', reproducibility='".$_POST['reproducibility']."'
		where report_id='".$_POST['report_id']."'";

$now = $GLOBALS['connection']->DBTimeStamp(time());
$new_log_sql = "insert into proj".$_POST['project_id']."_feedback_content_table (
        report_id, internal_user_id, post_time, description, filename, filedata) values(
		'".$_POST['report_id']."', '".$_SESSION[SESSION_PREFIX.'uid']."', $now, '$log', '$filename', '$filedata');";

$GLOBALS['connection']->StartTrans();
$GLOBALS['connection']->Execute($update_report_sql) or DBError(__FILE__.":".__LINE__);
$GLOBALS['connection']->Execute($new_log_sql) or DBError(__FILE__.":".__LINE__);
$GLOBALS['connection']->CompleteTrans();

SendFeedbackReportEmail($_POST['project_id'], $_POST['report_id'], $_SESSION[SESSION_PREFIX.'uid']);
FinishPrintOut("feedback_list.php?project_id=".$_POST['project_id'], "finish_update", "report");

?>
