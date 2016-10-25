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
class AnswerViewAnswer extends acysmsView
{
	var $ctrl = 'answer';
	var $nameListing = 'SMS_ANSWERS';
	var $nameForm = 'answer';
	var $icon = 'answer';
	var $defaultSize = 160;

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
		$phoneHelper = ACYSMS::get('helper.phone');


		$selectedIntegration = $app->getUserStateFromRequest( $paramBase."filter_integration",'filter_integration',0,'string');
		$selectedMessage = $app->getUserStateFromRequest( $paramBase."filter_message",'filter_message',0,'int');
		$selectedAnswerReceiver = $app->getUserStateFromRequest( $paramBase."filter_answerreceiver",'filter_answerreceiver',0,'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'answer.answer_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		if($pageInfo->filter->order->dir != "asc")	$pageInfo->filter->order->dir = 'desc';

		$integrationType = ACYSMS::get('type.integration');
		$integrationType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"';
		$integrationType->allIntegration = true;
		$integrationType->load();
		$dropdownFilters->integration = $integrationType->display('filter_integration',$selectedIntegration);

		$listMessageType = ACYSMS::get('type.message');
		$listMessageType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"';
		$dropdownFilters->message =  $listMessageType->display('filter_message',$selectedMessage);

		$listSender = ACYSMS::get('type.answerreceiver');
		$listMessageType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"';
		$dropdownFilters->answerreceiver =  $listSender->display('filter_answerreceiver',$selectedAnswerReceiver);


		$searchMap = array('answer.answer_id','answer.answer_body','answer.answer_receiver_table','answer.answer_from','answer.answer_to');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		if(!empty($selectedIntegration)) $filters[] = 'answer.answer_receiver_table = '.$db->quote($selectedIntegration);
		if(!empty($selectedMessage)) $filters[] = 'answer.answer_message_id = '.intval($selectedMessage);
		if(!empty($selectedType)) $filters[] = 'answer.answer_receiver_table = '.$db->quote($selectedType);
		if(!empty($selectedAnswerReceiver)) $filters[] = 'answer.answer_to = '.$db->quote($selectedAnswerReceiver);



		$queryCount = 'SELECT COUNT(answer.answer_id) FROM '.ACYSMS::table('answer').' as answer ';

		$query = 'SELECT * FROM '.ACYSMS::table('answer').' as answer';
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


		$receiverInformations = array();

		foreach($rows as $oneRow){
			if(empty($oneRow->answer_receiver_table)) continue;
			$user = new stdClass();
			$user->queue_receiver_id = $oneRow->answer_receiver_id;
			$receiverInformations[$oneRow->answer_receiver_table][] = $user;
		}
		$receivers = array();
		$receiverNames = array();
		foreach($receiverInformations as $integrationName => $receiverdId ){
			$integration = ACYSMS::getIntegration($integrationName);
			if (empty($integration))continue;
			$integration->addUsersInformations($receiverdId);
			$receiversInfo = $receiverdId;
			$receivers[$integrationName] = $receiversInfo;
			foreach($receiversInfo as $oneReceiverInfo){
				if(!empty($oneReceiverInfo->receiver_phone) && !empty($oneReceiverInfo->receiver_name)) $receiverNames[$integrationName][$phoneHelper->getValidNum($oneReceiverInfo->receiver_phone)] = $oneReceiverInfo->receiver_name;
			}
		}





		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );

		ACYSMS::setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$bar = JToolBar::getInstance('toolbar');
		if(ACYSMS::isAllowed($config->get('acl_answers_export','all')))	$bar->appendButton( 'Link', 'smsexport', JText::_('SMS_EXPORT'), ACYSMS::completeLink('answer&task=exportGlobal') );
		JToolBarHelper::divider();
		if(ACYSMS::isAllowed($config->get('acl_answers_delete','all')))	JToolBarHelper::deleteList(JText::_('SMS_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','answers');
		if(ACYSMS::isAllowed($config->get('acl_cpanel_manage','all')))	$bar->appendButton( 'Link', 'acysms', JText::_('SMS_CPANEL'), ACYSMS::completeLink('dashboard') );

		$this->assignRef('dropdownFilters',$dropdownFilters);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('config',$config);
		$this->assignRef('receiverNames',$receiverNames);
		$this->assignRef('phoneHelper',$phoneHelper);



	}
}
