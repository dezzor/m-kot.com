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
class plgAcySMSFreestyleSupport extends JPlugin
{
	function plgAcySMSFreestyleSupport(&$subject, $config){
		parent::__construct($subject, $config);
		if(!$this->init()) return;
	}

	function init(){
		if(!file_exists(rtrim(JPATH_SITE,DS).DS.'components'.DS.'com_fss'))	return;
		if(!file_exists(dirname(__FILE__).DS.'acysms.php')) return;

		if(!file_exists(rtrim(JPATH_SITE,DS).DS.'components'.DS.'com_fss'.DS.'plugins'.DS.'tickets'.DS.'acysms.php')){
			if(!copy(dirname(__FILE__).DS.'acysms.php', rtrim(JPATH_SITE,DS).DS.'components'.DS.'com_fss'.DS.'plugins'.DS.'tickets'.DS.'acysms.php')){
				$app = JFactory::getApplication();
				$app->enqueueMessage('The Freestyle support plugin for AcySMS can\'t copy the file acysms.php in the Freestyle Support directory. Please share this message with your admin.');
			}
		}

		if(defined('ACYSMS_COMPONENT'))
			return true;
		$acySmsHelper = rtrim(JPATH_ROOT,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper))	include_once $acySmsHelper;
		return defined('ACYSMS_COMPONENT');
	}

	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_BASED_ON_FS_NOTIFICATION');
		$types['fsnotification'] = $newType;
	}

	function onACYSMSdisplayParamsAutoMessage_fsnotification($message){

		$triggerValues = array();
		$triggerValues[] = JHTML::_('select.option','ticketCreated',JText::_('SMS_FS_TICKET_CREATED'));
		$triggerValues[] = JHTML::_('select.option','ticketReplied',JText::_('SMS_FS_TICKET_REPLIED'));
		$triggerDropdown =  JHTML::_('select.genericlist', $triggerValues, "data[message][message_receiver][auto][fsnotification][trigger]" , 'size="1" style="width:auto"','value', 'text');

		echo JText::sprintf('SMS_SEND_FS_NOTIFICATIONS', $triggerDropdown).'<br />';

		$receiverType = '';
		if(!empty($message->message_receiver['auto']['fsnotification']['receiverType']))  $receiverType = $message->message_receiver['auto']['fsnotification']['receiverType'];

		$oneUserSelected = '';
		if(empty($receiverType) || $receiverType == 'oneUser') $oneUserSelected = 'checked';

		$threadSubscribersSelected = '';
		if(!empty($receiverType) && $receiverType == 'threadSubscribers') $threadSubscribersSelected = 'checked';

		$userAssignedSelected = '';
		if(!empty($receiverType) && $receiverType == 'userAssigned') $userAssignedSelected = 'checked';


		$receiverSelection =  '<input type="radio" name="data[message][message_receiver][auto][fsnotification][receiverType]" '.$oneUserSelected.' value="oneUser" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'block\';" id="fs_oneReceiver"/> <label for="fs_oneReceiver">'.JText::_('SMS_SPECIFIC_USER').'</label>';
		$receiverSelection .= '<input type="radio" name="data[message][message_receiver][auto][fsnotification][receiverType]" '.$threadSubscribersSelected.' value="threadSubscribers" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'none\';" id="fs_threadSubscribers"/> <label for="fs_threadSubscribers">'.JText::_('SMS_THREAD_SUBSCRIBERS').'</label>';
		$receiverSelection .=  '<input type="radio" name="data[message][message_receiver][auto][fsnotification][receiverType]" '.$userAssignedSelected.' value="userAssigned" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'none\';" id="fs_userAssigned"/> <label for="fs_userAssigned">'.JText::_('SMS_USER_ASSIGNED_TO_TICKET').'</label>';

		echo JText::sprintf('SMS_SEND_MESSAGE_TO',$receiverSelection);


		$userName = '';
		if(!empty($message->message_receiver['auto']['fsnotification']['fsNotification_receiverName']))  $userName = $message->message_receiver['auto']['fsnotification']['fsNotification_receiverName'];

		echo '<br/>';

		$style = '';
		if(!empty($threadSubscribersSelected) || !empty($userAssignedSelected)) $style = 'style="display:none"';
		echo '<div id="oneReceiverParameters" '.$style.'>';
		echo '<input type="hidden" id="fsNotification_receiverid" name="data[message][message_receiver][auto][fsnotification][fsNotification_receiverid]"/>';
		echo '<input type="hidden" id="fsNotification_receiverName" name="data[message][message_receiver][auto][fsnotification][fsNotification_receiverName]"/>';
		echo JText::sprintf('SMS_SELECT_USER','<span id="fsNotification_receiverNameDisplayed"/>'.$userName.'</span><a class="modal" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose&&jsFct=affectUser&currentIntegration='.$message->message_receiver_table.'\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png"/></a>');
		echo '</div>';
	}

	function onAcySMS_FreestyleSupportSendNotification($ticket, $params, $status){

		$db = JFactory::getDBO();
	 	$config = ACYSMS::config();

		$messageClass = ACYSMS::get('class.message');
	 	$allMessages = $messageClass->getAutoMessage('fsnotification');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No Freestyle Support notification message configured in AcySMS, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create a message</a> using the type : Automatic -> '.JText::_('SMS_AUTO_FSNOTIFICATION ');
			return false;
		}

		foreach($allMessages as $oneMessage){

			if($oneMessage->message_receiver['auto']['fsnotification']['trigger'] != $status) continue;

			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);
			$receivers = array();

			if ($oneMessage->message_receiver['auto']['fsnotification']['receiverType'] == 'oneUser'){

				if(empty($oneMessage->message_receiver['auto']['fsnotification']['fsNotification_receiverid'])){
					$this->messages[] = "Please select the user who will receive the notifications for the SMS ".$oneMessage->message_id;
					continue;
				}
				$receivers = array($oneMessage->message_receiver['auto']['fsnotification']['fsNotification_receiverid']);
			}else if($oneMessage->message_receiver['auto']['fsnotification']['receiverType'] == 'threadSubscribers'){

				$subscribedUserQuery = 'SELECT DISTINCT user_id
											FROM #__fss_ticket_messages AS messages
											JOIN rjapw_users AS users
											ON messages.user_id = users.id
											WHERE messages.ticket_ticket_id = '.intval($ticket->id);

				$db->setQuery($subscribedUserQuery);
				$subscribedUsers = $db->loadResultArray();
				if(empty($subscribedUsers)) continue;

				if(!empty($ticket->user_id) && in_array($ticket->user_id,$subscribedUsers)) unset($subscribedUsers[array_search($ticket->user_id,$subscribedUsers)]);


				$receivers = $integration->getReceiverIDs($subscribedUsers);
				if(empty($receivers)) continue;

			}else if($oneMessage->message_receiver['auto']['fsnotification']['receiverType'] == 'userAssigned'){
				if(empty($ticket->user_id)) continue;
				$receivers = $integration->getReceiverIDs(array($ticket->user_id));
				if(empty($receivers)) continue;

			}else continue;


			$config = ACYSMS::config();
			$db = JFactory::getDBO();
			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $oneMessage->message_receiver_table;
			$integrationFrom = $integration->componentName;
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			if(!empty($receivers))	$acyquery->addUserFilters($receivers, $integrationFrom, $integrationTo);
			$querySelect = $acyquery->getQuery(array('DISTINCT '.$oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.time().', '.$config->get('priority_message',3)));
			$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();

			$queueHelper = ACYSMS::get('helper.queue');
			$queueHelper->report = false;
			$queueHelper->message_id = $oneMessage->message_id;
			$queueHelper->process();
		}
	}
}//endclasss
