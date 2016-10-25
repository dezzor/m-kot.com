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
class CpanelViewCpanel extends acysmsView
{
	var $icon = 'smsconfig';
	function display($tpl = null)
	{
		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$tabs = ACYSMS::get('helper.tabs');
		$toggleHelper = ACYSMS::get('helper.toggle');
		$doc = JFactory::getDocument();
		$test_phone = $config->get('test_phone') ;
		jimport('joomla.filesystem.folder');

		JHTML::_('behavior.modal','a.modal');
		$tabs->setOptions(array('useCookie' => true));

		if($config->get('website') != ACYSMS_LIVE){
			$updateHelper = ACYSMS::get('helper.update');
			$updateHelper->addUpdateSite();
		}


		$dirs = JFolder::folders( ACYSMS_INTEGRATION );
		$integration_list = '<table>';
		foreach($dirs as $oneDir){
			if($oneDir == 'default') continue;

			$oneIntegration = ACYSMS::getIntegration($oneDir);
			if(!$oneIntegration->isPresent()) continue;

			$phoneFields = $oneIntegration->getPhoneField();

			if(empty(reset($phoneFields)->fields)) {
				$newPhoneFields = array();
				$newPhoneFields[$oneIntegration->componentName] = new stdClass();
				$newPhoneFields[$oneIntegration->componentName]->displayedName = $oneIntegration->displayedName;
				$newPhoneFields[$oneIntegration->componentName]->componentName = $oneIntegration->componentName;
				$newPhoneFields[$oneIntegration->componentName]->fields = $phoneFields;
				$phoneFields = $newPhoneFields;
			}
			foreach ($phoneFields as $onePhoneField) {
				$integration_list .= '<tr><td>'.$onePhoneField->displayedName.' :</td><td>';
				if(empty($onePhoneField->fields)){
					$integration_list .= '<span style="color:red">'.JText::_('SMS_NO_PHONE_FIELD').'</span>';
				}else{
					$emptyElement = new stdClass();
					$emptyElement->column = '';
					$emptyElement->name = JText::_('SMS_DO_NOT_USE_INTEGRATION');
					array_unshift($onePhoneField->fields,$emptyElement);
						$integration_list .= JHTML::_('select.genericlist', $onePhoneField->fields, "config[".$onePhoneField->componentName."_field]" , 'size="1" ','column', 'name',  $config->get($onePhoneField->componentName.'_field'));
				}
				$integration_list .= '</td></tr>';
			}
		}
		$integration_list .= '</table>';

		$defaultIntegration = $config->get('default_integration');
		$integrationType = ACYSMS::get('type.integration');
		$integrationType->load();
		$integrationType = $integrationType->display('config[default_integration]',$defaultIntegration);

		$country = $config->get('country');
		$countryType = ACYSMS::get('type.country');
		$countryPrefix = $countryType->displayCountry($country,'config[country]');

		$db = JFactory::getDBO();
		$integrationNameKey = ACYSMS::getIntegration('acysms')->getPhoneField();
		$db->setQuery('SELECT * FROM #__acysms_fields WHERE fields_type="phone" AND fields_namekey='.$db->Quote($integrationNameKey[0]->column));
		$res = $db->loadResult();
		if(!empty($res))
			$this->idPhoneField = $res;

		$messageMaxChar = $config->get('messageMaxChar');

		$i = 10;
		$sendXsms = array();
		while($i<=1000){
			$sendXsms[] = JHTML::_('select.option',$i, $i, 'value', 'text');
			if($i<200) $i += 10;
			else $i += 50;
		}
		$sendXsmsParams = JHTML::_('select.genericlist',   $sendXsms, 'config[queue_nbmsg]', ' class="inputbox" style="width:70px" size="1"', 'value', 'text', $config->get('queue_nbmsg'));


		$parallelThreads = array();
		$parallelThreads[] = JHTML::_('select.option',1, 1, 'value', 'text');
		$parallelThreads[] = JHTML::_('select.option',5, 5, 'value', 'text');
		for($j =10; $j<=50; $j+=10){
			$parallelThreads[] = JHTML::_('select.option',$j, $j, 'value', 'text');
		}
		$parallelThreads[] = JHTML::_('select.option',75, 75, 'value', 'text');
		$parallelThreads[] = JHTML::_('select.option',100, 100, 'value', 'text');
		$parallelThreadsParams = JHTML::_('select.genericlist',   $parallelThreads, 'config[parallel_threads]', 'class="inputbox" style="width:70px" size="1"', 'value', 'text', $config->get('parallel_threads'));

		$delayTypeAuto = ACYSMS::get('type.delay');
		$cronFrequency = $delayTypeAuto->display('config[cron_frequency]',$config->get('cron_frequency'),2);

		$elements = new stdClass();
		$elements->cron_url = ACYSMS_LIVE.'index.php?option=com_acysms&ctrl=cron';

		$item = $config->get('itemid');
		if(!empty($item)) $elements->cron_url.= '&Itemid='.$item;
		$urlCron = 'https://www.acyba.com/index.php?option=com_updateme&ctrl=launcher&component=acysms&task=edit&cronurl='.urlencode($elements->cron_url);
		$elements->cron_edit = '<a class="modal" href="'.$urlCron.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button class="btn" onclick="return false">'.JText::_('SMS_CREATE_CRON').'</button></a>';

		$cronreportval = array();
		$cronreportval[] = JHTML::_('select.option', '0',JText::_('SMS_NO'));
		$cronreportval[] = JHTML::_('select.option', '1',JText::_('SMS_EACH_TIME'));
		$cronreportval[] = JHTML::_('select.option', '2',JText::_('SMS_ONLY_ACTION'));
		$cronreportval[] = JHTML::_('select.option', '3',JText::_('SMS_ONLY_SOMETHING_WRONG'));


		$jscron = "function updateCronReport(){";
		$jscron .= "cronsendreport = window.document.getElementById('cronsendreport').value;";
		$jscron .= "if(cronsendreport != 0) {window.document.getElementById('cronreportdetail').style.display = 'block';}else{window.document.getElementById('cronreportdetail').style.display = 'none';}";
		$jscron .= '}';
		$jscron .='window.addEvent(\'domready\', function(){ updateCronReport(); });';

		$elements->cron_sendreport = JHTML::_('select.genericlist',   $cronreportval, 'config[cron_sendreport]', 'class="inputbox" size="1" style="width:280px;" onchange="updateCronReport();"', 'value', 'text', (int) $config->get('cron_sendreport',2) ,'cronsendreport');

		$cronsave = array();
		$cronsave[] = JHTML::_('select.option', '0',JText::_('SMS_NO'));
		$cronsave[] = JHTML::_('select.option', '1',JText::_('SMS_SIMPLIFIED_REPORT'));
		$cronsave[] = JHTML::_('select.option', '2',JText::_('SMS_DETAILED_REPORT'));

		$jscron .= "function updateCronReportSave(){";
			$jscron .= "cronsavereport = window.document.getElementById('cronsavereport').value;";
			$jscron .= "if(cronsavereport != 0) {window.document.getElementById('cronreportsave').style.display = 'block';}else{window.document.getElementById('cronreportsave').style.display = 'none';}";
		$jscron .= '}';
		$jscron .='window.addEvent(\'domready\', function(){ updateCronReportSave(); });';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $jscron );
		$elements->cron_savereport = JHTML::_('select.genericlist',   $cronsave, 'config[cron_savereport]', 'class="inputbox" size="1" style="width:200px;" onchange="updateCronReportSave();"', 'value', 'text', (int) $config->get('cron_savereport',0) ,'cronsavereport');


		$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=cpanel&amp;task=cleanreport';
		$elements->deleteReport = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 400, y: 100}}"><button class="btn" onclick="return false">'.JText::_('SMS_REPORT_DELETE').'</button></a>';
		$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=cpanel&amp;task=seereport';
		$elements->seeReport = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button class="btn" onclick="return false">'.JText::_('SMS_REPORT_SEE').'</button></a>';


		if(version_compare(JVERSION,'1.6.0','<')){
			$db->setQuery("SELECT name,published,id FROM `#__plugins` WHERE `folder` = 'acySMS' OR `element` LIKE '%acysms%' ORDER BY published DESC, ordering ASC");
		}else{
			$db->setQuery("SELECT name,enabled as published,extension_id as id FROM `#__extensions` WHERE (`folder` = 'acySMS' OR `element` LIKE 'acysms%') AND `type`= 'plugin' ORDER BY enabled DESC, ordering ASC");
		}
		$plugins = $db->loadObjectList();


		$path = JLanguage::getLanguagePath(JPATH_ROOT);
		$dirs = JFolder::folders( $path );
		$languages = array();
		foreach ($dirs as $dir)
		{
			if(strlen($dir) != 5 || $dir == "xx-XX") continue;
			$xmlFiles = JFolder::files( $path.DS.$dir, '^([-_A-Za-z]*)\.xml$' );
			$xmlFile = reset($xmlFiles);
			if(empty($xmlFile)){
				$data = array();
			}else{
				$data = JApplicationHelper::parseXMLLangMetaFile($path.DS.$dir.DS.$xmlFile);
			}
			$oneLanguage = new stdClass();
			$oneLanguage->language 	= $dir;
			$oneLanguage->name = empty($data['name']) ? $dir : $data['name'];
			$languageFiles = JFolder::files( $path.DS.$dir, '^(.*)\.com_acysms\.ini$' );
			$languageFile = reset($languageFiles);
			if(!empty($languageFile)){
				$linkEdit = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=file&amp;task=language&amp;code='.$oneLanguage->language;
				$oneLanguage->edit = ' <a class="modal" title="'.JText::_('SMS_EDIT_LANGUAGE_FILE',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img id="image'.$oneLanguage->language.'" class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('SMS_EDIT_LANGUAGE_FILE',true).'"/></a>';
			}else{
				$linkEdit = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=file&amp;task=language&amp;code='.$oneLanguage->language;
				$oneLanguage->edit = ' <a class="modal" title="'.JText::_('SMS_ADD_LANGUAGE_FILE',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img id="image'.$oneLanguage->language.'" class="icon16"  src="'.ACYSMS_IMAGES.'icons/icon-16-add.png" alt="'.JText::_('SMS_ADD_LANGUAGE_FILE',true).'"/></a>';
			}
			$languages[] = $oneLanguage;
		}





		$cssval = array('css_module' => 'module');
		foreach($cssval as $configval => $type){
			$myvals = array();
			$myvals[] = JHTML::_('select.option', '',JText::_('SMS_NONE'));

			$regex = '^'.$type.'_([-_a-z0-9]*)\.css$';
			$allCSSFiles = JFolder::files( ACYSMS_MEDIA.'css', $regex );

			$family = '';
			foreach($allCSSFiles as $oneFile){
				preg_match('#'.$regex.'#i',$oneFile,$results);
				$fileName = str_replace('default_','',$results[1]);
				$fileNameArray = explode('_',$fileName);
				if(count($fileNameArray) == 2){
					if($fileNameArray[0] != $family){
						if(!empty($family)) $myvals[] = JHTML::_('select.option',  '</OPTGROUP>');
						$family = $fileNameArray[0];
						$myvals[] = JHTML::_('select.option',  '<OPTGROUP>', ucfirst($family));
					}
					unset($fileNameArray[0]);
					$fileName = implode('_',$fileNameArray);
				}

				$fileName = ucwords(str_replace('_',' ',$fileName));
				$myvals[] = JHTML::_('select.option', $results[1],$fileName);
			}
			if(!empty($family)) $myvals[] = JHTML::_('select.option',  '</OPTGROUP>');
			$js = 'onchange="updateCSSLink(\''.$configval.'\',\''.$type.'\',this.value);"';
			$currentVal = $config->get($configval,'default');
			$aStyle = empty($currentVal) ? 'style="display:none"' : '';
			$elements->$configval = JHTML::_('select.genericlist',   $myvals, 'config['.$configval.']', 'class="inputbox" size="1" '.$js, 'value', 'text', $config->get($configval,'default'),$configval.'_choice' );
			$linkEdit = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=file&amp;task=css&amp;file='.$type.'_'.$config->get($configval,'default').'&amp;var='.$configval;
			$elements->$configval .= ' <a id="'.$configval.'_link" '.$aStyle.' class="modal" title="'.JText::_('SMS_EDIT',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('SMS_EDIT',true).'"/></a>';
		}
		$js = "function updateCSSLink(myid,type,newval){
			if(newval){document.getElementById(myid+'_link').style.display = '';}else{document.getElementById(myid+'_link').style.display = 'none'}
			document.getElementById(myid+'_link').href = 'index.php?option=com_acysms&tmpl=component&ctrl=file&task=css&file='+type+'_'+newval+'&var='+myid;
		}";
		$doc->addScriptDeclaration( $js );

		$bootstrapFrontValues = array();
		$bootstrapFrontValues[] = JHTML::_('select.option', 0, JTEXT::_('SMS_NO'));
		$bootstrapFrontValues[] = JHTML::_('select.option', 1, 'Bootstrap 2');
		$bootstrapFrontValues[] = JHTML::_('select.option', 2, 'Bootstrap 3');
		$elements->bootstrap_frontend = JHTML::_('acysmsselect.radiolist', $bootstrapFrontValues, "config[bootstrap_frontend]", '', 'value', 'text', $config->get('bootstrap_frontend',0));


		$query = 'SELECT `message_id` FROM #__acysms_message WHERE `message_type` = "activation_optin"';
		$db->setQuery($query);
		$confirmationMessageId = $db->loadResult();
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSgetVerificationCodeIntegrations',array(&$verificationCodeIntegrations));



		$allowFrontData[] = JHTML::_( 'select.option', '1', JText::_('SMS_YES'));
		$allowFrontData[] = JHTML::_( 'select.option', '0', JText::_('SMS_NO'));

		$allowFrontEndManagement = $config->get('allowFrontEndManagement');
		$frontEndManagementOption = JHTML::_('acysmsselect.radiolist', $allowFrontData, 'config[allowFrontEndManagement]', '', 'value', 'text', empty($allowFrontEndManagement) ? '0' : $allowFrontEndManagement);

		$customerData[] = JHTML::_( 'select.option', '1', JText::_('SMS_YES'));
		$customerData[] = JHTML::_( 'select.option', '0', JText::_('SMS_NO'));

		$customerdManagement = $config->get('allowCustomersManagement');
		$customerManagementOption = JHTML::_('acysmsselect.radiolist', $customerData, 'config[allowCustomersManagement]', '', 'value', 'text', empty($customerdManagement) ? '0' : $customerdManagement);


		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		$authorizedTypes = array();
		$aurthorizedFilters = array();
		$authorizedTypesData = array();
		$authorizedFiltersData = array();
		$conditionsToDisplay = '';

		$script = '';

		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);



		if(empty($frontEndFilters)){
			$frontEndFilters = array();
			$frontEndFilters['condition0']['type'] = '';
			$frontEndFilters['condition0']['filters'] = '';

			$emptyEntry = new stdClass();
			$emptyEntry->name = '';
			$authorizedTypes[''] = $emptyEntry;

			$emptyEntry = new stdClass();
			$emptyEntry->name = '';
			$authorizedFilters[''] = $emptyEntry;
		}


		foreach($frontEndFilters as $conditionNumber => $oneCondition){

			$dispatcher->trigger('onACYSMSdisplayAuthorizedType',array(&$authorizedTypes));
			$dispatcher->trigger('onACYSMSdisplayAuthorizedFilters',array(&$authorizedFilters, $oneCondition['filters']));

			$authorizedTypesData = array();
			$authorizedFiltersData = array();

			if(!empty($authorizedTypes)){
				foreach($authorizedTypes as $type => $object){
					$authorizedTypesData[] = JHTML::_( 'select.option', $type, $object->name);
				}
			}
			if(!empty($authorizedFilters)){
				foreach($authorizedFilters as $type => $object){
					$authorizedFiltersData[] = JHTML::_( 'select.option', $type, $object->name);
				}
			}
			$authorizedTypesSelection = JHTML::_('acysmsselect.genericlist', $authorizedTypesData, 'config[frontEndFilters]['.$conditionNumber.'][type]', 'onchange="showTypeDetails(this.value,\''.$conditionNumber.'\'); showAuthorizedFilters(this.value,\''.$conditionNumber.'\');"', 'value', 'text', $oneCondition['type'], $conditionNumber.'_acysmsAuthorizedType');
			$authorizedFiltersSelection = '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterSpan">'.JHTML::_('acysmsselect.genericlist', $authorizedFiltersData, 'config[frontEndFilters]['.$conditionNumber.'][filters]', 'onchange="showAuthorizedFiltersDetails(\''.$conditionNumber.'\')"', 'value', 'text', $oneCondition['filters'], $conditionNumber.'_acysmsAuthorizedFilter').'</span>';

			if(!empty($oneCondition['type']))	$dispatcher->trigger('onACYSMSdisplayAuthorizedType_'.$oneCondition['type'], array(&$authorizedTypesSelection, $conditionNumber));
			else $authorizedTypesSelection .= '<span id="'.$conditionNumber.'_acysmsTypeDetails"></span>';

			if(!empty($oneCondition['filters'])) $dispatcher->trigger('onACYSMSdisplayAuthorizedFilters_'.$oneCondition['filters'],array(&$authorizedFiltersSelection, $conditionNumber));
			else $authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';

			$conditionsToDisplay .= '<div id='.$conditionNumber.'frontEndFilters>'.JText::sprintf('SMS_ALLOW_USERS_OF_X_TO_FILTER_USERS_BASED_ON_Y',$authorizedTypesSelection,$authorizedFiltersSelection);
			$conditionsToDisplay .='<button class="button btn button-margin" type="button" onclick="removeCondition(\''.$conditionNumber.'frontEndFilters\');return false;">'.JText::_('SMS_REMOVE_CONDITION').'</button></div>';
		}

		$requiredFilters = array();
		$dispatcher->trigger('onACYSMSdisplayRequiredFilters',array(&$requiredFilters, $config->get('frontEndRequiredFilters')));

		$requiredFiltersData = array();
		$requiredFiltersData[] = JHTML::_( 'select.option', '',' - - - ');
		if(!empty($requiredFilters)){
			foreach($requiredFilters as $type => $object){
				$requiredFiltersData[] = JHTML::_( 'select.option', $type, $object->name);
			}
		}
		$requiredFiltersDropdown = JHTML::_('acysmsselect.genericlist', $requiredFiltersData, 'config[frontEndRequiredFilters]', '', 'value', 'text', $config->get('frontEndRequiredFilters', 'acysmsgroup'));
		$requiredFilterString = JText::sprintf('SMS_SELECT_AT_LEAST', $requiredFiltersDropdown);



		acysms_loadMootools();
		$script  .= "

		var myInterval = 0;

		function showTypeDetails(type,conditionNumber){
			if(document.getElementById(conditionNumber+'_acysmsTypeDetails')){
				element = document.getElementById(conditionNumber+'_acysmsTypeDetails');
				while (element.firstChild) {
					element.removeChild(element.firstChild);
				}
			}
			document.getElementById(conditionNumber+'_acysmsTypeDetails').innerHTML = '<span id=\"ajaxSpan\" class=\"onload\"></span>';
			try{
				new Ajax('index.php?&option=com_acysms&tmpl=component&ctrl=cpanel&task=displayAuthorizedTypeDetails&type='+type+'&conditionNumber='+conditionNumber,{ method: 'post', update: document.getElementById(conditionNumber+'_acysmsTypeDetails')}).request();
			}catch(err){
				new Request({
				method: 'post',
				url: 'index.php?&option=com_acysms&tmpl=component&ctrl=cpanel&task=displayAuthorizedTypeDetails&type='+type+'&conditionNumber='+conditionNumber,
				onSuccess: function(responseText, responseXML) {
					document.getElementById(conditionNumber+'_acysmsTypeDetails').innerHTML = responseText;
				}
				}).send();
			}
		}

		function showAuthorizedFilters(type,conditionNumber){
			try{
				new Ajax('index.php?&option=com_acysms&tmpl=component&ctrl=cpanel&task=displayAuthorizedFilters&type='+type+'&conditionNumber='+conditionNumber,{ method: 'post', update: document.getElementById(conditionNumber+'_acysmsAuthorizedFilterSpan')}).request();
			}catch(err){
				new Request({
				method: 'post',
				url: 'index.php?&option=com_acysms&tmpl=component&ctrl=cpanel&task=displayAuthorizedFilters&type='+type+'&conditionNumber='+conditionNumber,
				onSuccess: function(responseText, responseXML) {
					document.getElementById(conditionNumber+'_acysmsAuthorizedFilterSpan').innerHTML = responseText;
					if(document.getElementById(conditionNumber+'_acysmsAuthorizedFilterDetails')){
						element = document.getElementById(conditionNumber+'_acysmsAuthorizedFilterDetails');
						while (element.firstChild) {
							element.removeChild(element.firstChild);
						}
					}
					document.getElementById(conditionNumber+'_acysmsAuthorizedFilterDetails').innerHTML = '<span id=\"ajaxSpan\" class=\"onload\"></span>';
					myInterval = setInterval(function(){showAuthorizedFiltersDetails(conditionNumber)},500);
				}
				}).send();
			}

		}

		function showAuthorizedFiltersDetails(conditionNumber){
			filter = document.getElementById(conditionNumber+'_acysmsAuthorizedFilter').value;
			try{
				new Ajax('index.php?&option=com_acysms&tmpl=component&ctrl=cpanel&task=displayAuthorizedFiltersDetails&filter='+filter+'&conditionNumber='+conditionNumber,{ method: 'post', update: document.getElementById(conditionNumber+'_acysmsAuthorizedFilterDetails')}).request();
			}catch(err){
				new Request({
				method: 'post',
				url: 'index.php?&option=com_acysms&tmpl=component&ctrl=cpanel&task=displayAuthorizedFiltersDetails&filter='+filter+'&conditionNumber='+conditionNumber,
				onSuccess: function(responseText, responseXML) {
					document.getElementById(conditionNumber+'_acysmsAuthorizedFilterDetails').innerHTML = responseText;
				}
				}).send();
			}
			clearInterval(myInterval);
		}


		function addCondition()
		{
			var firstConditionId = document.getElementById('frontEndFilters').getElementsByTagName('div')[0].id;
			var i = 0;
			while(document.getElementById('condition'+i+'frontEndFilters')){
				i++;
			}

			var newElement = document.createElement('div');
			newElement.setAttribute('id', 'condition'+i+'frontEndFilters');
			newElement.innerHTML = document.getElementById(firstConditionId).innerHTML.replace(firstConditionId, 'condition'+i+'frontEndFilters');

			lastCondition = firstConditionId.replace('frontEndFilters','');
			var reg = new RegExp(lastCondition,'g');
			newElement.innerHTML = newElement.innerHTML.replace(reg, 'condition'+i);

			document.getElementById('frontEndFilters').appendChild(newElement);
		}

		function removeCondition(id){
			if(document.getElementById(id) && document.getElementById('frontEndFilters').getElementsByTagName('div').length > 1 ){
				document.getElementById(id).remove();
			}
		}";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $script );

		$eltsToClean = array('frontEndFilters', 'allfilters', 'allactions');
		ACYSMS::removeChzn($eltsToClean);




		ACYSMS::setTitle(JText::_('SMS_CONFIGURATION'), $this->icon, 'cpanel');

		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel('cancel',JText::_('SMS_CLOSE'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','config');
		if(ACYSMS::isAllowed($config->get('acl_cpanel_manage','all')))	$bar->appendButton( 'Link', 'acysms', JText::_('SMS_CPANEL'), ACYSMS::completeLink('dashboard') );

		$this->assignRef('integrationType',$integrationType);
		$this->assignRef('test_phone',$test_phone);
		$this->assignRef('languages',$languages);
		$this->assignRef('plugins',$plugins);
		$this->assignRef('toggleHelper',$toggleHelper);
		$this->assignRef('config',$config);
		$this->assignRef('tabs',$tabs);
		$this->assignRef('integration_list',$integration_list);
		$this->assignRef('elements',$elements);
		$this->assignRef('countryPrefix',$countryPrefix);
		$this->assignRef('messageMaxChar',$messageMaxChar);
		$this->assignRef('parallelThreadsParams',$parallelThreadsParams);
		$this->assignRef('sendXsmsParams',$sendXsmsParams);
		$this->assignRef('confirmationMessageId',$confirmationMessageId);
		$this->assignRef('cronFrequency',$cronFrequency);
		$this->assignRef('conditionsToDisplay',$conditionsToDisplay);
		$this->assignRef('requiredFilterString',$requiredFilterString);
		$this->assignRef('customerManagementOption',$customerManagementOption);
		$this->assignRef('frontEndManagementOption',$frontEndManagementOption);
		$this->assignRef('verificationCodeIntegration',$verificationCodeIntegrations);

		return parent::display($tpl);
	}

	public function form(){

	}
}
