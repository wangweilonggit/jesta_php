<?php
// model for user manage
class user extends CI_Model {
	function __construct()
    {
	    parent::__construct();
    }
	function is_existuserByEmail($email)
	{
		$sql = "SELECT * FROM " . TABLE_USERS . " WHERE email='{$email}' LIMIT 1";
		return get_sql_value($sql);
	}
	
	function is_existuserByName($username)
	{
		$sql = "SELECT * FROM " . TABLE_USERS . " WHERE username='{$username}' LIMIT 1";
		return get_sql_value($sql);
	}
	
	function is_existuserById($userid)
	{
		$sql = "SELECT * FROM " . TABLE_USERS . " WHERE userid='{$userid}' LIMIT 1";
		return get_sql_value($sql);
	}
	
	function register_user($username, $password, $email, $language, $country, $description, $photo, $paypal_id) 
	{
		$sql = "INSERT INTO " . TABLE_USERS . " (username, password, email, image, description, language, country, paypal_id) VALUES ( '{$username}', '{$password}', '{$email}', '{$photo}', '{$description}', '{$language}', '{$country}', '{$paypal_id}' )";
		get_sql_query($sql);
	}
	
	function update_user($userid, $language, $country, $description, $photo, $paypal_id)
	{
		
		if ( IsNullOrEmptyString($photo) ) 
		{
			$photo_update = "";
		}
		else
		{
			$photo_update = ", image='{$photo}'";
			
		}
		$sql = "UPDATE " . TABLE_USERS . " SET language='{$language}', country='{$country}', description='{$description}',
				paypal_id='{$paypal_id}' {$photo_update} WHERE userid='{$userid}'";
		get_sql_query($sql);
	}
	
	function change_password($userid, $new_password)
	{
		$sql = "UPDATE " . TABLE_USERS . " SET password='{$new_password}'  WHERE userid='{$userid}'";
		get_sql_query($sql);
	}
	
	function register_push_token($user_id, $device_token, $device_type) 
	{
		$sql = "UPDATE " . TABLE_USERS . " SET token='{$device_token}', device='{$device_type}' WHERE userid='{$user_id}'";
		get_sql_query($sql);
	}
	
	function add_marked_category_by_user($user_id, $catids) 
	{
		$sql = "UPDATE " . TABLE_USERS . " SET saved_cat='{$catids}' WHERE userid='{$user_id}'";
		get_sql_query($sql);
	}
	
	function get_user_profile($user_id) 
	{
		$sql = "SELECT * ,".
			 	"(SELECT COUNT(*) FROM " . TABLE_ANSWER . " WHERE sender= '{$user_id}' AND cost = 0 AND rating > 0 ) as free_up ,".
				"(SELECT COUNT(*) FROM " . TABLE_ANSWER . " WHERE sender= '{$user_id}' AND cost = 0 AND rating < 0 ) as free_down ,".
				"(SELECT COUNT(*) FROM " . TABLE_ANSWER . " WHERE sender= '{$user_id}' AND cost > 0 AND rating > 0 ) as pay_up ,".
				"(SELECT COUNT(*) FROM " . TABLE_ANSWER . " WHERE sender= '{$user_id}' AND cost > 0 AND rating < 0 ) as pay_down ".
				" FROM " . TABLE_USERS . " WHERE userid='{$user_id}' LIMIT 1";
		return get_sql_value($sql);
	}
	
	function increase_fund($user_id, $amount) 
	{
		$sql = "UPDATE " . TABLE_USERS . " SET fund=fund+({$amount}) WHERE userid='{$user_id}' ";
		get_sql_query($sql);
	}
	
	function send_gcm($android_ary, $message, $type, $data=array())
	{
		if(count($android_ary) == 0) return 0;
/*------------Send 	to GCM Server-----------*/	
		$headers = array(
		'Content-Type: application/json',
		'Authorization: key='.GCM_API_KEY
		);
		$arr = array();
		$arr['data'] = $data;
		$arr['data']['type'] = $type;
		$arr['data']['message'] = $message;
		$arr['registration_ids'] = $android_ary;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($arr));
		$response = curl_exec($ch);
		tolog($response);
		$json_result = json_decode($response);
		curl_close($ch);
		return $json_result ? $json_result->failure : -1;
	}
	
	function send_apns($iphone_ary, $message, $type, $data=array())
	{
		
	}
}
?>