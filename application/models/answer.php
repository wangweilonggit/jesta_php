<?php
// model for user manage
class answer extends CI_Model {
	
	function __construct()
    {
	    parent::__construct();
    }
    
	function get_answers($userid, $queid)
	{
		$sql = "SELECT U.userid, U.username, U.image, (TIMESTAMPDIFF(MINUTE, now(), U.last_online)+1) as online, ".
				" A.content as text, TIMESTAMPDIFF(MINUTE, A.time, now()) as time, SUM(A.rating) as rating, COUNT(*) as answer_count,".
				" SUM(1-A.`read`) as new_count, A.type, A.cost, A.locked FROM " . TABLE_USERS . " U, ".
				"(SELECT * FROM ".TABLE_ANSWER. " WHERE questionid='{$queid}' AND receiver='{$userid}' ORDER BY answerid DESC) A ".
				" WHERE U.userid=A.sender GROUP BY U.userid ";
		$result = get_sql_result($sql);
		$answers = array();
		foreach($result as $key => $answer)
		{
			$answer->time = convertToTime($answer->time);
			if ( $answer->locked == 0 ) 
				$answer->cost = -$answer->cost;
			$answers[] = $answer;
		}		
		return $answers;
	}
	
	function add_answer($userid, $queid, $otherid, $type, $cost, $content)
	{
		$locked = $cost > 0 ? 1 : 0;
		$sql = "INSERT INTO ".TABLE_ANSWER." (questionid, sender, receiver, content, type, cost, locked ) ".
				"VALUES ('{$queid}','{$userid}','{$otherid}','{$content}','{$type}','{$cost}', '{$locked}' ) ";
		get_sql_query($sql);
		$answer_id = mysql_insert_id();
		
		$sql = "SELECT username FROM ". TABLE_USERS ." WHERE userid='{$userid}' LIMIT 1";
		$result = get_sql_value($sql);
		
		if ( $result === false ) 
		{
			$message = "Guest : ";
		}
		else
		{
			$message = $result->username . " : ";
		}
		
		switch ( $type ) {
			case MSG_TYPE_TEXT:
				$message .= $content;
				break;
			case MSG_TYPE_AUDIO:
				$message .= " attached audio file";
				break;
			case MSG_TYPE_PHOTO:
				$message .= " attached photo file";
				break;
			case MSG_TYPE_VIDEO:
				$message .= " attached video file";
				break;
			case MSG_TYPE_LOCATION:
				$message .= " attached location info";
				break;
			case MSG_TYPE_CONTACT:
				$message .= " attached contact info";
				break;
		} 
		
		if ( $cost > 0 )
			$message = $result->username . " : payment answer $" . intval($cost);
		
		$sql = "SELECT device, token FROM ". TABLE_USERS ." WHERE userid = '{$otherid}'";
		
		$result = get_sql_result($sql);
		$android_array= array();
		$iphone_array = array();
		$data = array();
		
		foreach($result as $key => $user)
		{
			if ( !IsNullOrEmptyString($user->token) )
			{
				if ( $user->device == "Android" ) 
				{
					$android_array[] = $user->token;
				}
				else
				{
					$iphone_array[] = $user->token;
				}
			}
		}
		
		$type = TYPE_ANSWER;
		
		$data = array();
		$data['sender'] = $userid;
		$data['receiver'] = $otherid;
		$data['questionid'] = $queid;
		
		$this->load->model("user");
		$this->user->send_gcm($android_array, $message, $type, $data);
		$this->user->send_apns($iphone_array, $message, $type, $data);
	}
	
	function get_messages($userid, $queid, $otherid, $last_answer_id) 
	{
		$sql = "SELECT username, image FROM ". TABLE_USERS . " WHERE userid='{$userid}'";
		$result = get_sql_value($sql);
		
		if ( $result === false ) 
		{
			$username = "Guest";
			$userimage = "";
		}
		else
		{
			$username = $result->username;
			$userimage = $result->image;
		}
		
		$sql = "SELECT username, image FROM ". TABLE_USERS . " WHERE userid='{$otherid}'";
		$result = get_sql_value($sql);
		
		if ( $result === false ) 
		{
			$othername = "Guest";
			$otherimage = "";
		}
		else
		{
			$othername = $result->username;
			$otherimage = $result->image;
		}
		
		$this->load->model("question");
		/*$question = $this->question->get_question_by_user_and_id($otherid, $queid);
		if ( $question === false || $userid < 0 ) 
		{
			$sql = "UPDATE ".TABLE_ANSWER." SET `read`=1 WHERE ".
				"questionid='{$queid}' AND ((sender='{$userid}' AND receiver='{$otherid}') OR ".
				"(receiver='{$userid}' AND sender='{$otherid}')) AND answerid > '{$last_answer_id}'";*/
			$sql = "UPDATE ".TABLE_ANSWER." SET `read`=1 WHERE questionid='{$queid}' AND ".
				"(receiver='{$userid}' AND sender='{$otherid}') AND answerid > '{$last_answer_id}'";
			get_sql_query($sql);
		//}
		
		$sql = "SELECT answerid, sender as senderid, questionid, IF(sender='{$userid}','{$username}','{$othername}') as sendername, ".
				"IF(sender='{$userid}','{$userimage}','{$otherimage}') as senderimage, receiver as receiverid,".
				" IF(receiver='{$userid}','{$username}','{$othername}') as receivername, ".
				" IF(receiver='{$userid}','{$userimage}','{$otherimage}')  as receiverimage, ".
				"type, content, time, cost, locked, rating, unlock_time FROM ".TABLE_ANSWER. " WHERE ".
				"questionid='{$queid}' AND ((sender='{$userid}' AND receiver='{$otherid}') OR ".
				"(receiver='{$userid}' AND sender='{$otherid}')) AND answerid > '{$last_answer_id}'";
		
		return get_sql_result($sql);
	}
	
	function get_unlocks($userid, $queid, $otherid, $last_unlock_time)
	{
		$sql = "SELECT answerid, unlock_time FROM ".TABLE_ANSWER. " WHERE ".
				"questionid='{$queid}' AND ((sender='{$userid}' AND receiver='{$otherid}') OR ".
				"(receiver='{$userid}' AND sender='{$otherid}')) AND unlock_time > '{$last_unlock_time}'";
		return get_sql_result($sql);
	}
	
	function unlock_answer($userid, $answerid)
	{
		$this->load->model("user");
		$user = $this->user->is_existuserById($userid);
		
		if ($user===false) 
		{
			return "Invalid request parameters";
		}
		
		$sql = "SELECT sender, receiver, cost, questionid FROM ".TABLE_ANSWER." WHERE answerid='{$answerid}'";
		$result = get_sql_value($sql);
		
		if ( $result === false ) 
		{
			return "Invalid request parameters";
		}
		
		if ( $userid != $result->receiver ) 
		{
			return "Invalid request parameters";
		}
		
		if ( $user->fund < $result->cost ) 
		{
			return "You haven't enough charge in your account of Jesta\n Your balance : {$user->fund}$, Answer Cost : {$result->cost}$";
		}
		
		$otherid = $result->sender;
		$queid = $result->questionid;
		$cost = $result->cost;
		
		$this->load->model("transaction");
		$this->transaction->purchase_for_unlock($userid, $otherid, $cost, $answerid);
		
		$sql = "UPDATE ". TABLE_ANSWER . " SET locked=0, unlock_time=CURRENT_TIMESTAMP WHERE answerid='{$answerid}'";
		tolog($sql);
		get_sql_query($sql);
		
		
		$sql = "SELECT username FROM ". TABLE_USERS ." WHERE userid='{$userid}' LIMIT 1";
		$result = get_sql_value($sql);
		
		$message = $result->username . " has unlocked your answer with payment '{$cost}$'";
		
		$sql = "SELECT device, token FROM ". TABLE_USERS ." WHERE userid = '{$otherid}'";
		
		$result = get_sql_result($sql);
		$android_array= array();
		$iphone_array = array();
		$data = array();
		
		foreach($result as $key => $user)
		{
			if ( !IsNullOrEmptyString($user->token) )
			{
				if ( $user->device == "Android" ) 
				{
					$android_array[] = $user->token;
				}
				else
				{
					$iphone_array[] = $user->token;
				}
			}
		}
		
		$type = TYPE_ANSWER;
		
		$data = array();
		$data['sender'] = $otherid;
		$data['receiver'] = $userid;
		$data['questionid'] = $queid;
		
		$this->load->model("user");
		$this->user->send_gcm($android_array, $message, $type, $data);
		$this->user->send_apns($iphone_array, $message, $type, $data);
		return '';
	}
	
		
	function rate_answer($userid, $answerid, $rate)
	{
		$sql = "UPDATE ".TABLE_ANSWER." SET rating='{$rate}' WHERE answerid='{$answerid}' AND receiver='{$userid}'";
		get_sql_query($sql);
	}
}
?>