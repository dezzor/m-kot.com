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
class plgAcysmsAnswerTrigger extends JPlugin
{
	function plgAcysmsAnswerTrigger(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms');
			$this->params = new JParameter( $plugin->params );
		}
	}

	function onACYSMSDisplayActionsAnswersTrigger(&$actions,$answerTrigger){

		$newActionUnsubscribe = new stdClass();
		$newActionUnsubscribe->name = JText::_('SMS_ANSWER_TRIGGER_SUBSCRIBE');
		$actions['subscribe'] = $newActionUnsubscribe;

		$groupType = ACYSMS::get('type.group');
		$newActionForward = new stdClass();
		$newActionForward->name = JText::_('SMS_ACTION_TRIGGER_SUBSCRIBE_GROUP').' : ';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('subscribegroup', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['subscribegroup']) && !empty($answerTrigger->answertrigger_actions['subscribegroup']['group_id']))
			$group_id = $answerTrigger->answertrigger_actions['subscribegroup']['group_id'];
		$newActionForward->extra = $groupType->display("data[answertrigger][answertrigger_actions][subscribegroup][group_id]",@$group_id);
		$actions['subscribegroup'] = $newActionForward;

		$newActionDeleteAnswer = new stdClass();
		$newActionDeleteAnswer->name = JText::_('SMS_ANSWER_TRIGGER_DELETEANSWER');
		$actions['deleteanswer'] = $newActionDeleteAnswer;

		$newActionForward = new stdClass();
		$newActionForward->name = JText::_('SMS_ANSWER_TRIGGER_FORWARDEMAIL').' : ';
		$emailAddress = '';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('forwardemail', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['forwardemail']) && !empty($answerTrigger->answertrigger_actions['forwardemail']['emailAddress']))
			$emailAddress = $answerTrigger->answertrigger_actions['forwardemail']['emailAddress'];
		$newActionForward->extra = '<input type="text" name="data[answertrigger][answertrigger_actions][forwardemail][emailAddress]" value="'.$emailAddress.'"/>';
		$actions['forwardemail'] = $newActionForward;

		$answerMessageType = ACYSMS::get('type.answermessage');
		$newActionForward = new stdClass();
		$newActionForward->name = JText::_('SMS_ACTION_TRIGGER_ANSWER_MESSAGE').' : ';
		$emailAddress = '';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('sendmessage', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['sendmessage']) && !empty($answerTrigger->answertrigger_actions['sendmessage']['message_id']))
			$message_id = $answerTrigger->answertrigger_actions['sendmessage']['message_id'];
		$newActionForward->extra = $answerMessageType->display(@$message_id);
		$actions['sendmessage'] = $newActionForward;

		$newActionUnsubscribe = new stdClass();
		$newActionUnsubscribe->name = JText::_('SMS_ANSWER_TRIGGER_UNSUBSCRIBE');
		$actions['unsubscribe'] = $newActionUnsubscribe;


	}

	public function onACYSMSTriggerActions_forwardemail($actionsParams, $answer){
		if(empty($actionsParams['forwardemail']['emailAddress'])) return;
		$emailAddress = $actionsParams['forwardemail']['emailAddress'];

		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$user = JFactory::getUser();
		$mailer->addRecipient($emailAddress);
		$subject =  JText::sprintf('SMS_ACTION_TRIGGER_FORWARDEMAIL_SUBJECT', $answer->answer_from);
		$mailer->setSubject($subject);
		$body = JText::_('SMS_FROM').' : '.$answer->answer_from.'<br />';
		$body .= JText::_('SMS_TO').' : '.$answer->answer_to.'<br />';
		$body .= JText::_('SMS_RECEPTION_DATE').' : '.date(JText::_('DATE_FORMAT_LC2'), $answer->answer_date).'<br />';
		$body .= JText::_('SMS_SMS_BODY').' : '.$answer->answer_body.'<br />';

		$stringToReplace = array('{answer_body}', '{answer_to}', '{answer_date}', '{answer_from}');
		$values = array($answer->answer_body, $answer->answer_to, $answer->answer_date, $answer->answer_from );

		if(file_exists(ACYSMS_MEDIA.'plugins'.DS.'answer.php')){
			ob_start();
			require(ACYSMS_MEDIA.'plugins'.DS.'answer.php');
			$result = ob_get_clean();
			$body = str_replace($stringToReplace,$values,$result);
		}
		$mailer->setBody(nl2br($body));
		$send = $mailer->Send();
	}

	public function onACYSMSTriggerActions_deleteanswer($actionsParams, $answer){
		if(empty($answer->answer_id)) return;

		$answerClass = ACYSMS::get('class.answer');
		$answerClass->delete($answer->answer_id);
	}

	public function onACYSMSTriggerActions_unsubscribe($actionsParams, $answer){
		if(empty($answer->answer_from)) return;

		$phoneHelper = ACYSMS::get('helper.phone');
		$validNum = $phoneHelper->getValidNum($answer->answer_from);

		if(!$validNum) return;

		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($validNum, 0);


		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validNum);
		if(empty($user)) return;

		$groupUserClass = ACYSMS::get('class.groupuser');
		$groupUserClass->removeAllSubscriptions($user->user_id);
	}

	public function onACYSMSTriggerActions_subscribe($actionsParams, $answer){
		if(empty($answer->answer_from)) return;

		$userClass = ACYSMS::get('class.user');
		$phoneHelper = ACYSMS::get('helper.phone');

		$validNum = $phoneHelper->getValidNum($answer->answer_from);
		if(!$validNum) return;

		$alreadyExists = $userClass->getByPhone($validNum);
		if(!empty($alreadyExists)) return;

		$user = new stdClass();
		$user->user_phone_number = $validNum;
		$user_id = $userClass->save($user);

		$answerClass = ACYSMS::get('class.answer');
		$answer->answer_receiver_id = $user_id;
		$answer->answer_receiver_table = 'acysms';
		$answerClass->save($answer);

	}

	public function onACYSMSTriggerActions_subscribegroup($actionsParams, $answer){
		if(empty($answer->answer_from)) return;

		$phoneHelper = ACYSMS::get('helper.phone');
		$validNum = $phoneHelper->getValidNum($answer->answer_from);

		if(!$validNum) return;

		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validNum);
		if(empty($user)) return;

		$groupUserClass = ACYSMS::get('class.groupuser');
		$groupUserClass->addSubscription($user->user_id, array('1' => array($actionsParams['subscribegroup']['group_id'])));
	}

	public function onACYSMSTriggerActions_sendmessage($actionsParams, $answer){

		$integration = ACYSMS::getIntegration();
		$phoneHelper = ACYSMS::get('helper.phone');
		$queueHelper = ACYSMS::get('helper.queue');
	 	$db = JFactory::getDBO();
		if(!empty($answer->answer_from)) $informations = $integration->getInformationsByPhoneNumber($phoneHelper->getValidNum($answer->answer_from));

		if(empty($informations)){
			$userClass = ACYSMS::get('class.user');
			$newUser = new stdClass();

			$newUser->user_phone_number = $answer->answer_from;
			$receiverId = $userClass->save($newUser);

		}else $receiverId = $informations->receiver_id;

		if(!empty($receiverId) && !empty($actionsParams['sendmessage']['message_id'])){
			$query = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) VALUES ('.intval($actionsParams['sendmessage']['message_id']).','.intval($receiverId).','.$db->Quote($integration->componentName).','.time().',0,2)';
			$db->setQuery($query);
			$db->query();
		}
		$queueHelper->report = false;
		$queueHelper->message_id = $actionsParams['sendmessage']['message_id'];
		$queueHelper->process();
	}
}//endclass
