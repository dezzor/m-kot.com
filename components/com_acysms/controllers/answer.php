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
class AnswerController extends acysmsController{

	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('answer');
	}

	function answer(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		$gatewayName = JRequest::getCmd("gateway",'');
		if(empty($gatewayName)) die('No gateway specified');

		$pass = JRequest::getCmd("pass",JRequest::getCmd("p",''));
		if(empty($pass) || $pass != $config->get('pass')) die('Pass not valid');

		$gatewayClass = ACYSMS::get('class.senderprofile');
		$gateway = $gatewayClass->getGateway($gatewayName);

		if(empty($gateway)) exit;

		$apiAnswer = $gateway->answer();

		$answerClass = ACYSMS::get('class.answer');
		$answer_id = $answerClass->addAnswer($apiAnswer);

		if(!$answer_id) exit;

		$answerClass->processAnswerTriggers($answer_id);

		$gateway->closeRequest();

		exit(header("Status: 200 OK"));
	}
}
