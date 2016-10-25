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

class ACYSMSIntegration_redshop_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__redshop_users_info' ;

	var $componentName = 'redshop';

	var $displayedName = 'RedShop';

	var $primaryField = 'users_info_id';

	var $nameField = 'firstname';

	var $emailField = 'user_email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?option=com_redshop&view=user_detail&task=edit&cid[]=';

	var $addUserURL = 'index.php?option=com_redshop&view=user_detail';

	var $tableAlias = 'redshopusers';

	var $useJoomlaName = 0;


	public function getPhoneField(){
		$tableFields = array();
 		$oneField = new stdClass();
 		$oneField->name = $oneField->column = 'phone';
		$tableFields[] = $oneField;

		return $tableFields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db	= JFactory::getDBO();
		$config = ACYSMS::config();
		$user = JFactory::getUser();
		$searchFields = array('redshopusers.firstname','redshopusers.lastname','redshopusers.user_email');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT redshopusers.*, redshopusers.users_info_id as receiver_id, CONCAT_WS(" ",redshopusers.firstname,redshopusers.lastname) as receiver_name, redshopusers.user_email as receiver_email, redshopusers.'.$config->get('redshop_field').' as receiver_phone
				FROM #__redshop_users_info as redshopusers
				JOIN '.ACYSMS::table('users',false).' as joomusers
				ON joomusers.id = redshopusers.user_id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(redshopusers.users_info_id) FROM #__redshop_users_info as redshopusers';
		if(!empty($filters)){
			$queryCount .= ' LEFT JOIN '.ACYSMS::table('users',false).' as joomusers ON redshopusers.user_id = joomusers.id';
			$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	public function getStatDetailsQuery($filters, $order, $selectedMessage){
		$db	= JFactory::getDBO();
		$result = new stdClass();
		$config = ACYSMS::config();

		if(!empty($selectedMessage)) $filters[] = 'stats.statsdetails_message_id = '.intval($selectedMessage);
		$filters[] = 'statsdetails_receiver_table = "redshop"';

		$query = 'SELECT stats.*, message.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, CONCAT_WS(" ",redshopusers.firstname,redshopusers.lastname) as receiver_name, redshopusers.user_email as receiver_email, redshopusers.'.$config->get('redshop_field').' as receiver_phone, stats.statsdetails_status as message_status
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN #__redshop_users_info AS redshopusers ON stats.statsdetails_receiver_id = redshopusers.users_info_id
				LEFT JOIN '.ACYSMS::table('users',false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id ';

		$query .= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($order)){
				$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}


		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN #__redshop_users_info AS redshopusers ON stats.statsdetails_receiver_id = redshopusers.users_info_id
				LEFT JOIN '.ACYSMS::table('users',false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id ';

		$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function getDashboardQuery(){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$db->setQuery('SELECT redshopusers.*, redshopusers.users_info_id as receiver_id, redshopusers.user_email as receiver_email, CONCAT_WS(" ",redshopusers.firstname,redshopusers.lastname) as receiver_name, redshopusers.'.$config->get('redshop_field').' as receiver_phone
				FROM #__redshop_users_info AS redshopusers
				LEFT JOIN '.ACYSMS::table('users',false).' AS joomusers
				ON redshopusers.user_id = joomusers.id
				ORDER BY redshopusers.users_info_id DESC LIMIT 10');
		$users10 = $db->loadObjectList();
		return $users10;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users as joomusers ';
		$acyquery->join['redshopusers'] = 'JOIN #__redshop_users_info as redshopusers ON joomusers.id = redshopusers.user_id ';
		$acyquery->where[] =  'joomusers.block=0 AND CHAR_LENGTH(redshopusers.`'.$config->get('redshop_field').'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_redshop')) return true;
		return false;
	}

	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db= JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$RedShopUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}
		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.$config->get('redshop_field').'` as receiver_phone,  CONCAT_WS(" ",firstname, lastname) as receiver_name FROM #__redshop_users_info WHERE users_info_id IN ("'.implode('","',$userId).'")';
		$db->setQuery($query);
		$RedShopUser = $db->loadObjectList('users_info_id');

		if(empty($RedShopUser)) return false;
		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.* FROM #__redshop_users_info as redshopusers
									 JOIN #__users as joomusers
									 ON joomusers.id = redshopusers.user_id
									WHERE redshopusers.users_info_id IN ('.implode(',',$userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;

			if(empty($RedShopUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->redShop = $RedShopUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->redShop->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->redShop->receiver_name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->redShop->user_email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->redShop->user_id) && !empty($joomuserArray[$queueMessage[$messageID]->redShop->user_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->redShop->user_id];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db= JFactory::getDBO();

		$query = 'SELECT users_info_id FROM #__redshop_users_info WHERE user_id IN ('.implode(',',$userIDs).') AND users_info_id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db= JFactory::getDBO();

		$query = 'SELECT user_id FROM #__redshop_users_info WHERE users_info_id  IN ('.implode(',',$userIDs).')';
		$db->setQuery($query);

		return acysms_loadResultArray($db);
	}

	public function getReceiversByName($name){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT CONCAT_WS(" ", redshopusers.firstname, redshopusers.lastname) AS name, redshopusers.users_info_id AS receiverId
				FROM #__redshop_users_info AS redshopusers
				JOIN '.ACYSMS::table('users',false).' as joomusers
				ON joomusers.id = redshopusers.user_id
				WHERE redshopusers.firstname LIKE '.$db->Quote('%'.$name.'%').'
				OR redshopusers.lastname LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
