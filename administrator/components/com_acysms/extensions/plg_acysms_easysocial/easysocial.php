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
class plgAcysmsEasysocial extends JPlugin
{

	var $sendervalues = array();

	function plgAcysmsEasysocial(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_easysocial'))	return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)) {
			$plugin = JPluginHelper::getPlugin('acysms', 'jevents');
			$this->params = new acysmsParameter( $plugin->params );
		}
	}

	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers','1');
		if($allowCustomerManagement && !empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$js = "function displayFieldsFilter(fct, element, num, extra){";
					$ctrl = 'message';
					if(!$app->isAdmin()) $ctrl = 'frontmessage';
					$js .= "
					try{
						var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilter&fct='+fct+'&num='+num+'&'+extra,{
							method: 'get',
							update: document.getElementById(element)
						}).request();

					}catch(err){
						new Request({
							url:'index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilter&fct='+fct+'&num='+num+'&'+extra,
							method: 'get',
							onSuccess: function(responseText, responseXML) {
								document.getElementById(element).innerHTML = responseText;
							}
						}).send();
					}
				}";
				$ctrl = 'message';
				if(!$app->isAdmin()) $ctrl = 'frontmessage';

				$js .= "function displayFieldsFilterValues(num, map){
					var operator = document.getElementById('easysocialoperator_'+num).value;
					try{
						var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilterValues&map='+map+'&num='+num+'&operator='+operator+'&fieldsIntegration=easysocialField',{
							method: 'get',
							update: document.getElementById('valueToChange_'+num+'_value')
						}).request();

					}catch(err){
						new Request({
							url:'index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilterValues&map='+map+'&num='+num+'&operator='+operator+'&fieldsIntegration=easysocialField',
							method: 'get',
							onSuccess: function(responseText, responseXML) {
								document.getElementById('valueToChange_'+num+'_value').innerHTML = responseText;
							}
						}).send();
					}
				}";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$helperPlugin = ACYSMS::get('helper.plugins');
		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS','EasySocial');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('easysocialfield'))) $filters['easysocialfield'] = $newFilter;
	}





	 function onACYSMSGetTags(&$tags) {

		$lang = JFactory::getLanguage();
		$return = $lang->load('com_easysocial',JPATH_ADMINISTRATOR);
		$easyProfiles = array();
		$easyProfileData = array();
		$db = JFactory::getDBO();
		$previousProfile = '';
		$finalContent = '';
		$content = array();

		 $tags['easysocial'] = new stdClass();
		$tags['easysocial']->name = JText::sprintf('SMS_X_USER_INFO','EasySocial');

		$query = 'SELECT socialfields.title AS "name", socialfields.id AS "id", socialprofiles.id AS "profileid", socialprofiles.title AS "profile"
				FROM #__social_fields AS socialfields
				JOIN #__social_fields_steps AS socialfieldssteps ON socialfields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE socialfields.unique_key NOT LIKE "'.implode('%" AND socialfields.unique_key NOT LIKE "', array("JOOMLA_","HEADER","SEPARATOR","TERMS","COVER","AVATAR")).'%"
				ORDER BY socialprofiles.title
				';

		$db->setQuery($query);
		$easySocialFields = $db->loadObjectList();
		$k = 0;

		foreach($easySocialFields as $oneField){
			if(empty($oneField->profile) || empty($oneField->profileid)) continue;
			if(!in_array($oneField->profile,$easyProfiles)) $easyProfiles[$oneField->profileid] = $oneField->profile;

			$style = 'style="display:none"';
			if(empty($previousProfile)){
				$previousProfile = $oneField->profileid;
				$style = 'style="display:table"';
			}

			if(empty($content[$oneField->profileid]))	$content[$oneField->profileid] = '<table class="adminlist table table-striped table-hover" id="easySocialId_'.$oneField->profileid.'" cellpadding="1" width="100%" '.$style.'><tbody>';
			$content[$oneField->profileid] .= '<tr style="cursor:pointer" onclick="insertTag(\'{easysocial:'.JText::_($oneField->id).'}\')" class="row'.$k.'"><td>'.JText::_($oneField->name).'</td></tr>';

			if($oneField->profileid != $previousProfile){
				if(!empty($content[$oneField->profileid]))	$content[$previousProfile] .= '</tbody></table>';
				$previousProfile = $oneField->profileid;
			}
			$k = 1-$k;
		}
		$content[$previousProfile] .= '</tbody></table>';

		foreach($easyProfiles as $oneProfileId => $oneProfileTitle) $easyProfileData[] = JHTML::_('select.option', $oneProfileId, $oneProfileTitle);

		$profileSelection =  JHTML::_('select.genericlist',   $easyProfileData, '', 'onchange="(displayTags(this.value))"', 'value', 'text');

		$script = '
				function displayTags(profileId){
					tables = document.getElementById("easySocialDiv").getElementsByTagName("table");
					for(var i=0; i < tables.length; i++) {
						document.getElementById(tables[i].id).style.display = "none";
					}
					document.getElementById("easySocialId_"+profileId).style.display = "table";
				}';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		$tags['easysocial']->content = $profileSelection;
		$tags['easysocial']->content .= '<div id="easySocialDiv">'.implode($content,'').'</div>';
	 }


	 function onACYSMSReplaceUserTags(&$message,&$user,$send = true){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$app = JFactory::getApplication();
		if(empty($user)) return;

		if(!file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_easysocial'.DS.'includes'.DS.'foundry.php'))
		{
			$app->enqueueMessage('You must update your EasySocial component to include user fields','warning');
			return;
		}

		include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_easysocial'.DS.'includes'.DS.'foundry.php');

		if(empty($user->joomla)) return;
		$receiver = Foundry::user($user->joomla->id);

		$db = JFactory::getDBO();
		$lang = JFactory::getLanguage();
		$lang->load('com_easysocial', JPATH_SITE);

		$messageTags = $helperPlugin->extractTags($message, 'easysocial');
		if(empty($messageTags)) return;

		if(empty($user->joomla->id)){
			$app->enqueueMessage('No user id found','warning');
			return;
		}

		foreach($messageTags as $oneTag => $parameter)
		{
			$db->setQuery('SELECT unique_key FROM #__social_fields WHERE id = '.intval($parameter->id));
			$uniqueKey = $db->loadResult();
			$fieldValue = $receiver->getFieldValue($uniqueKey);

			if(empty($fieldValue)){
				$tags[$oneTag] = '';
				continue;
			}

			if(is_string($fieldValue)){
				$tags[$oneTag] = $fieldValue;
				continue;
			}

			if(!empty($fieldValue->value) && is_string($fieldValue->value)){
				if(strstr($fieldValue->unique_key, 'BOOLEAN')){
					$tags[$oneTag] = JText::_(empty($fieldValue->value) ? 'JOOMEXT_NO' : 'JOOMEXT_YES');
				}elseif(strstr($fieldValue->unique_key, 'RELATIONSHIP')){
					$tags[$oneTag] = json_decode($fieldValue->value)->type;
				}elseif(strstr($fieldValue->unique_key, 'COUNTRY')){
					$tags[$oneTag] = implode(', ', json_decode($fieldValue->value));
				}else{
					$tags[$oneTag] = $fieldValue->value;
				}
			}elseif(is_object($fieldValue->value)){
				$arrayValue = (array) $fieldValue->value;
				if(!empty($arrayValue['day']))
					$tags[$oneTag] = ACYSMS::getDate(ACYSMS::getTime($arrayValue['year'].'-'.$arrayValue['month'].'-'.$arrayValue['day'].' 00:00:00'), JText::_('DATE_FORMAT_LC'));
				elseif(!empty($arrayValue['address1']))
					$tags[$oneTag] = $arrayValue['address1'].', '.$arrayValue['zip'].' '.$arrayValue['city'].', '.$arrayValue['country'];
				elseif(!empty($arrayValue['text']))
					$tags[$oneTag] = JText::_($arrayValue['text']);
				else
					$tags[$oneTag] = implode(', ', $arrayValue);
			}elseif(is_array($fieldValue->value)){
				$tags[$oneTag] = implode(', ', $fieldValue->value);
			}else{
				$tags[$oneTag] = '';
			}
		}
		if(!empty($tags))
		{
			$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
		}
	}







	function onACYSMSDisplayFilterParams_easysocialField($message){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title FROM #__social_profiles');
		$allProfiles = $db->loadObjectList();
		$profiles = array();
		$profiles[] = JHTML::_('select.option', 0, '- - -');
		foreach($allProfiles as $oneProfile)
		{
			$profiles[] = JHTML::_('select.option', $oneProfile->id, JText::_($oneProfile->title));
		}

		$relation = array();
		$relation[] = JHTML::_('select.option','AND',JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option','OR',JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');
		?>
		<span id="countresult_easysocialField"></span>
		<?php

		for($i = 0;$i<5;$i++){
			$jsOnChange = "displayFieldsFilter('displayFields_easysocialField', 'maptoChange_".$i."', $i,'profile='+document.getElementById('selectedProfile_".$i."').value); ";
			$return = '<div id="filter_'.$i.'_easysocialfields">'.JHTML::_('select.genericlist', $profiles, "data[message][message_receiver][standard][easysocial][easysocialfield][".$i."][profile]", 'onchange="'.$jsOnChange.'"countresults(\'easysocialField\')" class="inputbox" size="1"', 'value', 'text', '', 'selectedProfile_'.$i);
			$return .= '<span id="maptoChange_'.$i.'"><input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$i.'][map]" style="width:200px" value="" id="filter'.$i.'easysocialfieldsmap"/></span>';
			$operators->extra = 'onchange="displayFieldsFilterValues('.$i.', document.getElementById(\'easysocialmap_'.$i.'\').value);setTimeout(function(){countresults(\'easysocialField\');}, 1000);"';
			$operators->id = 'easysocialoperator_'.$i;
			$return .= ' '.$operators->display("data[message][message_receiver][standard][easysocial][easysocialfield][".$i."][operator]").' <span id="valueToChange_'.$i.'_value"><input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$i.'][value]" style="width:200px" value="" id="filter'.$i.'easysocialfieldsvalue"></span></div>';
			if($i!=4)	$return .= JHTML::_('select.genericlist',   $relation, "data[message][message_receiver][standard][easysocial][easysocialfield][".$i."][relation]", 'onchange="countresults(\'easysocialField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onAcySMSDisplayFields_easysocialField()
	{
		$num = JRequest::getInt('num');
		$profile = JRequest::getString('profile');

		if(empty($profile)) return '<input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$num.'][map]" style="width:200px" value="" id="filter'.$num.'easysocialfieldsmap"/>';

		$lang = JFactory::getLanguage();
		$lang->load('com_easysocial', JPATH_ADMINISTRATOR);

		$db = JFactory::getDBO();
		$db->setQuery('SELECT socialfields.id, socialfields.title FROM #__social_fields AS socialfields JOIN #__social_fields_steps AS socialfieldssteps ON socialfields.step_id = socialfieldssteps.id WHERE socialfieldssteps.uid = '.intval($profile).' AND socialfields.unique_key NOT LIKE "'.implode('%" AND socialfields.unique_key NOT LIKE "', array("JOOMLA_","HEADER","SEPARATOR","TERMS","COVER","AVATAR")).'%"');
		$fields = $db->loadObjectList();
		$list = array();
		$list[] = JHTML::_('select.option', 0, '- - -');
		foreach($fields as $field)
			$list[] = JHTML::_('select.option', $field->id, JText::_($field->title));

		return JHTML::_('select.genericlist', $list, "data[message][message_receiver][standard][easysocial][easysocialfield][".$num."][map] ", 'onchange="displayFieldsFilterValues('.$num.', this.value);setTimeout(function(){countresults(\'easysocialField\');}, 1000);" class="inputbox" size="1"', 'value', 'text', '', 'easysocialmap_'.$num);
 	}

 	function onAcySMSdisplayFieldsFilterValues_easysocialField(){
 		$num = JRequest::getInt('num');
		$map = JRequest::getString('map');
		$cond = JRequest::getString('operator');
		$value = JRequest::getString('value');

		$emptyInputReturn = '<input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$num.'][value]" id="filter'.$num.'acymailingfieldvalue" style="width:200px" value="'.$value.'">';

		if(empty($map) || !in_array($cond,array('=','!='))) return $emptyInputReturn;

		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT `raw` AS value
				FROM #__social_fields_data
				WHERE field_id = '.intval($map).'
				LIMIT 100';

		$db->setQuery($query);
 		$prop = $db->loadObjectList();

 		if(empty($prop) || count($prop) >= 100 || (count($prop) == 1 && (empty($prop[0]->value) || $prop[0]->value == '-'))) return $emptyInputReturn;

 		return JHTML::_('select.genericlist', $prop, "data[message][message_receiver][standard][easysocial][easysocialfield][".$num."][value]", 'onchange="countresults(\'easysocialField\')" class="inputbox" size="1" style="width:200px"', 'value', 'value', $value, 'filter'.$num.'acysmsfieldvalue');
	 }

	function onACYSMSSelectData_easysocialField(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table))	$integration = ACYSMS::getIntegration($message->message_receiver_table);
		else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['easysocial']['easysocialfield'])) return;
		if(!isset($acyquery->join['socialfieldsvalue']) && $integration->componentName != 'easysocial' ) $acyquery->join['socialfieldsvalue'] = 'LEFT JOIN #__social_fields_data AS socialfieldsvalue ON socialfieldsvalue.uid = joomusers.id';
		$addCondition = '';
		$whereConditions = '';
		$i = 0;
		foreach($message->message_receiver['standard']['easysocial']['easysocialfield'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			$i++;
			$acyquery->join['socialfieldsvalue'.$i] = 'JOIN #__social_fields_data AS socialfieldsvalue_'.$i.' ON socialfieldsvalue_'.$i.'.uid = joomusers.id';
			if(!empty($addCondition))	$whereConditions = '  ('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])) $addCondition = $oneFilter['relation'];
			else  $addCondition = 'AND';
			$whereConditions .= $acyquery->convertQuery('socialfieldsvalue_'.$i,'raw',$oneFilter['operator'],$oneFilter['value']).' AND '.$acyquery->convertQuery('socialfieldsvalue_'.$i,'field_id','=',$oneFilter['map']);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}

}//endclass
