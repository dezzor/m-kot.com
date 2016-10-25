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
class ACYSMSmessageCreatorType{

	function ACYSMSmessageCreatorType(){
		$query = 'SELECT count(distinct message_id) as totalmsg, a.name, message_id,message_userid  FROM '.ACYSMS::table('message').' JOIN #__users AS a ON message_userid = a.id';
		$query .= ' GROUP BY a.id ORDER BY message_subject ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_CREATORS') );
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->message_userid, $oneMessage->name.' ( '.$oneMessage->totalmsg.' )' );
		}
	}
	function display($map,$value ){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" style="max-width:500px" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $value );
	}
}
