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

class SenderprofileController extends ACYSMSController{

	var $aclCat = 'sender_profiles';

	function copy(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('sender_profiles','copy')) return;

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		$db = JFactory::getDBO();
		$time = time();

		$my = JFactory::getUser();
		$creatorId = intval($my->id);

		foreach($cids as $oneSenderProfileid){
			$query = 'INSERT INTO `#__acysms_senderprofile` (`senderprofile_name`,`senderprofile_gateway`,`senderprofile_userid`,`senderprofile_params`)';
			$query .= " SELECT CONCAT('copy_',`senderprofile_name`), `senderprofile_gateway`,".intval($creatorId).",`senderprofile_params`  FROM `#__acysms_senderprofile` WHERE `senderprofile_id` = ".intval($oneSenderProfileid);
			$db->setQuery($query);
			$db->query();
		}

		return $this->listing();
	}

	function gatewayparams(){
		$gateway = JRequest::getCmd('gateway');

		if(!empty($gateway)){
			$class = ACYSMS::get('class.senderprofile');
			$gateway = $class->getGateway($gateway);
			$gateway->displayConfig();
		}
		exit;
	}

	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('sender_profiles','manage')) return;

		$app = JFactory::getApplication();
		$class = ACYSMS::get('class.senderprofile');
		$status = $class->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'SMS_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'SMS_ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function sendtest(){
		if(!$this->isAllowed('sender_profiles','sendtest')) return;

		$this->store();

		$app = JFactory::getApplication();
		$class = ACYSMS::get('class.senderprofile');
		$phoneHelper = ACYSMS::get('helper.phone');

		$gatewayId = ACYSMS::getCID('senderprofile_id');

		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);
		$currentIntegration = $integration->componentName;
		$testID = $app->getUserStateFromRequest( $currentIntegration."_testID", $currentIntegration."_testID",	'', 'int' );

		$message_body = JRequest::getString('message_body');
		if(empty($gatewayId)) return;

		if(empty($testID)){
			$app->enqueueMessage(JText::_('SMS_NO_USER_TEST'),'warning');
			return $this->edit();
		}

		$gateway = $class->getGateway($gatewayId);

		if(!$gateway->open()){
			$app->enqueueMessage(implode('<br />',$gateway->errors), 'error');
			return $this->edit();
		}

		$user = new stdClass();
		$user->queue_receiver_id = intval($testID);
		$testUser = array($user);
		$integration->addUsersInformations($testUser);
		$receiver = reset($testUser);

		$phone = $phoneHelper->getValidNum($receiver->receiver_phone);
		if(!$phone){
			$app->enqueueMessage($phoneHelper->error, 'error');
			return $this->edit();
		}
		$status = $gateway->send($message_body, $phone);
		$gateway->close();

		if(!$status){
			$app->enqueueMessage(JText::sprintf('SMS_ERROR_SENT','','<b><i>'.$receiver->receiver_phone.'</i></b>').'<br />'.implode('<br />',$gateway->errors), 'error');
		}else{
			$app->enqueueMessage(JText::sprintf('SMS_SUCC_SENT','','<b><i>'.$phone.'</i></b>'), 'message');
		}

		return $this->edit();
	}

	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('sender_profiles','delete')) return;

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		$class = ACYSMS::get('class.senderprofile');
		$num = $class->delete($cids);
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS',$num), 'message');

		return $this->listing();
	}
}
