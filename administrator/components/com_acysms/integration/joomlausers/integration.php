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

class ACYSMSIntegration_joomlausers_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__users' ;

	var $componentName = 'joomlausers';

	var $displayedName = 'Joomla User';

	var $primaryField = 'id';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'id';

	var $editUserURL = 'index.php?option=com_users&task=user.edit&id=';

	var $addUserURL = 'index.php?option=com_users&view=user&layout=edit';

	var $tableAlias = 'joomusers';

	var $useJoomlaName = 0;

	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT enabled FROM #__extensions WHERE type="plugin"	 AND element="profile" AND folder="user"';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$result = $db->loadResult();
		if(!$result) return array();

		$query = 'SELECT SUBSTRING(profile_key, 9) AS "name", SUBSTRING(profile_key, 9) AS "column"
					FROM #__user_profiles
					WHERE  profile_key LIKE "profile.%"
					ORDER BY ordering';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getQueryUsers($search, $order, $filters){
		$db	= JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('joomusers.name','joomusers.email');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT joomusers.*, joomusers.id as receiver_id, joomusers.name as receiver_name, joomusers.email as receiver_email, joomuserprofile.profile_value as receiver_phone
				FROM '.ACYSMS::table('users',false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';
		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (',$filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(joomusers.id)
						FROM '.ACYSMS::table('users',false).' AS joomusers
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';
		if(!empty($filters)){
			$queryCount .= ' AND ('.implode(') AND (',$filters).')';
		}
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	public function getStatDetailsQuery($filters, $order, $selectedMessage){
			$db   = JFactory::getDBO();
			$result = new stdClass();
			$config = ACYSMS::config();

			if(!empty($selectedMessage)) $filters[] = 'stats.statsdetails_message_id = '.intval($selectedMessage);
			$filters[] = 'statsdetails_receiver_table = "joomlausers"';

			$query = 'SELECT stats.*, message.*, joomusers.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.email as receiver_email, joomusers.name as receiver_name, joomuserprofile.profile_key as receiver_phone, stats.statsdetails_status as message_status
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						JOIN '.ACYSMS::table('users',false).' AS joomusers	ON stats.statsdetails_receiver_id = joomusers.id
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';



			$query.= ' AND ('.implode(') AND (',$filters).')';
			if(!empty($order)){
				$query .= ' ORDER BY '.$order->value.' '.$order->dir;
			}

			$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						JOIN '.ACYSMS::table('users',false).' AS joomusers	ON stats.statsdetails_receiver_id = joomusers.id
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';

			$queryCount.= ' AND ('.implode(') AND (',$filters).')';


			$db->setQuery($queryCount);
			$result->count = $db->loadResult();
			$result->query = $query;
			return $result;
		}

	public function getDashboardQuery(){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$db->setQuery('SELECT joomusers.*, joomusers.id as receiver_id, joomusers.email as receiver_email, joomuserprofile.profile_value as receiver_phone,
					 joomusers.name as receiver_name
					FROM '.ACYSMS::table('users',false).' AS joomusers
					JOIN #__user_profiles AS joomuserprofile	ON joomusers.id = joomuserprofile.user_id
					WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"
					LIMIT 10');
		$users10 = $db->loadObjectList();
		return $users10;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users AS joomusers';
		$acyquery->join['joomuserprofile'] = 'LEFT JOIN #__user_profiles AS joomuserprofile ON joomusers.id = joomuserprofile.user_id';
		$acyquery->where[] = 'joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';
		$acyquery->where[] = 'joomusers.block=0 AND CHAR_LENGTH(joomuserprofile.profile_key) > 3';
		return $acyquery;
	}

	public function isPresent(){
			if(!ACYSMS_J16){
				return false;
			}
			return true;
	}


	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db= JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$akeebaUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.id as receiver_id, joomuserprofile.profile_value as receiver_phone, joomusers.*, joomuserprofile.profile_key
				FROM '.ACYSMS::table('users',false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE profile_key = "profile.'.$config->get('joomlausers_field').'" AND joomusers.id IN ('.implode(',',$userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');

		if(empty($joomuserArray)) return false;


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($joomuserArray[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->joomlauser = $joomuserArray[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->joomla = $joomuserArray[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->joomlauser->receiver_phone;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomlauser->email;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->joomlauser->name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

		}
	}

	public function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$config = ACYSMS::config();
		$integrationField = $config->get($this->componentName.'_field');

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, joomusers.name as receiver_name, joomuserprofile.profile_value as receiver_phone, joomusers.id as receiver_id
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				JOIN '.ACYSMS::table('users',false).' AS joomusers	ON queue.queue_receiver_id = joomusers.id
				JOIN #__user_profiles AS joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';

		$result->query = $query;

		$queryCount = 'SELECT COUNT(queue.queue_message_id)
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				JOIN '.ACYSMS::table('users',false).' AS joomusers	ON queue.queue_receiver_id = joomusers.id
				JOIN #__user_profiles AS joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"';
		$result->queryCount = $queryCount;

		return $result;
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db= JFactory::getDBO();

		$query = 'SELECT id FROM #__users WHERE id IN ('.implode(',',$userIDs).') AND id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();

		return $userIDs;
	}

	public function getInformationsByPhoneNumber($phoneNumber){
		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();

		$integrationPhoneField = $config->get($this->componentName.'_field');

		$countryCode = $phoneHelper->getCountryCode($phoneNumber);

		$phoneNumberToSearch = str_replace('+'.$countryCode,'',$phoneNumber);

		if(!empty($integrationPhoneField)){
			$db->setQuery('SELECT joomusers.id as receiver_id, joomusers.name as receiver_name, joomuserprofile.profile_value as receiver_phone
				FROM '.ACYSMS::table('users',false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "profile.'.$integrationPhoneField.'"
				AND joomuserprofile.profile_value = '.$db->Quote($phoneNumberToSearch).' OR joomuserprofile.profile_value LIKE '.$db->Quote('%'.$phoneNumberToSearch));

			$informations = $db->loadObject();
			return $informations;
		}
	}

	public function getReceiversByName($name){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$query = 'SELECT joomusers.name AS name, joomusers.id AS receiverId
				FROM '.ACYSMS::table('users',false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE name LIKE '.$db->Quote('%'.$name.'%').'
				AND joomuserprofile.profile_key = "profile.'.$config->get('joomlausers_field').'"
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

}
