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
class ACYSMSqueuemessageType{

	var $existingMessages = array();

	function ACYSMSqueuemessageType(){
		$query = 'SELECT count(queue.queue_receiver_id) as totalsub, message.message_subject, queue.queue_message_id, queue.queue_receiver_table as queueTable FROM '.ACYSMS::table('queue').' as queue';
		$query .= ' JOIN '.ACYSMS::table('message').' AS message on queue.queue_message_id = message.message_id GROUP BY queue.queue_receiver_table, queue.queue_message_id ORDER BY message.message_subject ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();

		foreach($messages as $oneMessage)
			$this->existingMessages[] = $oneMessage->queue_message_id;

		$this->values = array();
		$currentTable = '';
		foreach($messages as $oneMessage){
			if($currentTable != $oneMessage->queueTable){
				$integration = ACYSMS::getIntegration($oneMessage->queueTable);
				$this->values[] = JHTML::_('select.option',  '<OPTGROUP>', $integration->displayedName );
				$this->values[] = JHTML::_('select.option',  $oneMessage->queueTable.'.all', $integration->displayedName.' ('.JText::_('SMS_ALL').')' );
				$currentTable = $oneMessage->queueTable;
			}
			$this->values[] = JHTML::_('select.option', $oneMessage->queueTable.'.'.$oneMessage->queue_message_id, $oneMessage->message_subject.' ( '.$oneMessage->totalsub.' )' );
		}
	}
	function display($map,$value ){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" style="max-width:500px" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $value );
	}
}
