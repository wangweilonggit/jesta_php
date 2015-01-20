<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller {

	private static $instance;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$instance =& $this;
		
		// Assign all the class objects that were instantiated by the
		// bootstrap file (CodeIgniter.php) to local class variables
		// so that CI can run as one big super object.
		foreach (is_loaded() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		$this->load =& load_class('Loader', 'core');

		$this->load->initialize();
		
		log_message('debug', "Controller Class Initialized");
	}

	public static function &get_instance()
	{
		return self::$instance;
	}
	
	public function CorrectParam($param, $def = "")
	{
		if(!isset($param)) return $def;
		if((!is_numeric($param)) && ($def != "")) return $def;
		return $param;
	}
	public function CorrectRequest($param, $def)
	{
		$ret = $def;
		if(isset($_POST[$param])) $ret = $_POST[$param];
		if(isset($_GET[$param])) $ret = $_GET[$param];
		return $ret;
	}
	public function RestrictArray($arrSrc, $arrField){
		$arrRet = array();
		for($i=0; $i<count($arrSrc); $i++)
			array_push($arrRet, $this->Restrict($arrSrc[$i], $arrField));
		return $arrRet;
	}
	public function Restrict($obj, $arrField){
		$arrObj = get_object_vars($obj);
		foreach($arrObj as $key=>$value){
			if(!(in_array($key, $arrField))) unset($obj->$key);
		}
		return $obj;
	}
}
// END Controller class

/* End of file Controller.php */
/* Location: ./system/core/Controller.php */