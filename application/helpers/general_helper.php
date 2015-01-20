<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @desc        General Helper.
 * @author      Zhongge Han for Second Opinion
 * @copyright   2014
 * @version     1.0
 */
 
	 
	// Get SQL Result
	function get_sql_result($sql)
	{
	    $CI =& get_instance(); 
		$query = $CI->db->query($sql);
		return $query->result();
	}
	function get_sql_query($sql)
	{
	    $CI =& get_instance(); 
		$query = $CI->db->query($sql);
	}
	function get_sql_value($sql)
	{
	    $CI =& get_instance(); 
		$query = $CI->db->query($sql);
		$result =  $query->result();
		if(count($result) <= 0) return false;
		return $result[0];
	}

	function tolog($data)
	{		
		$data = "[JESTA][IP:".$_SERVER['REMOTE_ADDR']."]".$data;
		log_message('error', $data);
	}

	function json_capsule($arr)
	{
		$data = json_encode($arr);
		$json_result = "{\"result\":[";
		$json_result .= $data;
		$json_result .= "]}";
		//tolog($json_result);
		
		return $json_result;
	}
	
	function user_request() 
	{
		tolog(json_encode($_REQUEST));
		$request = array();
		foreach($_REQUEST as $field_name => $field_value)
			$request[ $field_name ] = mysql_real_escape_string($field_value);
		
		if ( isset($request["user_id"]) ) 
		{
			$sql = "UPDATE users SET last_online=CURRENT_TIMESTAMP WHERE userid='{$request['user_id']}'";
			get_sql_query($sql);
		}
		
		return $request;
	}
	
	function IsNullOrEmptyString($question){
	    return (!isset($question) || trim($question)==='');
	}
	
	function convertToTime($m)  {
		if($m > 0 && $m < 60)
		{
			return $m . " minutes ago";
		}
		else if($m >= 60 && $m < (60 * 24))
		{
			return round($m / 60) . " hours ago";
		}
		else if($m >= (60 * 24) && $m < (60 * 24 * 365))
		{
			return round($m / (60 * 24)) . " days ago";
		}
		return "just now";
	}
?>