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
class ACYSMSanswerClass extends ACYSMSClass{

	var $tables = array('answer'=>'answer_id');
	var $pkey = 'answer_id';


	function get($id,$default = null){
		$this->database->setQuery('SELECT * FROM #__acysms_answer WHERE answer_id = '.intval($id).' LIMIT 1');
		$answerTrigger = $this->database->loadObject();
		return $answerTrigger;
	}


	function addAnswer($apiAnswer){

		$integration = ACYSMS::getIntegration();
		$db = JFactory::getDBO();
		$phoneHelper = ACYSMS::get('helper.phone');

		if(empty($apiAnswer->answer_date)) $apiAnswer->answer_date = time();
		if(!is_numeric($apiAnswer->answer_date)) $apiAnswer->answer_date = strtotime($apiAnswer->answer_date);

		if(!empty($apiAnswer->answer_sms_id) && empty($apiAnswer->answer_receiver_id)){
			$query = 'SELECT statsdetails_message_id, statsdetails_receiver_id, statsdetails_receiver_table FROM #__acysms_statsdetails WHERE statsdetails_sms_id = '.$db->Quote($apiAnswer->answer_sms_id);
			$db->setQuery($query);
			$receiver = $db->loadObject();
			if(!empty($receiver)){
				$apiAnswer->answer_message_id = $receiver->statsdetails_message_id;
				$apiAnswer->answer_receiver_id = $receiver->statsdetails_receiver_id;
				$apiAnswer->answer_receiver_table = $receiver->statsdetails_receiver_table;
			}
		}
		if(!empty($apiAnswer->answer_from) && empty($apiAnswer->answer_message_id) && $phoneHelper->getValidNum($apiAnswer->answer_from) != false){

			$informations = $integration->getInformationsByPhoneNumber($phoneHelper->getValidNum($apiAnswer->answer_from));

			if(!empty($informations)){
				$apiAnswer->answer_receiver_id = $informations->receiver_id;
				$apiAnswer->answer_receiver_table = $integration->componentName;
			}
		}
		return $this->save($apiAnswer);
	}

	function processAnswerTriggers($answer_id){

		$db = JFactory::getDBO();

		$answer = $this->get($answer_id);
		$query = 'SELECT * FROM '.ACYSMS::table('answertrigger').' WHERE answertrigger_publish = 1 ORDER BY answertrigger_ordering ';
		$db->setQuery($query);
		$answerTriggerList = $db->loadObjectList();

		if(empty($answerTriggerList)) return;

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		foreach($answerTriggerList as $oneAnswerTrigger){
			$triggers = unserialize($oneAnswerTrigger->answertrigger_triggers);
			if(!empty($triggers['selected'])) $selectedTrigger = $triggers['selected'];
			else if(empty($triggers['attachment'])) continue;

			if(!empty($selectedTrigger) && !empty($triggers[$selectedTrigger])) {
				if($selectedTrigger == 'regex') $regex = '#'.$triggers[$selectedTrigger].'#is';
				else if($selectedTrigger == 'word') $regex = '#^'.preg_quote($triggers[$selectedTrigger],'#').'$#is';

				if(!preg_match($regex,$answer->answer_body,$result))	continue;
			}

			if(!empty($triggers['attachment']) && $triggers['attachment'] == 'contains' && empty($answer->answer_attachment)) continue;

			$actions = unserialize($oneAnswerTrigger->answertrigger_actions);

			if(empty($actions['selected'])) break;

			foreach($actions['selected'] as $oneAction)	$dispatcher->trigger('onACYSMSTriggerActions_'.$oneAction,array($actions, $answer));


		}
	}
}
