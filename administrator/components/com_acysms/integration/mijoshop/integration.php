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

class ACYSMSIntegration_mijoshop_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__mijoshop_customer' ;

	var $componentName = 'mijoshop';

	var $displayedName = 'MijoShop';

	var $primaryField = 'customer_id';

	var $nameField = 'firstname';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php??option=com_mijoshop&route=sale/customer/update&customer_id=';

	var $addUserURL = 'index.php?option=com_mijoshop&route=sale/customer/insert';

	var $tableAlias = 'mijoshopusers';

	var $useJoomlaName = 0;


	public function getPhoneField(){

 		$tableFields = array();
 		$oneField = new stdClass();
 		$oneField->name = $oneField->column = 'telephone';
		$tableFields[] = $oneField;

		return $tableFields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db	= JFactory::getDBO();
		$config = ACYSMS::config();
		$user = JFactory::getUser();
		$searchFields = array('mijoshopusers.firstname','mijoshopusers.lastname','mijoshopusers.email');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT mijoshopusers.*, mijoshopusers.customer_id as receiver_id, CONCAT_WS(" ",mijoshopusers.firstname,mijoshopusers.lastname) as receiver_name, mijoshopusers.email as receiver_email, mijoshopusers.'.$config->get('mijoshop_field').' as receiver_phone
				FROM #__mijoshop_customer as mijoshopusers
				JOIN #__mijoshop_juser_ocustomer_map as mijoshoprelation
				ON mijoshopusers.customer_id = mijoshoprelation.ocustomer_id
				JOIN '.ACYSMS::table('users',false).' as joomusers
				ON joomusers.id = mijoshoprelation.juser_id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(mijoshopusers.customer_id) FROM #__mijoshop_customer as mijoshopusers';
		if(!empty($filters)){
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
		$filters[] = 'statsdetails_receiver_table = "mijoshop"';

		$query = 'SELECT stats.*, message.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, CONCAT_WS(" ",mijoshopusers.firstname,mijoshopusers.lastname) as receiver_name, mijoshopusers.email as receiver_email, mijoshopusers.'.$config->get('mijoshop_field').' as receiver_phone, stats.statsdetails_status as message_status
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN #__mijoshop_customer as mijoshopusers ON stats.statsdetails_receiver_id = mijoshopusers.customer_id
				LEFT JOIN '.ACYSMS::table('users',false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id ';

		$query .= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}


		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN #__mijoshop_customer as mijoshopusers ON stats.statsdetails_receiver_id = mijoshopusers.customer_id
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
		$query = 'SELECT mijoshopusers.*, mijoshopusers.customer_id as receiver_id, CONCAT_WS(" ",mijoshopusers.firstname,mijoshopusers.lastname) as receiver_name, mijoshopusers.email as receiver_email, mijoshopusers.'.$config->get('mijoshop_field').' as receiver_phone
				FROM #__mijoshop_customer as mijoshopusers
				JOIN #__mijoshop_juser_ocustomer_map as mijoshoprelation ON mijoshopusers.customer_id = mijoshoprelation.ocustomer_id
				JOIN '.ACYSMS::table('users',false).' as joomusers ON joomusers.id = mijoshoprelation.juser_id
				ORDER BY mijoshopusers.customer_id DESC
				LIMIT 10';
		$db->setQuery($query);

		$users10 = $db->loadObjectList();
		return $users10;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users as joomusers ';
		$acyquery->join['mijoshoprelation'] = 'JOIN #__mijoshop_juser_ocustomer_map as mijoshoprelation ON mijoshoprelation.juser_id = joomusers.id ';
		$acyquery->join['mijoshopusers'] = 'JOIN #__mijoshop_customer as mijoshopusers ON mijoshoprelation.ocustomer_id = mijoshopusers.customer_id ';
		$acyquery->where[] =  'joomusers.block=0 AND CHAR_LENGTH(mijoshopusers.`'.$config->get('mijoshop_field').'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_mijoshop')) return true;
		return false;
	}

	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db= JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$mijoShopUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.$config->get('mijoshop_field').'` as receiver_phone,  CONCAT_WS(" ",firstname, lastname) as receiver_name FROM #__mijoshop_customer WHERE customer_id IN ("'.implode('","',$userId).'")';
		$db->setQuery($query);
		$mijoShopUser = $db->loadObjectList('customer_id');

		if(empty($mijoShopUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.*, mijoshopusers.customer_id FROM #__mijoshop_customer as mijoshopusers
									 JOIN #__mijoshop_juser_ocustomer_map as mijoshoprelation ON mijoshopusers.customer_id = mijoshoprelation.ocustomer_id
									 JOIN #__users as joomusers
									 ON joomusers.id = mijoshoprelation.juser_id
									 WHERE mijoshopusers.customer_id  IN ('.implode(',',$userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('customer_id');

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;

			if(empty($mijoShopUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->mijoShop = $mijoShopUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->mijoShop->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->mijoShop->receiver_name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->mijoShop->email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->mijoShop->customer_id) && !empty($joomuserArray[$queueMessage[$messageID]->mijoShop->customer_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->mijoShop->customer_id];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT ocustomer_id
				 FROM #__mijoshop_juser_ocustomer_map 
				WHERE juser_id IN ('.implode(',',$userIDs).') AND ocustomer_id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db= JFactory::getDBO();

		$query = 'SELECT juser_id
				 FROM #__mijoshop_juser_ocustomer_map 
				 WHERE ocustomer_id IN ('.implode(',',$userIDs).')';
		$db->setQuery($query);

		return acysms_loadResultArray($db);
	}

	public function getReceiversByName($name){
		if(empty($name)) return;
		$db = JFactory::getDBO();

		$query = 'SELECT CONCAT_WS(" ",mijoshopusers.firstname,mijoshopusers.lastname) AS name, mijoshopusers.customer_id AS receiverId
				FROM #__mijoshop_customer as mijoshopusers
				JOIN #__mijoshop_juser_ocustomer_map as mijoshoprelation
				ON mijoshopusers.customer_id = mijoshoprelation.ocustomer_id
				JOIN '.ACYSMS::table('users',false).' as joomusers
				ON joomusers.id = mijoshoprelation.juser_id
				WHERE mijoshopusers.firstname LIKE '.$db->Quote('%'.$name.'%').'
				OR mijoshopusers.lastname LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
