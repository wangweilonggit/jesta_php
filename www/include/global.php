<?php
/*
|--------------------------------------------------------------------------
| Web Site Global Server-Side Script
|--------------------------------------------------------------------------
| Define global variables or class/function.
|
*/
$menuindex = 0;
$cpp = 15;	// Count Per Page
/*
|--------------------------------------------------------------------------
| MySQL Configuration
|--------------------------------------------------------------------------
|
*/
$MYSQL['homeURL']			= "http://".$_SERVER["HTTP_HOST"];

/*
|--------------------------------------------------------------------------
| Table Configuration
|--------------------------------------------------------------------------
|
| Define all tables.
|
*/
$TABLE['candidates']			= "candidates";
$TABLE['election']				= "election";
$TABLE['member']				= "member";
$TABLE['premember']				= "premember";
$TABLE['votes']					= "votes";

/*
|--------------------------------------------------------------------------
| Global Timezone Configuration
|--------------------------------------------------------------------------
|
| Define default timezone.
|
*/	
date_default_timezone_set("Asia/Seoul");
/*
|--------------------------------------------------------------------------
| Session Management Class
|--------------------------------------------------------------------------
|
| Define session management class.
|
*/
class sess_info
{
	function sess_info($sessname){ session_start();}
	function checkUser() { 
		return true;
		global $ADMSESS; return ($ADMSESS['user_index'] > 0);
	}
	function checkAdmin() {
		return true;
		global $ADMSESS; return ($ADMSESS['user_index'] <= 1); 
	}
}
/*
|--------------------------------------------------------------------------
| Global Functions Configuration
|--------------------------------------------------------------------------
|
| Define global functions.
|
*/
/* ============================== CheckNumeric ============================== */
function CheckNumeric()
{
	return true;
	foreach($_POST as $field_name => $field_value)
	{
		$field_name = strtolower($field_name);
		if(($field_name == "checkuser")||($field_name == "checkadmin")||($field_name == "checkblogger")||($field_name == "mysess")||($field_name == "adminsess")) return false;
		if((strpos ($field_name, "str")===false) && (!(strpos ($field_name, "idx")===false)))
			if(!is_numeric($field_value) && ($field_value != "")) return false;
	}
	
	foreach($_GET as $field_name => $field_value)
	{
		if(($field_name == "checkuser")||($field_name == "checkadmin")||($field_name == "checkblogger")||($field_name == "mysess")||($field_name == "adminsess")) return false;
		$field_name = strtolower($field_name);
		if((strpos ($field_name, "str")===false) && (!(strpos ($field_name, "idx")===false)))
			if(!is_numeric($field_value) && ($field_value != "")) return false;
	}
	
	return true;
}
/* ============================== GetPostValue ============================== */
function GetPostValue()
{
	$list = null;
	 
	// Building the list
	foreach($_POST as $field_name => $field_value)
		$list .= $field_value;
	
	foreach($_GET as $field_name => $field_value)
	      //$list .= "<strong>{$field_name}</strong>: {$field_value}<br />\r\n";
		$list .= $field_value;
	 
	// Trimming the ends of the list from any unneeded white spaces
	$list = trim($list);
	 
	 $list = strtoupper($list);
	 $list = html_entity_decode($list);
	// Returning the list of variables.
	return $list;
}
/* ============================== CheckSQLInjection ============================== */
function CheckSQLInjection($strIn)
{
	$ret = true;
	
	$patternStrArr  = array('SELECT','DELETE','FROM','INFORMATION','UPDATE','SET','WHERE');
	for($i=0; $i<count($patternStrArr); $i++)
	{
		if(!(strpos ($strIn, $patternStrArr[$i])===false)){
			$ret = false;
			return false;
		}
	}
	return $ret;
}

/*
|--------------------------------------------------------------------------
| Global Function Configuration
|--------------------------------------------------------------------------
|
| Define global variables.
|
*/
function active_menu($index)
{
	global $menuindex;
	$menuindex = $index;
}
function pagination($totalPage, $pageNum, $funcName = 'SwitchPage', $prevStr = '<', $nextStr = '>', $firstStr = '<<', $lastStr = '>>')
{
	if($totalPage <= 0) return;
	
	$out = "";
	$out .= '<ul class="page-navigation">';
	$out .= '<li><a href="javascript:'.$funcName.'(0);">'.$firstStr.'</a></li>';
	$out .= '<li><a href="javascript:'.$funcName.'('.(max($pageNum - 1, 0)).');">'.$prevStr.'</a></li>';	
	$gap = 2;
	$initGap = true;
	for($i=0; $i<=$totalPage; $i++){
		if($pageNum == $i){
			$out .= '<li class="cla-page-$i active"><a href="javascript:'.$funcName.'('.$i.');">'.($i+1).'</a></li>';
			$initGap = true;
		} else if(($i < $gap) || ($i + $gap > $totalPage) || (($i >= $pageNum-$gap) && ($i <= $pageNum+$gap))) {
			$out .= '<li class="cla-page-$i"><a href="javascript:'.$funcName.'('.$i.');">'.($i+1).'</a></li>';
			$initGap = true;
		} else {
			if($initGap){
				$out .= '<li><a class="gap">…</a></li>';
				$initGap = false;
			}
		}
	}
	$out .= '<li><a href="javascript:'.$funcName.'('.(min($pageNum + 1,$totalPage)).');">'.$nextStr.'</a></li>';
	$out .= '<li><a href="javascript:'.$funcName.'('.($totalPage).');">'.$lastStr.'</a></li>';
	$out .= '</ul>';
	return $out;
}

/*
|--------------------------------------------------------------------------
| Global Initialize
|--------------------------------------------------------------------------
|
| Initialize
|
*/
// Base File Directory
$baseFileDir = $_SERVER["SCRIPT_NAME"];

// Base SiteName
$baseDir = substr($baseFileDir, 0, strrpos($baseFileDir, "/"));
//$baseDir = '/stock';
$postStr = GetPostValue();
$defenceCheck = CheckSQLInjection($postStr);
if (!($defenceCheck)) exit("You are a bad man!!!");
$defenceCheck = CheckNumeric();
if (!($defenceCheck)) exit("You are a bad man!!!");
$sess_info = new sess_info("");
if (isset($_SESSION["ADMSESS"])) $ADMSESS = $_SESSION["ADMSESS"];
$checkUser = $sess_info->checkUser();
$checkAdmin = $sess_info->checkAdmin();

?>