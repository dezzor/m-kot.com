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
class ACYSMSmessagetypeType{

	function ACYSMSmessagetypeType(){
		$query = 'SELECT count(message_id) as totalmsg, message_type, message_id FROM '.ACYSMS::table('message').' WHERE message_type <> "activation_optin" AND message_type <> "answer" AND message_type <> "conversation"';
		$query .= ' GROUP BY message_type ORDER BY message_subject ASC ';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_TYPES') );

		$messageStandardPresent = false;
		foreach($messages as $oneMessage){
			if($oneMessage->message_type == 'standard') $messageStandardPresent = true;
			$this->values[] = JHTML::_('select.option', $oneMessage->message_type, JText::_('SMS_'.strtoupper($oneMessage->message_type)).' ( '.$oneMessage->totalmsg.' )');
		}

		if(!$messageStandardPresent)	$this->values[] = JHTML::_('select.option', 'standard', JText::_('SMS_STANDARD').' ( 0 )' );
	}

	function display($map,$value ){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" style="max-width:500px" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $value );
	}
}
