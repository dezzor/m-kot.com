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

JSession::checkToken() or die( 'Invalid Token' );
class acySMSsendmessageClass{

	function sendSMS(){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$dbParams = "";

		$currentPage = $_SERVER['HTTP_REFERER'];

		$moduleId = JRequest::getInt('module_id');
		if(empty($moduleId)) return;
	 	$db->setQuery('SELECT params FROM #__modules WHERE id = '.intval($moduleId).' AND `module` LIKE \'%acysms%\' LIMIT 1');
	 	$dbParams = $db->loadResult();
	 	if(empty($dbParams)) return;

		$moduleParameter = new acysmsParameter($dbParams);
		$senderprofile = $moduleParameter->get('senderprofile');

		$phoneHelper = ACYSMS::get('helper.phone');
		$phoneClass = ACYSMS::get('class.phone');
		$class = ACYSMS::get('class.senderprofile');

		$numbers = JRequest::getVar('module_'.$moduleId.'_numbers','');

		if(empty($numbers[0]['phone_num'])){
			$app->enqueueMessage(JText::_('SMS_NO_PHONE'), 'error');
			$app->redirect($currentPage);
		}

		if(empty($senderprofile)){
			$app->enqueueMessage(JText::_('SMS_NO_SENDERPROFILE'), 'error');
			$app->redirect($currentPage);
		}

		$message_body = JRequest::getVar("message_body",'');
		if(empty($message_body)){
			$app->enqueueMessage(JText::_('SMS_ENTER_BODY'), 'error');
			$app->redirect($currentPage);
		}

		$receivers = array();
		foreach($numbers as $oneNumber){
			$validPhoneNumber = $phoneHelper->getValidNum(implode(',',$oneNumber));
			if(!$validPhoneNumber){
				$app->enqueueMessage(JText::sprintf('SMS_INVALID_PHONE_NUMBER', implode(',',$oneNumber)), 'warning');
			}
			else $receivers[] = acysms_getEscaped(strip_tags($validPhoneNumber));
		}


		if(empty($receivers)) $app->redirect($currentPage);
		$query = 'SELECT phone_number FROM '.ACYSMS::table("phone").' WHERE phone_number IN ("'.implode('","',$receivers).'")';
		$db->setQuery($query);
		$blockedPhones = $db->loadObjectList();


		$gateway = $class->getGateway($senderprofile);
		if(!$gateway->open()){
			$app->enqueueMessage(implode('<br />',$gateway->errors), 'error');
			return $this->preview();
		}

		foreach($receivers as $oneReceiver =>$onePhoneNumber){

			if(!empty($blockedPhones[$onePhoneNumber])){
				$app->enqueueMessage(JText::sprintf('SMS_ERROR_SENT','','<b><i>'.$onePhoneNumber.'</i></b>').'<br />'.JText::sprintf('SMS_USER_BLOCKED', $onePhoneNumber), 'error');
			}else{
				if(!empty($gateway->waittosend)) sleep($gateway->waittosend);
				$status = $gateway->send($message_body,$onePhoneNumber);
				if(!$status){
					$app->enqueueMessage(JText::sprintf('SMS_ERROR_SENT','','<b><i>'.$onePhoneNumber.'</i></b>').'<br />'.implode('<br />',$gateway->errors), 'error');
				}else{
					$app->enqueueMessage(JText::sprintf('SMS_SUCC_SENT','','<b><i>'.$onePhoneNumber.'</i></b>'), 'message');
				}

			}
		}
		$gateway->close();
		$app->redirect($currentPage);
	}
}
