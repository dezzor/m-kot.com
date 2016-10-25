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

class ACYSMSGroupCreatorType{
	function ACYSMSGroupCreatorType(){

		$db = JFactory::getDBO();

		$db->setQuery('SELECT COUNT(*) as total, group_user_id FROM #__acysms_group WHERE `group_user_id` > 0 GROUP BY group_user_id');
		$allusers = $db->loadObjectList('group_user_id');

		$allnames = array();
		if(!empty($allusers)){

			$arrayKeys = array_keys($allusers);
			JArrayHelper::toInteger($arrayKeys);

			$db->setQuery('SELECT CONCAT_WS(" ",user_firstname,user_lastname) as name, user_id FROM #__acysms_user WHERE user_id IN ('.implode(',',$arrayKeys).') ORDER BY name ASC');
			$allnames = $db->loadObjectList('user_id');
		}
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_CREATORS') );
		foreach($allnames as $userid => $oneCreator){
			$this->values[] = JHTML::_('select.option', $userid, $oneCreator->name.' ( '.$allusers[$userid]->total.' )' );
		}
	}

	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}
