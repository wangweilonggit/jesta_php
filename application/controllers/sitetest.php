<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include("www/include/global.php");
$data['baseDir'] = $baseDir;
/*
|--------------------------------------------------------------------------
| Basis controller for Inzone Management Sites
|--------------------------------------------------------------------------
|
| Description
|
*/
class sitetest extends CI_Controller {

function __construct()
{
	parent::__construct();
}
/*
|--------------------------------------------------------------------------
| Modelling functions
|--------------------------------------------------------------------------
| 
|
*/
public function index()
{
	$this->apis();
}

// API Test
public function apis()
{
	global $data;
	$this->load->view("t_apis", $data);
}

// SMS Test
public function sms_viewpage()
{
	
}

public function facebook()
{
	global $data;
	$this->load->view("facebook", $data);
}

}

?>