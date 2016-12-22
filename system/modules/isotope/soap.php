<?php
namespace Isotope;

use Haste\Http\Response\Response;

/**
 * Set the script name
 */
define('TL_SCRIPT', 'system/modules/isotope/soap.php');

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
define('BYPASS_TOKEN_CHECK', true);

require_once('initialize.php');

class Soap extends \Frontend
{
	public function getMessage()
	{
		return "test";
	}
	  
	public function addNumbers($num1,$num2)
	{

	}
}

$options= array('uri'=>'http://localhost/isoap');
$server=new SoapServer(NULL,$options);
$server->setClass('Isoap');
$server->handle();
