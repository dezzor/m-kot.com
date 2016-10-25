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
class plgAcysmsEasyprofile extends JPlugin
{

	var $sendervalues = array();

	function plgAcysmsEasyprofile(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_jsn'))	return;
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

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS','EasyProfile');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('easyprofile'))) $filters['easyprofileFields'] = $newFilter;


	}





	 function onACYSMSGetTags(&$tags) {

	 	$tags['easyprofile'] = new stdClass();
		$tags['easyprofile']->name = JText::sprintf('SMS_X_USER_INFO','EasyProfile');
		$db = JFactory::getDBO();
		$tableFields = acysms_getColumns('#__jsn_users');

		$lang = JFactory::getLanguage();
		$lang->load('com_jsn',JPATH_SITE);

		$tags['easyprofile']->content = '<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['easyprofile']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{easyprofile:'.$oneField.'}\')" class="row'.$k.'"><td>'.JText::_($oneField).'</td></tr>';
			$k = 1-$k;
		}
		$tags['easyprofile']->content .= '</tbody></table>';

	 }


	 function onACYSMSReplaceUserTags(&$message,&$user,$send = true){
	 	$match = '#(?:{|%7B)easyprofile:(.*)(?:}|%7D)#Ui';
		$helperPlugin = ACYSMS::get('helper.plugins');

		if(empty($message->message_body)) return;
		if(!preg_match_all($match,$message->message_body,$results)) return;
		if(!isset($user->easyprofile)){
		 	$db = JFactory::getDBO();
		 	if(!empty($user->joomla->id)){
			 	$query = 'SELECT * FROM #__jsn_users WHERE id = '.intval($user->joomla->id);
				$db->setQuery($query);
				$user->easyprofile = $db->loadObject();
			}
		}
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
			$tags[$oneTag] = (isset($user->easyprofile->$field) && strlen($user->easyprofile->$field) > 0) ? $user->easyprofile->$field : $mytag->default;
			$helperPlugin->formatString($tags[$oneTag],$mytag);
		}
		$message->message_body = str_replace(array_keys($tags),$tags,$message->message_body);
	}







	function onACYSMSDisplayFilterParams_easyprofileFields($message){
		$db = JFactory::getDBO();
		$fields = acysms_getColumns('#__jsn_users');
		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option','',' - - - ');
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option',$oneField,JText::_($oneField));
		}

		$relation = array();
		$relation[] = JHTML::_('select.option','AND',JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option','OR',JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_easyprofileFields"></span>
		<?php
		for($i = 0;$i<5;$i++){
			$operators->extra = 'onchange="countresults(\'easyprofileFields\')"';
			$return = '<div id="filter'.$i.'easyprofilefield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][easyprofile][easyprofilefield][".$i."][map]", 'onchange="countresults(\'easyprofileFields\')" class="inputbox" size="1"', 'value', 'text');
			$return.= ' '.$operators->display("data[message][message_receiver][standard][easyprofile][easyprofilefield][".$i."][operator]").' <input onchange="countresults(\'easyprofileFields\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easyprofile][easyprofilefield]['.$i.'][value]" style="width:200px" value=""></div>';
	 		if($i!=4)	$return .= JHTML::_('select.genericlist',   $relation, "data[message][message_receiver][standard][easyprofile][easyprofilefield][".$i."][relation]", 'onchange="countresults(\'easyprofileFields\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
	 		echo  $return;
	 	}
	}

	function onACYSMSSelectData_easyprofileFields(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table))	$integration = ACYSMS::getIntegration($message->message_receiver_table);
		else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['easyprofile']['easyprofilefield'])) return;
		if(!isset($acyquery->join['easyProfileUsers']) && $integration->componentName != 'easyprofile' ) $acyquery->join['easyProfileUsers'] = 'LEFT JOIN #__jsn_users AS easyProfileUsers ON joomusers.id = easyProfileUsers.id ';
		$addCondition = '';
		$whereConditions = '';

		foreach($message->message_receiver['standard']['easyprofile']['easyprofilefield'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition))	$whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])) $addCondition = $oneFilter['relation'];
			else  $addCondition = 'AND';

			$type = '';
			$value = ACYSMS::replaceDate($oneFilter['value']);

			$whereConditions .= $acyquery->convertQuery('easyProfileUsers',$oneFilter['map'],$oneFilter['operator'],$value,$type);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}

}//endclass
