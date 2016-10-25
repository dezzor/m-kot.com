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
class ACYSMSanswerreceiverType{

	function ACYSMSanswerreceiverType(){
		$config = ACYSMS::config();

		$query = 'SELECT count(answer_to) as totalmsg, answer_to FROM '.ACYSMS::table('answer');
		$query .= ' WHERE `answer_to` IS NOT NULL OR `answer_to` <> "" GROUP BY answer_to ';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$answerReceiverPhoneNumber = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_SENDERS') );
		foreach($answerReceiverPhoneNumber as $onePhoneNumber){
			$this->values[] = JHTML::_('select.option', $onePhoneNumber->answer_to, $onePhoneNumber->answer_to.' ( '.$onePhoneNumber->totalmsg.' )');
		}
	}
	function display($map,$value ){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="max-width:500px" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $value );
	}
}
