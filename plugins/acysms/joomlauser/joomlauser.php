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
class plgAcysmsJoomlauser extends JPlugin
{
	var $sendervalues =array();
	function plgAcysmsJoomlauser(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'joomlauser');
			$this->params = new JParameter( $plugin->params );
		}
	}
	 function onACYSMSGetTags(&$tags) {
	 	$tags['joomlauser'] = new stdClass();
		$tags['joomlauser']->name = JText::sprintf('SMS_X_USER_INFO','Joomla');
		$db = JFactory::getDBO();
		$tableFields = acysms_getColumns('#__users');
		$tags['joomlauser']->content = '<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['joomlauser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{joomlauser:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1-$k;
		}
		$tags['joomlauser']->content .= '</tbody></table>';
	 }

	 function onACYSMSReplaceUserTags(&$message,&$juser,$send = true){

	 	$db = JFactory::getDBO();

	 	$query = 'SELECT queue_paramqueue FROM '.ACYSMS::table('queue').' WHERE queue_message_id = '.intval($message->message_id);
	 	$db->setQuery($query);
	 	$paramQueue = $db->loadResult();
	 	if(!empty($paramQueue)) $paramQueue = unserialize($paramQueue);

		$match = '#(?:{|%7B)joomlauser:(.*)(?:}|%7D)#Ui';
		$variables = array('message_body');
		if(empty($message->message_body)) return;
		if(!preg_match_all($match,$message->message_body,$results)) return;
		$tags = array();
		foreach($results[0] as $i => $oneTag){
			if(isset($tags[$oneTag])) continue;
			$arguments = explode('|',strip_tags($results[1][$i]));
			$field = $arguments[0];
			unset($arguments[0]);
			$mytag = new stdClass();
			$mytag->default = '';
			if(!empty($arguments)){
				foreach($arguments as $onearg){
					$args = explode(':',$onearg);
					if(isset($args[1])){
						$mytag->$args[0] = $args[1];
					}else{
						$mytag->$args[0] = 1;
					}
				}
			}
			if($field == 'password' && !empty($paramQueue->password))	$tags[$oneTag] = base64_decode($paramQueue->password);
			else $tags[$oneTag] = (isset($juser->joomla->$field) && strlen($juser->joomla->$field) > 0) ? $juser->joomla->$field : $mytag->default;
		}
		$message->message_body = str_replace(array_keys($tags),$tags,$message->message_body);
	}
}//endclass
