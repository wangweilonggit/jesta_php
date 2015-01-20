<?php
// model for user manage
class report extends CI_Model {
	function __construct()
    {
	    parent::__construct();
    }
    
	function report_answer($userid, $answerid, $text)
	{
		$sql = "SELECT username FROM ". TABLE_USERS ." WHERE userid='{$userid}' LIMIT 1";
		$result = get_sql_value($sql);
		if ( $result === false ) 
		{
			return;
		}
		
		$username=$result->username;
		
		$sql = "SELECT * FROM ".TABLE_ANSWER." WHERE answerid = '{$answerid}'";
		$result = get_sql_value($sql);
		if ( $result === false ) 
		{
			return;
		}
		
		$paid = ($result->cost > 0 ) ? 1 : 0;
		$answer_text = $result->content;
		$answer_user = $result->sender;
		$questionid = $result->questionid;
		
		$sql = "SELECT username FROM ". TABLE_USERS ." WHERE userid='{$answer_user}' LIMIT 1";
		$result = get_sql_value($sql);
		if ( $result === false ) 
		{
			return;
		}
		$answer_username = $result->username;
		
		$sql = "SELECT text FROM ". TABLE_QUESTION ." WHERE qid='{$questionid}' LIMIT 1";
		$result = get_sql_value($sql);
		if ( $result === false ) 
		{
			return;
		}
		$question_text = $result->text;
		
		$sql = "INSERT INTO " . TABLE_REPORT . " (userid, answerid, paid, text) VALUES  ('{$userid}', '{$answerid}', '{$paid}', '{$text}')";
		get_sql_query($sql);
		
		
		$config['mailpath'] = "/usr/sbin/sendmail";
		$config['protocol'] = "sendmail";
		$config['smtp_host'] = "relay-hosting.secureserver.net";
		$config['smtp_user'] = "support@jesta4me.com";  
		$config['smtp_pass'] = "jesta!@#$";
		$config['smtp_port'] = "25";
		$config['mailtype'] = "text";
		$config['validate'] = "TRUE";
		
		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");

		$this->email->from('support@jesta4me.com', 'JESTA');
        $this->email->to('kobib007@gmail.com'); 
        //$this->email->to('zhengguang04@gmail.com'); 

        $this->email->subject('New Report');
        $message = "New report is submitted from JESTA.\n\n";
        $message .= "Submitted User\t\t: ".$username ."\n";
        $message .= "Question\t\t: " . $question_text."\n";
        $message .= "Answered User\t\t: ".$answer_username."\n";
        $message .= "Answer\t\t\t\t: ".$answer_text."\n";
        $message .= "Report\t\t\t\t: ".$text."\n";
        
        $this->email->message($message);  

        $this->email->send();
	}
	
}
?>
