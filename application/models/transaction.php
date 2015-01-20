<?php
// model for user manage
class transaction extends CI_Model {
	function __construct()
    {
	    parent::__construct();
    }
    
	function purchase_from_paypal($userid,  $get_data, $do_data)
	{
		$paypal_id = $get_data['EMAIL'];
		$token = $do_data['TOKEN'];
		$amount = $do_data['PAYMENTINFO_0_AMT'] - $do_data['PAYMENTINFO_0_FEEAMT'] - $do_data['PAYMENTINFO_0_TAXAMT'];
		$transactionid = $do_data['PAYMENTINFO_0_TRANSACTIONID'];
		$raw_data=str_replace('\\', '', json_encode(array("GetExpressCheckoutDetails"=>$get_data, "DoExpressCheckoutPayment"=>$do_data)));
		
		$sql = "INSERT INTO ".TABLE_TRANSACTION." (`from`, `to`, `from_paypal_id`, `to_paypal_id`, `token`, ".
				"`amount`, `tranactionId`, `raw_data`) VALUES ('0', '{$userid}', '{$paypal_id}', '', '{$token}', '{$amount}', ".
				"'{$transactionid}', '{$raw_data}')";
		get_sql_query($sql);
		
		$this->load->model("user");
		$this->user->increase_fund($userid, $amount);
	}
	
	function purchase_from_device($userid, $proof, $payment, $amount)
	{
		$paypal_id = '';
		$token = '';
		$transactionid = '';
		$raw_data=json_encode(array("Proof"=>$proof, "payment"=>$payment));
		$raw_data = preg_replace("{\\\}", "", $raw_data);
		
		$sql = "INSERT INTO ".TABLE_TRANSACTION." (`from`, `to`, `from_paypal_id`, `to_paypal_id`, `token`, ".
				"`amount`, `tranactionId`, `raw_data`) VALUES ('0', '{$userid}', '{$paypal_id}', '', '{$token}', '{$amount}', ".
				"'{$transactionid}', '{$raw_data}')";
		get_sql_query($sql);
		
		$this->load->model("user");
		$this->user->increase_fund($userid, $amount);
	}

	
	function purchase_for_unlock($sender, $receiver, $amount, $answerid) 
	{
		$sql = "INSERT INTO ".TABLE_TRANSACTION." (`from`, `to`, `amount`, `answerid`) VALUES ('{$sender}', '{$receiver}', '{$amount}', '{$answerid}')";
		get_sql_query($sql);
		
		$this->load->model("user");
		$this->user->increase_fund($sender, -$amount);
		$this->user->increase_fund($receiver, $amount * 0.9);
	}
	
}
?>