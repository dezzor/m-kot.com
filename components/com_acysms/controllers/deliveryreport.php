<?php
/**
 * @package	AcySMS for Joomla!
 * @version	1.7.7
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class DeliveryReportController extends acysmsController{

	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('deliveryReport');
	}

	function deliveryReport(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		$gatewayName = JRequest::getCmd("gateway",JRequest::getCmd('g'));
		if(empty($gatewayName)) exit;

		$pass = JRequest::getCmd("pass",JRequest::getCmd("p",''));
		if(empty($pass) || $pass != $config->get('pass')) $app->redirect('index.php', 'Pass not valid', '', true);


		$gatewayClass = ACYSMS::get('class.senderprofile');
		$gateway = $gatewayClass->getGateway($gatewayName);

		$apiAnswer = $gateway->deliveryReport();

		$statsClass = ACYSMS::get('class.stats');
		$statsClass->addDeliveryInformations($apiAnswer);

		$gateway->closeRequest();

		exit;
	}
}
