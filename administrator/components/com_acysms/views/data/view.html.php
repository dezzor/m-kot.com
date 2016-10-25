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
class dataViewdata extends acysmsView
{
	var $ctrl = 'import';

	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

		parent::display($tpl);
	}

	function import(){

		$app = JFactory::getApplication();
		$config = ACYSMS::config();


		$importData = array();
		$importData['textarea'] = JText::_('SMS_IMPORT_TEXTAREA');
		$importData['file'] = JText::_('SMS_FILE');

		$importvalues = array();
		foreach($importData as $div => $name){
			$importvalues[] = JHTML::_('select.option', $div,$name);
		}

		$js = 'var currentoption = \''.JRequest::getCmd('importfrom','textarea').'\';
		function updateImport(newoption){document.getElementById(currentoption).style.display = "none";document.getElementById(newoption).style.display = \'block\';currentoption = newoption;}';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );


		if($app->isAdmin()){
				ACYSMS::setTitle(JText::_('SMS_IMPORT'),'import','data&task=import');

				$bar = JToolBar::getInstance('toolbar');
				JToolBarHelper::custom('importConfig', 'import', '',JText::_('SMS_IMPORT'), false);
				JToolBarHelper::cancel('cancel',JText::_('SMS_CANCEL'));
				JToolBarHelper::divider();
				$bar->appendButton( 'Pophelp','import');
		}

		$this->assignRef('importvalues',$importvalues);
		$this->assignRef('importdata',$importData);
		$this->assignRef('config',$config);

	}

	function importConfig(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		$filename =  JRequest::getCmd('filename');
		$paramBase = ACYSMS_COMPONENT.'.'.$filename;

		if(empty($filename)){
			if($app->isAdmin())	$link = ACYSMS::completeLink('data&task=import');
			else  $link = ACYSMS::completeLink('frontdata&task=import');

			$app->redirect($link);
		}

		$importObject = new stdClass();
		$importObject->importcolumn = $app->getUserStateFromRequest( $paramBase."importColumn",'importColumn',0,'array');
		$importObject->importFirstLine = $app->getUserStateFromRequest( $paramBase."importFirstLine",'importFirstLine',0,'int');
		$importObject->charsetconvert = $app->getUserStateFromRequest( $paramBase."charsetconvert",'charsetconvert',0,'string');
		$importObject->overwriteExisting = $app->getUserStateFromRequest( $paramBase."overwriteExisting",'overwriteExisting',0,'string');


		$importHelper = ACYSMS::get('helper.import');
		$importHelper->charsetConvert = $importObject->charsetconvert;
		$lines = $importHelper->get10Lines($filename);
		$importObject->charsetconvert = $importHelper->charsetConvert;

		$this->_detectHeader($lines,$importObject);

		$nbColumns = 0 ;
		if(!empty($lines)){
			foreach($lines as $oneLine){
				if(count($oneLine) > $nbColumns) $nbColumns =  count($oneLine);
			}
		}

		$groupClass = ACYSMS::get('class.group');
		$groups = $app->isAdmin() ? $groupClass->getGroups() : $groupClass->getFrontendGroups();



		if($app->isAdmin()){
				ACYSMS::setTitle(JText::_('SMS_IMPORT'),'import','data&task=import');

				$bar = JToolBar::getInstance('toolbar');
				JToolBarHelper::custom('doImport', 'import', '',JText::_('SMS_IMPORT'), false);
				JToolBarHelper::cancel('cancel',JText::_('SMS_CANCEL'));
				JToolBarHelper::divider();
				$bar->appendButton( 'Pophelp','import');
		}

		$tableColumn = acysms_getColumns('#__acysms_user');

		$columns = array();
		$columns[] = JHTML::_('select.option', '', ' - - - ');
		foreach($tableColumn as $colunmName => $columnType){
			if($colunmName != 'user_id') $columns[] = JHTML::_('select.option', $colunmName, $colunmName);
		}
		$columns[] = JHTML::_('select.option', 'ignore', JText::_('SMS_IGNORE_COLUMN'));

		$this->assignRef('nbColumns',$nbColumns);
		$this->assignRef('lines',$lines);
		$this->assignRef('filename',$filename);
		$this->assignRef('columns',$columns);
		$this->assignRef('importObject',$importObject);
		$this->assignRef('groups',$groups);

	}

	function export(){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$fields = acysms_getColumns('#__acysms_user');

		if(isset($fields['user_activationcode'])) unset($fields['user_activationcode']);

		$config = ACYSMS::config();
		$selectedFields = explode(',',$config->get('export_fields','user_phone_number,user_firstname'));

		if($app->isAdmin()){
			ACYSMS::setTitle(JText::_('SMS_EXPORT'),'smsexport','data&task=export');

			$bar = JToolBar::getInstance('toolbar');
			JToolBarHelper::custom('doexport', 'smsexport', '',JText::_('SMS_EXPORT'), false);
			JToolBarHelper::cancel('cancel',JText::_('SMS_CANCEL'));
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','export');

		}

		$groupClass = ACYSMS::get('class.group');
		$groups = $app->isAdmin() ? $groupClass->getGroups() : $groupClass->getFrontendGroups();


		$selectedGroups = explode(',',$config->get('export_groups'));
		$export_filters	= $config->get('export_filters');
		if(!empty($export_filters)) $selectedFilters = unserialize($export_filters);

		$userStatus = array();
		$userStatus[] = JHTML::_( 'select.option', 'blocked', JText::_('SMS_BLOCKED'));
		$userStatus[] = JHTML::_('select.option', 'notblocked', JText::_('SMS_NOT_BLOCKED'));
		$userStatus[] = JHTML::_('select.option', 'all', JText::_('SMS_ALL_USERS'));

		$charsetType = ACYSMS::get('type.charset');
		$this->assignRef('charset',$charsetType);
		$this->assignRef('fields',$fields);
		$this->assignRef('selectedfields',$selectedFields);
		$this->assignRef('config',$config);
		$this->assignRef('groups',$groups);
		$this->assignRef('selectedgroups',$selectedGroups);
		$this->assignRef('userStatus',$userStatus);
		$this->assignRef('selectedFilters',$selectedFilters);
	}

	private function _detectHeader(&$lines,&$importObject){
		if(!is_array($importObject->importcolumn)) $importObject->importcolumn = array();

		$dbColumns = array(
						'user_firstname'
							=> array('firstname','SMS_FIRSTNAME', '#first#i'),
						'user_lastname'
							=> array('lastname','SMS_LASTNAME', '#last#i'),
						'user_phone_number'
							=> array('phone','SMS_PHONE', '#(^[0-9\+])#i'),
						'user_birthdate'
							=> array('birthdate','SMS_BIRTHDATE', '#last#i'),
						'user_email'
							=>array('email', 'SMS_EMAIL', '/^([a-z0-9_\'&\.\-\+=])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+$/i')
					);
		foreach($lines[0] as $columnNumber => $oneValue){
			foreach($dbColumns as $oneDbColumnName => $searchParams){
				if(strpos(strtolower($oneValue), $searchParams[0]) !== false || strpos(strtolower($oneValue),JText::_($searchParams[1])) !== false){
					$importObject->importcolumn[$columnNumber] = $oneDbColumnName;
					$importObject->importFirstLine = false;
				}
				elseif($oneDbColumnName == 'user_phone_number' && preg_match($searchParams[2],$oneValue)){
						$importObject->importFirstLine = true;
				}
			}
		}
	}
}
