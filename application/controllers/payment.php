<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Payment extends CI_Controller {
	private $currency = 'USD'; // currency for the transaction
	private $ec_action = 'Sale'; // for PAYMENTREQUEST_0_PAYMENTACTION, it's either Sale, Order or Authorization
	private $paypal_fee = 0.039;
	
	function __construct() {
		parent::__construct();
		$paypal_details = array(
			'API_username' => 'wizard_api1.jesta.com',
			'API_signature' => 'AN6f-b01fVMjqc0boAxemUtcySSTAe3zrd3UL9UjYGh8UxONOCEI8XNO', 
			'API_password' => 'EUQTH2EZUX8YGTWH', 
			// 'sandbox_status' => false,
		);
		$this->load->library('paypal_ec', $paypal_details);
		$this->load->helper('url');
	}

	public function index($userid) {
		if ( !isset($userid) ) 
		{
			return;
		}
		
		$this->load->model("user");
		$user = $this->user->is_existuserById($userid);
		if ( $user === false) 
		{
			return ;
		}
		
		$data = array();
		$data['userid']=$userid;
		$data['paypal_fee']=$paypal_fee;
		$data['fund'] = $user->fund;
		
		$this->load->view("payment", $data);
	}

	function buy($userid) {
		if ( !isset($userid) ) 
		{
			return;
		}
		
		$amount = $_REQUEST["amount"];
		if ( $amount <= 0 ) 
		{
			return;
		}
		
		$this->load->model("user");
		$user = $this->user->is_existuserById($userid);
		if ( $user === false) 
		{
			return ;
		}
		
		$to_buy = array(
			'desc' => 'Subscribe for JESTA', 
			'currency' => $this->currency, 
			'type' => $this->ec_action, 
			'return_URL' => site_url('payment/back/'.$userid), 
			'cancel_URL' => site_url('cancel_payment'),
			'get_shipping' => false);
			$temp_product = array(
				'quantity' => 1, 
				'amount' => $amount);
				
			// add product to main $to_buy array
			$to_buy['products'][] = $temp_product;
		// enquire Paypal API for token
		$set_ec_return = $this->paypal_ec->set_ec($to_buy);
		if (isset($set_ec_return['ec_status']) && ($set_ec_return['ec_status'] === true)) {
			$this->paypal_ec->redirect_to_paypal($set_ec_return['TOKEN']);
		} else {
			$this->_error($set_ec_return);
		}
	}
	
	/* -------------------------------------------------------------------------------------------------
	* a sample back function that handles
	* --------------------------------------------------------------------------------------------------
	*/
	function back($userid) {
		// we are back from Paypal. We need to do GetExpressCheckoutDetails
		// and DoExpressCheckoutPayment to complete.
		$token = $_GET['token'];
		$payer_id = $_GET['PayerID'];
		// GetExpressCheckoutDetails
		$get_ec_return = $this->paypal_ec->get_ec($token);
		if (isset($get_ec_return['ec_status']) && ($get_ec_return['ec_status'] === true)) {
			// at this point, you have all of the data for the transaction.
			// you may want to save the data for future action. what's left to
			// do is to collect the money -- you do that by call DoExpressCheckoutPayment
			// via $this->paypal_ec->do_ec();
			//
			// I suggest to save all of the details of the transaction. You get all that
			// in $get_ec_return array
			$ec_details = array(
				'token' => $token, 
				'payer_id' => $payer_id, 
				'currency' => $this->currency, 
				'amount' => $get_ec_return['PAYMENTREQUEST_0_AMT'], 
				'IPN_URL' => site_url('payment/ipn/'.$userid), 
				// in case you want to log the IPN, and you
				// may have to in case of Pending transaction
				'type' => $this->ec_action);
				
			// DoExpressCheckoutPayment
			$do_ec_return = $this->paypal_ec->do_ec($ec_details);
			if (isset($do_ec_return['ec_status']) && ($do_ec_return['ec_status'] === true)) {
				$this->load->model("transaction");
				$this->transaction->purchase_from_paypal($userid,  $get_ec_return, $do_ec_return);
				$this->load->view("payment_complete");
			} else {
				$this->_error($do_ec_return);
			}
		} else {
			$this->_error($get_ec_return);
		}
	}
	
	/* -------------------------------------------------------------------------------------------------
	* The location for your IPN_URL that you set for $this->paypal_ec->do_ec(). obviously more needs to
	* be done here. this is just a simple logging example. The /ipnlog folder should the same level as
	* your CodeIgniter's index.php
	* --------------------------------------------------------------------------------------------------
	*/
	function ipn($userid) {
		$logfile = 'ipnlog/' . uniqid() . '.html';
		$logdata = "<pre>\r\n" . print_r($_POST, true) . '</pre>';
		file_put_contents($logfile, $logdata);
	}
	
	/* -------------------------------------------------------------------------------------------------
	* a simple message to display errors. this should only be used during development
	* --------------------------------------------------------------------------------------------------
	*/
	function _error($ecd) {
		echo "<br>error at Express Checkout<br>";
		echo "<pre>" . print_r($ecd, true) . "</pre>";
		echo "<br>CURL error message<br>";
		echo 'Message:' . $this->session->userdata('curl_error_msg') . '<br>';
		echo 'Number:' . $this->session->userdata('curl_error_no') . '<br>';
	}
}
/* Sample controller for Paypal_ec.php Library */
/* End of file test.php */
/* Location: ./application/controllers/test.php */
