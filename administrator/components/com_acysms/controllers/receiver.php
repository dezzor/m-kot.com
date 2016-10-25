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
class ReceiverController extends ACYSMSController{
	var $aclCat = 'receivers';

	function choose(){
		if(!$this->isAllowed('receivers','manage')) return;
		JRequest::setVar( 'layout', 'choose'  );
		return parent::display();
	}

	function add(){
		if(!$this->isAllowed('receivers','manage')) return;
		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );

		$integration = ACYSMS::getIntegration($currentIntegration);

		global $Itemid;
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;

		if(!$app->isAdmin()){
			$integration = ACYSMS::getIntegration('acysms');
			$url = $integration->addUserFrontURL.$myItem;
		}
		else	$url = $integration->addUserURL.$myItem;

		$this->setRedirect($url);
	}

	function edit(){
		if(!$this->isAllowed('receivers','manage')) return;
		$config = ACYSMS::config();
		$app = JFactory::getApplication();

		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cid)) return;
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);

		if(!$app->isAdmin()){
			$integration = ACYSMS::getIntegration('acysms');
			$url = $integration->editUserFrontURL.substr($cid[0],0,strpos($cid[0],'_'));
		}
		else $url = $integration->editUserURL.substr($cid[0],0,strpos($cid[0],'_'));;


		$this->setRedirect($url);
	}

	function remove(){
		JRequest::checkToken() or die( 'Invalid Token' );
		if(!$this->isAllowed('receivers','delete')) return;

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cids)) return $this->listing();
		$class = ACYSMS::get('class.user');
		JArrayHelper::toInteger($cids);
		$num = $class->delete($cids);
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS',$num), 'message');

		return $this->listing();
	}

	function block(){
		if(!$this->isAllowed('receivers','manage')) return;

		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cid)) return;
		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($cid, 0);
		return $this->listing();
	}

	function unblock(){
		if(!$this->isAllowed('receivers','manage')) return;

		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		if(empty($cid)) return;
		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($cid, 1);
		return $this->listing();
	}

	function conversation(){
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar( 'layout', 'conversation');
		return parent::display();
	}

	function getReceiversByName(){
		$app = JFactory::getApplication();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);

		$NameSearched = JRequest::getVar('nameSearched', '');
		if(empty($NameSearched)) exit;

		$users = $integration->getReceiversByName($NameSearched);
		echo '<table>';
			foreach($users as $oneUser){
				echo '<tr class="row_user" onclick="setUser(\''.str_replace("'","\'",$oneUser->name).'\',\''.str_replace("'","\'",$oneUser->receiverId).'\');loadConversation();" style="cursor:pointer"><td>'.htmlspecialchars($oneUser->name, ENT_COMPAT, 'UTF-8').'</td></tr>';
			}
		echo '</table>';
		exit;
	}

	function sendOneShotSMS(){
		$receiverIdsString = JRequest::getCmd('receiverIds');
		$senderProfile = JRequest::getCmd('senderProfile_id');
		$messageBody = JRequest::getString('messageBody');

		if(empty($messageBody)){
			ACYSMS::display(JText::_('SMS_ENTER_BODY'), 'error');
			exit;
		}

		if(empty($receiverIdsString)){
			ACYSMS::display(JText::_('SMS_SELECT_RECEIVER'), 'error');
			exit;
		}

		if(empty($senderProfile)){
			ACYSMS::display('Please select a sender profile', 'error');
			exit;
		}

		$messageClass = ACYSMS::get('class.message');
		$userClass = ACYSMS::get('class.user');

		$receiverIdArray = explode('-',$receiverIdsString);

		$message = new stdClass();
		$message->message_senderprofile_id = $senderProfile;
		$message->message_body = $messageBody;
		$message->message_type = 'conversation';

		$messageClass->sendOneShotSMS($message, $receiverIdArray);

		$isAjax = JRequest::getCmd('isAjax','');
		if($isAjax) exit;
		return $this->conversation();
	}
}
