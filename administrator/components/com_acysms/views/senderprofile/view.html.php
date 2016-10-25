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
class senderprofileViewsenderprofile extends acysmsView {
	var $ctrl = 'senderprofile';
	var $nameForm = 'senderprofile';
	var $icon = 'sender';

	function display($tpl = null) {
		$function = $this->getLayout();
		if(method_exists($this, $function))
			$this->$function();

		parent::display($tpl);
	}

	function listing() {
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();



		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase . ".filter_order", 'filter_order', 'a.senderprofile_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase . ".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase . ".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase . '.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase . '.limitstart', 'limitstart', 0, 'int');

		if($pageInfo->filter->order->dir != "asc")	$pageInfo->filter->order->dir = 'desc';


		$searchMap = array (
			'a.senderprofile_id',
			'a.senderprofile_name',
			'a.senderprofile_gateway',
			'a.senderprofile_userid',
			'b.username'
		);
		$filters = array ();
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap) . " LIKE $searchVal";
		}

		$query = 'SELECT b.*,a.* FROM ' . ACYSMS::table('senderprofile') . ' AS a LEFT JOIN #__users AS b ON a.senderprofile_userid=b.id';
		$queryCount = 'SELECT COUNT(a.senderprofile_id) FROM ' . ACYSMS::table('senderprofile') . ' as a';

		if(!empty($filters))
			$query .= ' WHERE (' . implode(') AND (', $filters) . ')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY ' . $pageInfo->filter->order->value . ' ' . $pageInfo->filter->order->dir;
		}
		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);



		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		ACYSMS::setTitle(JText::_('SMS_SENDER_PROFILES'), $this->icon, $this->ctrl);

		$bar = JToolBar::getInstance('toolbar');

		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		if(ACYSMS::isAllowed($config->get('acl_sender_profiles_delete','all')))	JToolBarHelper::deleteList(JText::_('SMS_VALIDDELETEITEMS'));
		JToolBarHelper::spacer();
		if(ACYSMS::isAllowed($config->get('acl_sender_profiles_copy','all')))	JToolBarHelper::custom('copy', 'copy.png', 'copy.png', JText::_('SMS_COPY'));
		JToolBarHelper::divider();

		$bar->appendButton('Pophelp', 'senderprofiles');
		if(ACYSMS::isAllowed($config->get('acl_cpanel_manage','all')))	$bar->appendButton( 'Link', 'acysms', JText::_('SMS_CPANEL'), ACYSMS::completeLink('dashboard') );

		$toggleHelper = ACYSMS::get('helper.toggle');
		$this->assignRef('toggleHelper',$toggleHelper);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);

	}

	function form() {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$config = ACYSMS::config();

		JHTML::_('behavior.modal', 'a.modal');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		acysms_loadMootools();

		$acltype = ACYSMS::get('type.acl');


		$senderprofile_id = ACYSMS::getCID('senderprofile_id');
		$senderprofileClass = ACYSMS::get('class.senderprofile');

		if(!empty ($senderprofile_id)) {
			$senderprofile = $senderprofileClass->get($senderprofile_id);
		} else {
			$senderprofile = new stdClass();
			$senderprofile->senderprofile_name = '';
			$senderprofile->senderprofile_gateway = '';
			$senderprofile->senderprofile_access = 'all';
		}

		$gateways = array ();
		$gateways[] = JHTML::_('select.option', '', JText::_('SMS_SELECT_GATEWAY'));
		$dirs = JFolder::folders(ACYSMS_GATEWAY);
		foreach ($dirs as $oneDir) {
			if($oneDir == 'default')
				continue;
			$oneGateway = $senderprofileClass->getGateway($oneDir);
			$gateways[] = JHTML::_('select.option', $oneDir, $oneGateway->name);
		}
		$gatewaydropdown = JHTML::_('select.genericlist', $gateways, "data[senderprofile][senderprofile_gateway]", 'size="1" onchange="loadGateway(this.value);"', 'value', 'text', $senderprofile->senderprofile_gateway);


		if(version_compare(JVERSION, '1.6.0', '<')) {
			$script = 'function submitbutton(pressbutton){
									if(pressbutton == \'cancel\') {
										submitform( pressbutton );
										return;
									}';
		} else {
			$script = 'Joomla.submitbutton = function(pressbutton) {
									if(pressbutton == \'cancel\') {
										Joomla.submitform(pressbutton,document.adminForm);
										return;
									}';
		}
		$script .= 'if(window.document.getElementById("senderprofile_name").value.length < 2){alert(\'' . JText::_('SMS_ENTER_NAME', true) . '\'); return false;}';
		$script .= 'if(window.document.getElementById("datasenderprofilesenderprofile_gateway").selectedIndex==0){alert(\'' . JText::_('SMS_PLEASE_SELECT_GATEWAY', true) . '\'); return false;}';
		if(version_compare(JVERSION, '1.6.0', '<')) {
			$script .= 'submitform( pressbutton );} ';
		} else {
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}

		$script .= "function loadGateway(gateway){
						document.getElementById('gateway_params').innerHTML = '<span class=\"onload\"></span>';
						try{
							new Ajax('index.php?&option=com_acysms&tmpl=component&ctrl=senderprofile&task=gatewayparams&gateway='+gateway,{ method: 'post', update: document.getElementById('gateway_params')}).request();
						}catch(err){
							new Request({
							method: 'post',
							url: 'index.php?&option=com_acysms&tmpl=component&ctrl=senderprofile&task=gatewayparams&gateway='+gateway,
							onSuccess: function(responseText, responseXML) {
								document.getElementById('gateway_params').innerHTML = responseText;
							}
							}).send();
						}
				}";




		$message_body = JRequest::getString('message_body');
		if(empty($message_body)) $message_body = JText::_('SMS_TEST_MESSAGE');


		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);
		$currentIntegration = $integration->componentName;

		$script .= "function affectTestUser(idcreator,name,phoneNumber){
									window.document.getElementById('test_phone').value = phoneNumber;
									window.document.getElementById('test_phone').innerHTML = name+' ('+phoneNumber+')';
									window.document.getElementById('testID').value = idcreator;
				}";

		$testID = $app->getUserStateFromRequest( $currentIntegration."_testID", $currentIntegration."_testID",	'', 'int' );

		if(empty($testID)) {
			$user = JFactory::getUser();
			$testIDs = $integration->getReceiverIDs($user->id);
			if(!empty($testIDs))$testID = reset($testIDs);
		}

		if(!empty($testID)){
			$user = new stdClass();
			$user->queue_receiver_id = intval($testID);
			$userInformations = array($user);
			$integration->addUsersInformations($userInformations);
			$userInformations = reset($userInformations);
		}



		ACYSMS::setTitle(JText::_('SMS_SENDER_PROFILE'), $this->icon, $this->ctrl . '&task=edit&senderprofile_id=' . $senderprofile_id);

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton('Pophelp', 'senderprofiles');


		$doc->addScriptDeclaration($script);
		$this->assignRef('message_body', $message_body);
		$this->assignRef('senderprofile', $senderprofile);
		$this->assignRef('gatewaydropdown', $gatewaydropdown);
		$this->assignRef('userInformations', $userInformations);
		$this->assignRef('currentIntegration', $currentIntegration);
		$this->assignRef('acltype', $acltype);


	}
}
