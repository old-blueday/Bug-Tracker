<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: area_js.php,v 1.6 2008/11/28 10:36:49 alex Exp $
 *
 */

// 檔案說明：這個檔案是用來寫出讓使用者在輸入或修改 report 時
//	     改變 Area 下拉選單時就自動改變 Minor 下拉選單的 JAVA Script
//           在要使用 Area 及 Minor Area 的 form 中，請設定 form name="form1"
//           另外還要設 Area 的下拉選單 <select size="1" name="area" onChange="change()">，
//           Minor Area 的下拉選單 <select size="1" name="minor_area">
//           一開始請自行輸入所有 Area 的資料，及第一個 Area 的所有 Minor Area 的資料
//           order by area_id
if (!$_GET['project_id']) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "project_id");
}
if (CheckProjectAccessable($_GET['project_id'], $_SESSION[SESSION_PREFIX.'uid']) == FALSE) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "project");
}
$area_assign_array=array();
$minor_area_assign_array=array();

// 取得 Area 的資料
$JS_all_area = "select * from ".$GLOBALS['BR_proj_area_table']." where project_id='".$_GET['project_id']."' and area_parent=0 order by area_name";
$JS_root_area_result = $GLOBALS['connection']->Execute($JS_all_area) or DBError(__FILE__.":".__LINE__);
$JS_area_line = $JS_root_area_result->Recordcount();
echo '<script language="JavaScript" type="text/javascript">';
echo "var ArrayBlank=new Array(\"\");";
$i = 0;
while ($row = $JS_root_area_result->FetchRow()) {
   $JS_area_id = $row["area_id"];
   $JS_area_name = $row["area_name"];
   $JS_owner = $row["owner"];
   $JS_minor_area = "select * from ".$GLOBALS['BR_proj_area_table']." where project_id='".$_GET['project_id']."' and area_parent='$JS_area_id' order by area_name";
   $JS_minor_result = $GLOBALS['connection']->Execute($JS_minor_area) or DBError(__FILE__.":".__LINE__);
   $JS_minor_line = $JS_minor_result->Recordcount();
   
   if ($JS_owner =="") {$JS_owner=0;}
   array_push($area_assign_array, $JS_owner);
   
   echo "var Array".$i." = new Array(";
   $count_minor=0;
   while($JS_minor_row = $JS_minor_result->FetchRow()){
      $JS_minor_name = $JS_minor_row["area_name"];
      $JS_minor_owner = $JS_minor_row["owner"];
      
      if ($JS_minor_owner == "") {$JS_minor_owner = 0;}
      array_push($minor_area_assign_array, $JS_minor_owner);
      
      echo "\"".$JS_minor_name."\"";
      $count_minor++;
      if ($count_minor!=$JS_minor_line) { echo ",";}
   }
   if ($count_minor==0) { echo "\"\"";}
   echo ") ;";
   $i++;
}
echo "var ArrayAll = new Array(ArrayBlank";
if ($JS_area_line != 0) { echo ",";}
for ($i=0; $i<$JS_area_line; $i++) {
   echo "Array".$i;
   if ($i<$JS_area_line-1) {echo ",";}
}
echo ") ;";
?>


function area_assign() {
	var doc=document.form1;
	var selArea=doc.area.options[doc.area.selectedIndex].text;
	var selMinorArea=doc.minor_area.options[doc.minor_area.selectedIndex].text;
	var assign_to=0;

<?php
$JS_root_area_result->MoveFirst();
while ($row = $JS_root_area_result->FetchRow()) {
	$JS_area_name = $row["area_name"];
	$JS_area_owner = $row["owner"];
	if (trim($JS_area_owner)=="") {$JS_area_owner = 0;}
		
	echo "if (selArea == '$JS_area_name') { assign_to = $JS_area_owner;}";
}
?>
	if (!doc.orig_assign_to || (doc.orig_assign_to.value == -1)) {
		for (var i=0;i<doc.assign_to.length;i++) {
			if (doc.assign_to.options[i].value==assign_to){
				doc.assign_to.options[i].selected=true;
				break;
			}
		}
	}
<?php
$JS_get_minor_area = "select * from ".$GLOBALS['BR_proj_area_table']." where project_id='".$_GET['project_id']."' and area_parent!=0";
$JS_minor_area_result = $GLOBALS['connection']->Execute($JS_get_minor_area) or DBError(__FILE__.":".__LINE__);
$JS_minor_area_line = $JS_minor_area_result->Recordcount();

while ($row = $JS_minor_area_result->FetchRow()) {
	$JS_minor_area_name = $row["area_name"];
	$JS_minor_area_owner = $row["owner"];
	if (trim($JS_minor_area_owner)=="") {$JS_minor_area_owner=0;}
   		
	echo "if (selMinorArea == '$JS_minor_area_name'){ assign_to=$JS_minor_area_owner; }";
}	
?>
	if (!doc.orig_assign_to || (doc.orig_assign_to.value == -1)) {
		for (var i=0;i<doc.assign_to.length;i++) {
			if ((doc.assign_to.options[i].value==assign_to)&&(assign_to!=0)){
				doc.assign_to.options[i].selected=true;
				break;
			}
		}
	}
}

function change() {

	document.form1.minor_area.options.length = ArrayAll[document.form1.area.selectedIndex].length ;

	for(var i=0 ; i< ArrayAll[document.form1.area.selectedIndex].length ; i++) {
		document.form1.minor_area.options[i].text = ArrayAll[document.form1.area.selectedIndex][i] ;
	}
    area_assign();
}

</script>


