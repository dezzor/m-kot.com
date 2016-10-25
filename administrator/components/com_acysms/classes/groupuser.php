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

class ACYSMSgroupuserClass extends acysmsClass{


	var $gid;

	function updateSubscription($user_id,$groups){

		$result = true;
		$time = time();

		foreach($groups as $status => $groupids){
			if(empty($groupids)) continue;

			JPluginHelper::importPlugin('acysms');
			$dispatcher = JDispatcher::getInstance();

			if($status == '-1') $column = 'groupuser_unsubdate';
			else $column = 'groupuser_subdate';

			JArrayHelper::toInteger($groupids);

			$query = 'UPDATE '.ACYSMS::table('groupuser').' SET `groupuser_status` = '.intval($status).', '.$column.' = '.time().'  WHERE groupuser_user_id = '.intval($user_id).' AND groupuser_group_id IN ('.implode(',',$groupids).')';
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
			if($status == '1')
				$dispatcher->trigger('onAcySMSSubscribe',array($user_id,$groups));
			if($status == '-1')
				$dispatcher->trigger('onAcySMSUnsubscribe',array($user_id,$groups));
		}

		return $result;
	}

	function removeSubscription($user_id,$groupids){
		JArrayHelper::toInteger($groupids);
		$query = 'DELETE FROM '.ACYSMS::table('groupuser').' WHERE groupuser_user_id = '.intval($user_id).' AND groupuser_group_id IN ('.implode(',',$groupids).')';
		$this->database->setQuery($query);
		$this->database->query();
		return true;

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAcySMSUnsubscribe',array($user_id,$groupids));

	}

	function removeAllSubscriptions($user_id){

		$query = 'DELETE FROM '.ACYSMS::table('groupuser').' WHERE groupuser_user_id = '.intval($user_id);
		$this->database->setQuery($query);
		$this->database->query();
		return true;

	}

	function addSubscription($user_id,$groups){
		$app = JFactory::getApplication();

		$my = JFactory::getUser();

		$result = true;
		$time = time();
		$user_id = intval($user_id);

		foreach($groups as $status => $groupids){
			$status = intval($status);
			JArrayHelper::toInteger($groupids);

			if($status == '-1') $column = 'groupuser_unsubdate';
			else $column = 'groupuser_subdate';

			$values = array();
			foreach($groupids as $groupid){
				if(empty($groupid)) continue;
				$values[] = intval($groupid).','.$user_id.','.$status.','.time();
			}

			if(empty($values)) continue;

			$query = 'INSERT INTO '.ACYSMS::table('groupuser').' (groupuser_group_id, groupuser_user_id, groupuser_status,'.$column.') VALUES ('.implode('),(',$values).')';
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
		}

		$dispatcher = JDispatcher::getInstance();
		$resultsTrigger = $dispatcher->trigger('onAcySMSSubscribe',array($user_id,$groups));

		return $result;
	}

	function getSubscription($user_id){
		$query = 'SELECT * FROM '.ACYSMS::table('groupuser').' as groupuser LEFT JOIN '.ACYSMS::table('group').' as groups on groupuser.groupuser_group_id = groups.group_id WHERE groupuser.groupuser_user_id = '.intval($user_id).' ORDER BY group.group_ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectgroup('group_id');
	}

	function getSubscriptionString($user_id){
		$usersubscription = $this->getSubscription($user_id);
		$subscriptionString = '';
		if(!empty($usersubscription)){
			$subscriptionString = '<ul>';
			foreach($usersubscription as $onesub){
				$status = ($onesub->status == 1) ? JText::_('SUBSCRIBED') : (($onesub->status == -1) ? JText::_('UNSUBSCRIBED') : JText::_('PENDING_SUBSCRIPTION'));
				$subscriptionString .= '<li>['.$onesub->groupid.'] '.$onesub->name.' : '.$status.'</li>';
			}
			$subscriptionString .= '</ul>';
		}

		return $subscriptionString;
	}
}

