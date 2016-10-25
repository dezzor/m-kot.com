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
class AnswertriggerViewAnswerTrigger extends acysmsView
{
	var $ctrl = 'answertrigger';
	var $nameListing = 'SMS_ANSWERS_TRIGGER';
	var $nameForm = 'answertrigger';
	var $icon = 'answertrigger';

	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$config = ACYSMS::config();
		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$dropdownFilters = new stdClass();
		$db = JFactory::getDBO();
		$filters = array();
		$toggleHelper = ACYSMS::get('helper.toggle');



		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'answertrigger.answertrigger_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));



		$searchMap = array('answertrigger.answertrigger_id','answertrigger.answertrigger_actions','answertrigger.answertrigger_name','answertrigger.answertrigger_triggers');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}


		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'answertrigger.answertrigger_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$plgactions = array();
		$answerTrigger = new stdClass();
		$dispatcher->trigger('onACYSMSDisplayActionsAnswersTrigger',array(&$plgactions,$answerTrigger));





		$queryCount = 'SELECT COUNT(answertrigger.answertrigger_id) FROM '.ACYSMS::table('answertrigger').' as answertrigger ';

		$query = 'SELECT * FROM '.ACYSMS::table('answertrigger').' as answertrigger ';
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
			$queryCount.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $db->loadObjectList();
		$pageInfo->elements->page = count($rows);

		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();

		if(!empty($rows)){
			foreach($rows as $oneRow){
				if(!empty($oneRow->answertrigger_actions)){
					$actions = array();
					$actions = unserialize($oneRow->answertrigger_actions);
					$oneRow->answertrigger_actions = "";
					if(empty($actions['selected']))	continue;
					if(!is_array($actions['selected'])){
						$oneRow->answertrigger_actions = '- '.$plgactions[$actions['selected']]->name;
						continue;
					}
					foreach($actions['selected'] as $oneAction){
						$oneRow->answertrigger_actions .= '- '.$plgactions[$oneAction]->name;
						if(!empty($actions[$oneAction]))	$oneRow->answertrigger_actions .= implode(',',$actions[$oneAction]);
						$oneRow->answertrigger_actions .= '<br />';
					}
				}
			}
		}


		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );

		ACYSMS::setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::addNew();
		JToolBarHelper::editList();

		if(ACYSMS::isAllowed($config->get('acl_answers_trigger_delete','all')))	JToolBarHelper::deleteList(JText::_('SMS_VALIDDELETEITEMS'));

		if(ACYSMS::isAllowed($config->get('acl_answers_trigger_copy','all'))){
			JToolBarHelper::divider();
			JToolBarHelper::custom( 'copy', 'copy.png', 'copy.png', JText::_('SMS_COPY') );
		}

		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','answerTrigger');
		if(ACYSMS::isAllowed($config->get('acl_cpanel_manage','all')))	$bar->appendButton( 'Link', 'acysms', JText::_('SMS_CPANEL'), ACYSMS::completeLink('dashboard') );

		$this->assignRef('dropdownFilters',$dropdownFilters);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('config',$config);
		$this->assignRef('order',$order);
		$this->assignRef('toggleHelper',$toggleHelper);
	}


	function form(){
		$answerTriggerid = ACYSMS::getCID('answertrigger_id');
		$config = ACYSMS::config();

		if(!empty($answerTriggerid)){
			$answerTriggerClass = ACYSMS::get('class.answertrigger');
			$answerTrigger = $answerTriggerClass->get($answerTriggerid);
		}else{
			$answerTrigger = new stdClass();
			$answerTrigger->answertrigger_name = '';
			$answerTrigger->answertrigger_description = '';
			$answerTrigger->answertrigger_actions = '';
			$answerTrigger->answertrigger_trigger = '';
			$answerTrigger->answertrigger_publish = '1';
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		$actions = array();
		$dispatcher->trigger('onACYSMSDisplayActionsAnswersTrigger',array(&$actions,$answerTrigger));

		$radioListActions = '';
		foreach($actions as $value => $newOption){
			$radioListActions .=  '<input type="checkbox" name="data[answertrigger][answertrigger_actions][selected][]" id="action_'.$value.'" value="'.$value.'" '.@(in_array($value,@$answerTrigger->answertrigger_actions['selected']) ? "checked" : "").'/> ';
			$radioListActions .=  '<label for="action_'.$value.'" >'.$newOption->name.'</label>';
			if(!empty($newOption->extra)) $radioListActions .= $newOption->extra;
			$radioListActions .= '<br />';
		}

		$triggerWhen = '';
		$inputRegex = '#<input type="text" name="data[answertrigger][answertrigger_triggers][regex]" onChange="document.getElementById(\'answertrigger_regex\').checked=true;" value="'.@$answerTrigger->answertrigger_triggers['regex'].'" />#is';
		$inputWord = '<input type="text" name="data[answertrigger][answertrigger_triggers][word]" onChange="document.getElementById(\'answertrigger_word\').checked=true;" value="'.@$answerTrigger->answertrigger_triggers['word'].'" />';

		$triggerWhen = '<label for="answertrigger_regex"><input type="radio" id="answertrigger_regex" name="data[answertrigger][answertrigger_triggers][selected]" value="regex" '.(!empty($answerTrigger->answertrigger_triggers['selected']) && $answerTrigger->answertrigger_triggers['selected'] == "regex" ? "checked" : "" ).'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_MATCHES_REGEX').' : </label>'.$inputRegex.'<br />';
		$triggerWhen .= '<label for="answertrigger_word"><input type="radio" id="answertrigger_word" name="data[answertrigger][answertrigger_triggers][selected]" value="word" '.(!empty($answerTrigger->answertrigger_triggers['selected']) && $answerTrigger->answertrigger_triggers['selected'] == "word" ? "checked" : "" ).'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_CONTAINS_ONLY').' : </label>'.$inputWord.'<br />';
		$triggerWhen .= '<label for="answertrigger_attachment"><input type="checkbox" id="answertrigger_attachment" name="data[answertrigger][answertrigger_triggers][attachment]" value="contains" '.(!empty($answerTrigger->answertrigger_triggers['attachment']) && $answerTrigger->answertrigger_triggers['attachment'] == "contains" ? "checked" : "" ).'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_CONTAINS_ATTACHMENT').'</label>';




		ACYSMS::setTitle(JText::_('SMS_ANSWERS_TRIGGER'),'answertrigger',$this->ctrl.'&task=edit&answertrigger_id='.$answerTriggerid);

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','answerTrigger');

		$tabs = ACYSMS::get('helper.tabs');
		$tabs->setOptions(array('useCookie' => true));

		if(version_compare(JVERSION,'1.6.0','<')){
			$script = 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$script = 'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton == \'cancel\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		$script .= 'if(window.document.getElementById("answertrigger_name").value.length < 1){alert(\''.JText::_('SMS_ENTER_NAME',true).'\'); return false;}';
		if(version_compare(JVERSION,'1.6.0','<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}


		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $script );
		$this->assignRef('config',$config);
		$this->assignRef('answertrigger',$answerTrigger);
		$this->assignRef('radioListActions',$radioListActions);
		$this->assignRef('triggerWhen',$triggerWhen);

	}
}
