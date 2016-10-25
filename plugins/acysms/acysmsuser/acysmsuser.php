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
class plgAcysmsAcysmsUser extends JPlugin
{

	var $sendervalues =array();

	 function plgAcysmsAcysmsUser(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acysms'))	return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)) {
			$plugin = JPluginHelper::getPlugin('acysms', 'jevents');
			$this->params = new acysmsParameter( $plugin->params );
		}
	}




	 function onACYSMSGetTags(&$tags) {

	 	$tags['acysms'] = new stdClass();
		$tags['acysms']->name = JText::sprintf('SMS_X_USER_INFO','AcySMS');
		$db = JFactory::getDBO();
		$tableFields = acysms_getColumns('#__acysms_user');

		$tags['acysms']->content = '<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['acysms']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{acysms:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1-$k;
		}
		$tags['acysms']->content .= '</tbody></table>';

	 }


	 function onACYSMSReplaceUserTags(&$message,&$user,$send = true){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'acysms');

		foreach ($tags as $oneTag){
			if(!isset($user->acysms)){
			 	$db = JFactory::getDBO();
			 	if(!empty($user->joomla->id)){
				 	$query = 'SELECT * FROM #__acysms_user WHERE user_joomid = '.intval($user->joomla->id);
					$db->setQuery($query);
					$user->acysms = $db->loadObject();
				}
			}
		}
		foreach($tags as $oneString => $oneObject){
			$tags[$oneString] = (isset($user->acysms->{$oneObject->id}) && strlen($user->acysms->{$oneObject->id}) > 0) ? $user->acysms->{$oneObject->id} : $oneObject->default;

			if($oneObject->id == 'user_activationcode'){
				if(!empty($oneObject->subType)){
					if(is_string($tags[$oneString])) $tags[$oneString] = unserialize($tags[$oneString]);
					if(!empty($tags[$oneString][$oneObject->subType])) $tags[$oneString] = $tags[$oneString][$oneObject->subType];
					else $tags[$oneString] = 'Error subType '.$oneObject->subType.'. Use '.implode(' or ',array_keys($tags[$oneString]));
				}else{
					if(is_string($tags[$oneString]))	$tags[$oneString] = unserialize($tags[$oneString]);
					if(is_array($tags[$oneString]))	$tags[$oneString] = reset($tags[$oneString]);
				}
			}
			$helperPlugin->formatString($tags[$oneString],$oneObject);
		}
		$message->message_body = str_replace(array_keys($tags),$tags,$message->message_body);
	}





	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers','1');
		if($allowCustomerManagement && !empty($displayToCustomers) && !$app->isAdmin()) return;

		$db = JFactory::getDBO();

		$helperPlugin = ACYSMS::get('helper.plugins');

		$filter = new stdClass();
		$filter->name = JText::sprintf('SMS_INTEGRATION_FIELDS','AcySMS');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('acysmsField'))) $filters['acysmsField'] = $filter;

		$secondFilter = new stdClass();
		$secondFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS','AcySMS Statistics');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('acysmsStatisticsfield'))) $filters['acysmsStatisticsField'] = $secondFilter;
	}


	function onACYSMSDisplayFilterParams_acysmsStatisticsfield($message){
		$db = JFactory::getDBO();

		$field = array();
		$field[] = JHTML::_('select.option','',' - - - ');


		$field[] = JHTML::_('select.option','0',JText::_('SMS_STATUS_FAILED'));
		$field[] = JHTML::_('select.option','1',JText::_('SMS_STATUS_1'));
		$field[] = JHTML::_('select.option','nevertried',JText::_('SMS_NEVER_TRIED_TO_SEND'));
		$field[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('SMS_DELIVERY_STATUS'));
		$field[] = JHTML::_('select.option','2',JText::_('SMS_STATUS_2'));
		$field[] = JHTML::_('select.option','3',JText::_('SMS_STATUS_3'));
		$field[] = JHTML::_('select.option','4',JText::_('SMS_STATUS_4'));
		$field[] = JHTML::_('select.option','5',JText::_('SMS_STATUS_5'));
		$field[] = JHTML::_('select.option', '</OPTGROUP>');
		$field[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('SMS_DELIVERY_FAILURE_STATUS'));
		$field[] = JHTML::_('select.option','-1',JText::_('SMS_STATUS_M1'));
		$field[] = JHTML::_('select.option','-2',JText::_('SMS_STATUS_M2'));
		$field[] = JHTML::_('select.option','-3',JText::_('SMS_STATUS_M3'));
		$field[] = JHTML::_('select.option','-99',JText::_('SMS_STATUS_M99'));
		$field[] = JHTML::_('select.option', '</OPTGROUP>');

		$db = JFactory::getDBO();
		$db->setQuery("SELECT `message_id`,CONCAT(`message_subject`,' ( ID : ',`message_id`,' )') AS value FROM `#__acysms_message` WHERE `message_type` NOT IN ('answer') ORDER BY `message_subject` ASC ");
		$allMessages = $db->loadObjectList();
		$element = new stdClass();
		$element->message_id = 0;
		$element->value = JText::_('SMS_AT_LEAST_ONE_MESSAGE');
		array_unshift($allMessages,$element);

		$operators = ACYSMS::get('type.operators');

		$relation = array();
		$relation[] = JHTML::_('select.option','AND',JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option','OR',JText::_('SMS_OR'));

		?>
		<span id="countresult_acysmsStatisticsfield"></span>
		<?php
		for($i = 0;$i<5;$i++){
			$operators->extra = 'onchange="countresults(\'acysmsStatisticsfield\')"';
			$return = '<div id="filter'.$i.'acysmsStatisticsfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][acysms][acysmsStatisticsfield][".$i."][status]", 'onchange="countresults(\'acysmsStatisticsfield\')" class="inputbox" size="1" style=""', 'value', 'text');
			$return.= JHTML::_('select.genericlist',   $allMessages, "data[message][message_receiver][standard][acysms][acysmsStatisticsfield][".$i."][message_id]", 'onchange="countresults(\'acysmsStatisticsfield\')" class="inputbox" style="width:auto;" size="1"', 'message_id', 'value').'<br />';
	 		if($i!=4)	$return .= JHTML::_('select.genericlist',   $relation, "data[message][message_receiver][standard][acysms][acysmsStatisticsfield][".$i."][relation]", 'onchange="countresults(\'acysmsStatisticsfield\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
	 		echo  $return;
	 	}
	}

	function onACYSMSSelectData_acysmsStatisticsfield(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table))	$integration = ACYSMS::getIntegration($message->message_receiver_table);
		else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['acysms']['acysmsStatisticsfield'])) return;
		if(!isset($acyquery->join['acysmsstatsdetails'])) $acyquery->join['acysmsstatistics'] = 'LEFT JOIN #__acysms_statsdetails AS acysmsstatsdetails ON '.$integration->tableAlias.'.'.$integration->primaryField.' = acysmsstatsdetails.statsdetails_receiver_id ';
		$addCondition = '';
		$whereConditions = '';

		foreach($message->message_receiver['standard']['acysms']['acysmsStatisticsfield'] as $filterNumber => $oneFilter){
			if($oneFilter['status'] == '') continue;
			if(!empty($addCondition))	$whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])) $addCondition = $oneFilter['relation'];
			else  $addCondition = 'AND';

			if($oneFilter['status'] == 'nevertried') $whereConditions .= 'acysmsstatsdetails.statsdetails_status IS NULL';
			else{
				$whereConditions .= 'acysmsstatsdetails.statsdetails_status = '.intval($oneFilter['status']);
				if(!empty($oneFilter['message_id']))	$whereConditions .= ' AND acysmsstatsdetails.statsdetails_message_id = '.intval($oneFilter['message_id']);
			}
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}





	function onACYSMSDisplayFilterParams_acysmsField($message){
		$db = JFactory::getDBO();
		$fields = acysms_getColumns('#__acysms_user');
		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option','',' - - - ');
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option',$oneField,$oneField);
		}

		$relation = array();
		$relation[] = JHTML::_('select.option','AND',JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option','OR',JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_acysmsField"></span>
		<?php
		for($i = 0;$i<5;$i++){
			$operators->extra = 'onchange="countresults(\'acysmsField\')"';
			$return = '<div id="filter'.$i.'acyfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][acysms][acysmsField][".$i."][map]", 'onchange="countresults(\'acysmsField\')" class="inputbox" size="1"', 'value', 'text');
			$return.= ' '.$operators->display("data[message][message_receiver][standard][acysms][acysmsField][".$i."][operator]").' <input onchange="countresults(\'acysmsField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][acysms][acysmsField]['.$i.'][value]" style="width:200px" value=""></div>';
	 		if($i!=4)	$return .= JHTML::_('select.genericlist',   $relation, "data[message][message_receiver][standard][acysms][acysmsField][".$i."][relation]", 'onchange="countresults(\'acysmsField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
	 		echo  $return;
	 	}
	}

	function onACYSMSSelectData_acysmsField(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table))	$integration = ACYSMS::getIntegration($message->message_receiver_table);
		else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['acysms']['acysmsField'])) return;
		if(!isset($acyquery->join['acysmsusers']) && $integration->componentName != 'acysms' ) $acyquery->join['acysmsusers'] = 'LEFT JOIN #__acysms_user AS acysmsusers ON joomusers.id = acysmsusers.user_joomid ';
		$addCondition = '';
		$whereConditions = '';
		foreach($message->message_receiver['standard']['acysms']['acysmsField'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition))	$whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])) $addCondition = $oneFilter['relation'];
			else  $addCondition = 'AND';

			$type = '';
			$value = ACYSMS::replaceDate($oneFilter['value']);

			$whereConditions .= $acyquery->convertQuery('acysmsusers',$oneFilter['map'],$oneFilter['operator'],$value,$type);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}

}//endclass
