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
class QueueViewQueue extends acysmsView
{
	var $icon = 'queue';
	var $defaultSize = 160;

	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function preview(){
		$config = ACYSMS::config();

		$message_id = JRequest::getInt('message_id');
		$receiver_id = JRequest::getInt('receiver_id');
		$receiver_table = JRequest::getCmd('receiver_table');

		$messageClass = ACYSMS::get('class.message');
		$message = $messageClass->get($message_id);

		$user = new stdClass();
		$user->queue_receiver_id = $receiver_id;
		$testUser = array($user);

		if(!empty($message->message_receiver_table)) $integration = ACYSMS::getIntegration($message->message_receiver_table);
		else $integration = $integration = ACYSMS::getIntegration($config->get('default_integration'));
		$integration->addUsersInformations($testUser);
		$userInformations = reset($testUser);

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSReplaceTags',array(&$message, false));
		$dispatcher->trigger('onACYSMSReplaceUserTags',array(&$message,&$userInformations, false));

		$this->assignRef('message',$message);

	}

	function listing(){
		JHTML::_('behavior.modal','a.modal');
		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$db = JFactory::getDBO();
		$toggleClass = ACYSMS::get('helper.toggle');
		$dropdownFilters = new stdClass();
		$filters = array();
		$helperPhone = ACYSMS::get('helper.phone');
		$doc = JFactory::getDocument();



		$filter_messagequeue = $app->getUserStateFromRequest( "filter_messagequeue",'filter_messagequeue', '','string');
		$filterArray = explode('.',$filter_messagequeue);

		$selectedMessage = '';
		$currentIntegration = '';
		$queueMessageFilterIntegration = '';

		if(!empty($filterArray[0])){
			$queueMessageFilterIntegration = $filterArray[0];
			$selectedMessage = $filterArray[1];
		}

		if(empty($queueMessageFilterIntegration)){
			$currentIntegration = $app->getUserStateFromRequest( "currentIntegration", 'currentIntegration', '', 'string' );
		}else $currentIntegration = $queueMessageFilterIntegration;
		$integration = ACYSMS::getIntegration($currentIntegration);



		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',    'queue.queue_senddate','cmd' );
		$pageInfo->filter->order->dir   = $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',    'desc', 'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));


		$listQueueMessage = ACYSMS::get('type.queuemessage');
		$listQueueMessage->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"';
		$dropdownFilters->message = $listQueueMessage->display('filter_messagequeue',$currentIntegration.'.'.$selectedMessage);

		$filters = array();
		if(!empty($selectedMessage) && $selectedMessage != 'all') $filters[] = 'queue.queue_message_id = '.intval($selectedMessage);
		if(!empty($currentIntegration)) $filters[] = 'queue.queue_receiver_table = '.$db->quote($currentIntegration);

		$searchMap = array('queue.queue_message_id','message.message_subject','message.message_userid', 'message.message_senderid','message.message_status');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}




		$result = $integration->getQueueListingQuery($filters, $pageInfo->filter->order);

		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;
		$db->setQuery($result->query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$db->setQuery($result->queryCount);
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if(empty($rows)){
			$query = 'SELECT message_receiver_table FROM #__acysms_message message
						JOIN #__acysms_queue queue
						ON queue_message_id = message_id';
			if(!empty($pageInfo->search)){
				$query .= ' WHERE '.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			}
			$query .= ' ORDER BY queue_senddate DESC LIMIT 1';
			$db->setQuery($query);
			$result = $db->loadResult();

			if(!empty($result)){
				$queueFirstMessageIntegration = ACYSMS::getIntegration($result);
				$link = '<a href="'.ACYSMS::completeLink('queue&filter_messagequeue='.$queueFirstMessageIntegration->componentName.'.all').'" >'.$queueFirstMessageIntegration->displayedName.'</a>';
				$app->enqueueMessage(JText::sprintf('SMS_QUEUE_NO_ENTRY', $link), 'warning');
			}
		}



		if(empty($searchVal) && empty($selectedMessage)){
			$joomConfig = JFactory::getConfig();
			$offset = ACYSMS_J30 ? $joomConfig->get('offset') : $joomConfig->getValue('config.offset');
			$diff = date('Z') + intval($offset*60*60);
			$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");

			$db->setQuery("SELECT count(statsdetails.`statsdetails_message_id`) as total,  statsdetails.statsdetails_status, DATE_FORMAT(FROM_UNIXTIME(`statsdetails_sentdate` - $diff), '%Y-%m-%d') as sentdate
							FROM ".ACYSMS::table('statsdetails')." AS statsdetails
							WHERE statsdetails.statsdetails_sentdate < ".intval(time() - 2628000)."
							GROUP BY statsdetails.statsdetails_status, sentdate
							ORDER BY sentdate DESC"
							);
			$messages = $db->loadObjectList();
		}
		$this->assignRef('messages',$messages);


		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );

		ACYSMS::setTitle(JText::_('SMS_QUEUE'), $this->icon ,'queue');
		$bar = JToolBar::getInstance('toolbar');

		if(ACYSMS::isAllowed($config->get('acl_queue_process','all')))	$bar->appendButton( 'AcySMSpopup', 'process', JText::_('SMS_PROCESS'), "index.php?option=com_acysms&ctrl=queue&task=process&tmpl=component&message_id=".$selectedMessage);
		if(ACYSMS::isAllowed($config->get('acl_queue_delete','all')))	$bar->appendButton( 'Confirm', JText::sprintf('SMS_CONFIRM_DELETE_QUEUE',$pageInfo->elements->total), 'delete', JText::_('SMS_DELETE'), 'remove', false, false );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','queue');
		$bar->appendButton( 'Link', 'acysms', JText::_('SMS_CPANEL'), ACYSMS::completeLink('dashboard') );

		$this->assignRef('dropdownFilters',$dropdownFilters);
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('integration',$integration);
		$this->assignRef('helperPhone',$helperPhone);
	}

	function process(){
		$message_id = ACYSMS::getCID('message_id');
		$queueClass = ACYSMS::get('class.queue');
		$queueStatus = $queueClass->queueStatus($message_id);
		$nextqueue = $queueClass->queueStatus($message_id,true);
		$scheduleSMS = $queueClass->getScheduled();
		$this->assignRef('schedMsgs',$scheduleSMS);

		$msgWaitingCredits = false;
		foreach($queueStatus as $oneMsgInQueue){
			if($oneMsgInQueue->message_status == 'waitingcredits') $msgWaitingCredits = true;
		}

		if(empty($queueStatus) AND empty($scheduleSMS)) ACYSMS::display(JText::_('SMS_NO_PROCESS'),'info');
		if($msgWaitingCredits) ACYSMS::display(JText::_('SMS_MESSAGE_WAITING_CREDITS_IN_QUEUE'),'info');
		$infos = new stdClass();
		$infos->message_id = $message_id;
		$this->assignRef('queue',$queueStatus);
		$this->assignRef('nextqueue',$nextqueue);
		$this->assignRef('infos',$infos);
	}
}
