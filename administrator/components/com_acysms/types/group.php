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

class ACYSMSgroupType{

	var $js = '';
	function load(){
		$query = 'SELECT group_id, group_name FROM '.ACYSMS::table('group').' ORDER BY group_ordering ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$this->values = $db->loadObjectList();
		$newElement = new stdClass();
		$newElement->group_id = 0;
		$newElement->group_name = JText::_('SMS_NO_GROUP');
		array_unshift($this->values, $newElement);
	}

	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" style="max-width:200px" size="1" '.$this->js, 'group_id', 'group_name', (int) $value );
	}
}
