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

class ACYSMSmessageType{

	function ACYSMSmessageType(){
		$query = 'SELECT count(distinct answer_id) as totalmsg, message_subject, message_id  FROM '.ACYSMS::table('message').' as a';
		$query .= ' INNER JOIN '.ACYSMS::table('answer').' as b on b.answer_message_id = a.message_id';
		$query .= ' WHERE message_type <> "activation_optin" && message_type <> "answer"';
		$query .= ' GROUP BY message_id ORDER BY message_id ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_MESSAGES') );
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->message_id, $oneMessage->message_subject.' ( '.$oneMessage->totalmsg.' )' );
		}
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" style="max-width:500px" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $value );
	}
}
