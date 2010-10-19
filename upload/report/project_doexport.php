<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: project_doexport.php,v 1.13 2009/07/07 15:13:52 alex Exp $
 *
 */
session_start();
ini_set('include_path', ".".PATH_SEPARATOR."include".PATH_SEPARATOR."../PEAR".PATH_SEPARATOR."../include".PATH_SEPARATOR.ini_get('include_path'));
include_once("../include/db.php");
include_once("../include/group_function.php");
include_once("../include/misc.php");
include_once("../include/error.php");
include_once("../include/string_function.php");
include("../include/auth.php");
include("../include/user_function.php");
include("../include/status_function.php");
include("../include/customer_function.php");
include("../include/project_function.php");
include("../include/datetime_function.php");

require_once 'Spreadsheet/Excel/Writer.php';

AuthCheckAndLogin();

if (!isset($_POST['project_id']) || ($_POST['project_id'] == "")) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	include("../include/header.php");
	ErrorPrintOut("miss_parameter", "project_id");
}

// Get project data
$project_sql = "select * from ".$GLOBALS['BR_project_table']." where project_id='".$_POST['project_id']."'";
$project_result = $GLOBALS['connection']->Execute($project_sql) or DBError(__FILE__.":".__LINE__);
$project_line = $project_result->Recordcount();
if ($project_line == 1) {
	$project_name = $project_result->fields["project_name"];
}else{
	WriteSyslog("error", "syslog_not_found", "project", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "project");
}

if (CheckProjectAccessable($_POST['project_id'], $_SESSION[SESSION_PREFIX.'uid']) == FALSE) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	include("../include/header.php");
	ErrorPrintOut("no_such_xxx", "project");
}

// Initial parameters
if (!$_POST['sort_by']) {
	$sort_by = "report_id";
} else {
	if (false === strpos($_GET['sort_by'], ';') && false === strpos($_GET['sort_by'], ' ')) {
		$sort_by = $_GET['sort_by'];
	}
}

if (!$_POST['sort_method']) {
	$sort_method = "DESC";
} else {
	$sort_method = $_POST['sort_method'];
}
if ($sort_method != "DESC") {
	$sort_method = "ASC";
}

$_POST['search_key'] = trim($_POST['search_key']);
if ($_POST['search_key'] == "") {
	unset($_POST['search_key']);
}

if ($_POST['choice_filter'] == "") {
	$_POST['choice_filter'] = 0;
}

for ($i=0; $i<sizeof($show_column_array); $i++) {
	if (isset($_POST[$show_column_array[$i]])) {
		if ($quick_filter != "") {
			$quick_filter .= " and ";
		}
		$quick_filter .= $show_column_array[$i]."='".$_POST[$show_column_array[$i]]."'";
	}
}

$condition = ConditionByFilterSearch($_POST['choice_filter'], $_POST['label'], $_POST['search_key'], $_POST['search_type']);
if ($condition != "") {
	if ($quick_filter != "") {
		$condition = "where (".$condition.") and (".$quick_filter.")";
	} else {
		$condition = "where ".$condition;
	}
} else if ($quick_filter != "") {
	$condition = "where ".$quick_filter;
}

// Get all report
$allsql="SELECT * FROM 
		proj".$_POST['project_id']."_report_table
		$condition ORDER BY $sort_by $sort_method";
$allposts = $GLOBALS['connection']->Execute($allsql) or DBError(__FILE__.":".__LINE__);

$userarray = GetAllUsers(1, 1);
$status_array = GetStatusArray();
$customer_array = GetAllCustomers();

$columns = array();
array_push($columns, "summary");
for ($i = 0; $i < sizeof($show_column_array); $i++) {
	array_push($columns, $show_column_array[$i]);
}

/* Start to output Excel file */
// Creating a workbook
$workbook  = new Spreadsheet_Excel_Writer();

// Allow UTF-8
$workbook->setVersion(8);

// Creating a worksheet
$worksheet =& $workbook->addWorksheet($project_name);
if (PEAR::isError($worksheet)) {
    die($worksheet->getMessage());
}
// Set to UTF-8
$worksheet->setInputEncoding('UTF-8');

// Creating the format
$format_project =& $workbook->addFormat();
$format_project->setBold();
$format_project->setSize(16);

$format_title =& $workbook->addFormat();
$format_title->setBold();
$format_date =& $workbook->addFormat();
$format_date->setNumFormat('YYYY/MM/DD hh:mm:ss');


$worksheet_row = 0;
$worksheet->write($worksheet_row, 0, $project_name, $format_project);

$worksheet_row = 2;
$worksheet_column = 0;
if ($_POST['show_id'] == 'Y') {
	$worksheet->write($worksheet_row, $worksheet_column, $STRING[id], $format_title);
	$worksheet_column++;
}
for ($i = 0; $i < sizeof($columns); $i++) {
	$show_column = "show_".$columns[$i];
	if ($_POST[$show_column] == 'Y') {
		$worksheet->write($worksheet_row, $worksheet_column, $STRING[$columns[$i]], $format_title);
		$worksheet_column++;
	}
}
$worksheet_row++;

while ($row = $allposts->FetchRow()) {
	$worksheet_row++;
	$worksheet_column = 0;
	$status = GetStatusClassByID($status_array, $row['status']);
	if ($_POST['show_id'] == 'Y') {
		$worksheet->write($worksheet_row, $worksheet_column, $row['report_id']);
		$worksheet_column++;
	}
	for ($i = 0; $i < sizeof($columns); $i++) {
		$show_column = "show_".$columns[$i];
		if ($_POST[$show_column] == 'Y') {
			$column_value = $row[$columns[$i]];

			if ($columns[$i] == "summary") {
				$value = $column_value;
				$value = str_replace("&lt;", '<', $value);
				$value = str_replace("&gt;", '>', $value);
				$value = str_replace("&quot;", '"', $value);
				$value = str_replace("&amp;", '&', $value);
			} elseif ($columns[$i] == "priority") {
				$value = $STRING[$GLOBALS['priority_array'][$column_value]];
			} elseif ($columns[$i] == "type") {
				$value = $STRING[$GLOBALS['type_array'][$column_value]];
			} elseif ($columns[$i] == "status") {
				$status = GetStatusClassByID($status_array, $column_value);
				if ($status) {
					$value = $status->getstatusname();
				} else {
					$value = "";
				}
				
			} elseif ($columns[$i] == "reported_by_customer") {
				$value = GetCustomerNameFromID($customer_array, $column_value);
			} elseif (($columns[$i] == "reported_by") || ($columns[$i] == "assign_to") || 
					  ($columns[$i] == "fixed_by") || ($columns[$i] == "verified_by") ) {

				$value = UidToUsername($userarray, $column_value);
			} elseif (($columns[$i] == "created_date") || ($columns[$i] == "fixed_date") ||
					  ($columns[$i] == "verified_date")|| ($columns[$i] == "estimated_time")) {

				if ($column_value != "") {
					$value = $allposts->UserTimeStamp($column_value, "U"); // seconds since January 1 1970 00:00:00 GMT
					$value = $value + $allposts->UserTimeStamp($column_value, "Z"); // Add timezone
					// Calculate the number of days since December 30 1899 (Excel's day zero)
					$value = ($value/86400) + 25569; // 25569 is Jan. 1, 1970
				}
				$format = $format_date;
			} else {
				$value = $column_value;
			}
			if (isset($format)) {
				$worksheet->write($worksheet_row, $worksheet_column, $value, $format);
			} else {
				$worksheet->write($worksheet_row, $worksheet_column, $value);
			}
			unset($format);
			$worksheet_column++;
		} /* end of show column */
	} /* for each column */
}// end of for each report
// Let's send the file

// Send HTML header
$workbook->send('export.xls');

// Send data
$workbook->close();

?>
