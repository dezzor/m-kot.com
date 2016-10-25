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
class ACYSMSIntegration_acysms_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__acysms_user' ;

	var $componentName = 'acysms';

	var $displayedName = 'AcySMS';

	var $primaryField = 'user_id';

	var $nameField = 'user_firstname';

	var $emailField = 'user_email';

	var $joomidField = 'user_joomid';

	var $editUserURL = 'index.php?option=com_acysms&ctrl=user&task=edit&user_id=';

	var $editUserFrontURL = 'index.php?option=com_acysms&ctrl=frontuser&task=edit&user_id=';

	var $addUserURL = 'index.php?option=com_acysms&ctrl=user&task=add';

	var $addUserFrontURL = 'index.php?option=com_acysms&ctrl=frontuser&task=add';

	var $tableAlias = 'acysmsusers';

	var $useJoomlaName = 0;

	 public function getPhoneField(){

	 	$db = JFactory::getDBO();

		$query = 'SELECT  fields_namekey AS "name", fields_namekey AS "column" FROM `#__acysms_fields` WHERE `fields_type`= "phone"';
		$db->setQuery($query);
		return $db->loadObjectList();
	 }

	public function getQueryUsers($search, $order, $filters){
		$db	= JFactory::getDBO();
		$app = JFactory::getApplication();
		$searchFields = array('acysmsusers.user_firstname','acysmsusers.user_lastname', 'acysmsusers.user_phone_number');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchFields)." LIKE $searchVal";
		}

		$selectedGroup = $app->getUserStateFromRequest("filter_group", 'filter_group',	'', 'string' );
		if(!empty($selectedGroup)) $filters[] = 'groupuser.groupuser_group_id = '.intval($selectedGroup);

		$query = 'SELECT acysmsusers.*, acysmsusers.user_id as receiver_id, CONCAT_WS(" ",acysmsusers.user_firstname,acysmsusers.user_lastname) as receiver_name, acysmsusers.user_email as receiver_email, acysmsusers.user_phone_number as receiver_phone
				FROM #__acysms_user as acysmsusers';

		if(!empty($selectedGroup)) $query .= ' JOIN #__acysms_groupuser AS groupuser ON groupuser.groupuser_user_id = acysmsusers.user_id ';

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(acysmsusers.user_id) FROM #__acysms_user as acysmsusers';
		if(!empty($selectedGroup)) $queryCount .= ' JOIN #__acysms_groupuser AS groupuser ON groupuser.groupuser_user_id = acysmsusers.user_id ';
		if(!empty($filters)){
			$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	public function getStatDetailsQuery($filters, $order, $selectedMessage){
		$db = JFactory::getDBO();
		$result = new stdClass();

		if(!empty($selectedMessage)) $filters[] = 'stats.statsdetails_message_id = '.intval($selectedMessage);
		$filters[] = 'statsdetails_receiver_table = "acysms"';

		$query = 'SELECT stats.*, message.*, acysmsusers.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, acysmsusers.user_email as receiver_email, acysmsusers.user_phone_number as receiver_phone, CONCAT_WS(" ",acysmsusers.user_firstname,acysmsusers.user_lastname) as receiver_name, stats.statsdetails_status as message_status
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__acysms_user AS acysmsusers ON stats.statsdetails_receiver_id = acysmsusers.user_id';

		$query.= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($order)){
				$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' as stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__acysms_user AS acysmsusers ON stats.statsdetails_receiver_id = acysmsusers.user_id';
		$queryCount.= ' WHERE ('.implode(') AND (',$filters).')';
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function getDashboardQuery(){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT acysmsusers.*, acysmsusers.user_id as receiver_id, CONCAT_WS(" ",acysmsusers.user_firstname,acysmsusers.user_lastname) as receiver_name, acysmsusers.user_email as receiver_email, acysmsusers.user_phone_number as receiver_phone
					FROM #__acysms_user as acysmsusers
					ORDER BY acysmsusers.user_id DESC
					LIMIT 10');
		$users10 = $db->loadObjectList();
		return $users10;
	}

	public function initQuery(&$acyquery){
		$acyquery->from = '#__acysms_user as acysmsusers ';
		$acyquery->join['joomusers'] = 'LEFT JOIN #__users as joomusers ON acysmsusers.user_joomid = joomusers.id';
		$acyquery->where[] = ' CHAR_LENGTH(acysmsusers.`user_phone_number`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acysms'))	return true;
		return false;
	}


	public function addUsersInformations(&$queueMessage){

		$db= JFactory::getDBO();
		$userId = array();
		$juserid = array();
		$acyUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `user_phone_number` as receiver_phone, CONCAT_WS(" ",user_firstname, user_lastname) as receiver_name, user_email as receiver_email
					FROM #__acysms_user
					WHERE user_id
					IN ("'.implode('","',$userId).'")';

		$db->setQuery($query);
		$acyUser = $db->loadObjectList('user_id');

		if(empty($acyUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.* FROM #__acysms_user as acysmsusers
									 JOIN #__users as joomusers
									 ON joomusers.id = acysmsusers.user_joomid
									WHERE acysmsusers.user_id IN ('.implode(',',$userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($acyUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->acysms = $acyUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->acysms->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->acysms->receiver_name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->acysms->receiver_email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->acysms->user_joomid) && !empty($joomuserArray[$queueMessage[$messageID]->acysms->user_joomid])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->acysms->user_joomid];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db= JFactory::getDBO();

		$query = 'SELECT user_id FROM #__acysms_user WHERE user_joomid IN ('.implode(',',$userIDs).') AND user_joomid > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db= JFactory::getDBO();

		$query = 'SELECT user_joomid FROM #__acysms_user WHERE user_id IN ('.implode(',',$userIDs).')';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}

	public function displayFiltersUserListing(){
		$app = JFactory::getApplication();

		$selectedGroup = $app->getUserStateFromRequest('filter_group', 'filter_group',	'', 'string' );
		$groupType = ACYSMS::get('type.group');
		$groupType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';

		if(!$app->isAdmin()){
			$groupClass = ACYSMS::get('class.group');
			$allGroups = $groupClass->getFrontendGroups();
			if(count($allGroups)>1){
				$filterGroup = JHTML::_('select.genericlist',   $allGroups, "filter_group", 'class="inputbox" size="1" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"', 'group_id', 'group_name', (int) $selectedGroup,"filter_group" );
			}else{
				$filterGroup = '<input type="hidden" name="filter_group" value="'.$selectedGroup.'"/>';
			}
		}else{
			$filterGroup = $groupType->display('filter_group', $selectedGroup);
		}

		return $filterGroup;
	}

	public function getReceiversByName($name){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT CONCAT_WS(" ",user_firstname, user_lastname) AS name, user_id AS receiverId
				FROM #__acysms_user
				WHERE user_firstname LIKE '.$db->Quote('%'.$name.'%').'
				OR  user_lastname LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
