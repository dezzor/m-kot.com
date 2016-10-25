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

class ACYSMSsenderprofileType{

	var $allSenderProfileOptions = false;

	var $onlyDefaultSenderProfile = false;

	var $displayDefaultSenderProfileOption = false;

	var $includeJS = false;

	var $customer;

	function display($map,$value){
		$onChangeFunction = '';
		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$groups = $my->gid;
			$condGroup = ' OR senderprofile_access LIKE (\'%,'.$groups.',%\')';
		}else{
			jimport('joomla.access.access');
			$groups = JAccess::getGroupsByUser($my->id,false);
			$condGroup = '';
			foreach($groups as $group){
				$condGroup .= ' OR senderprofile_access LIKE (\'%,'.$group.',%\')';
			}
		}

		$where = array();
		$where[] = '(senderprofile_access = \'all\' '. $condGroup . ')';

		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		if(!$app->isAdmin() && $allowCustomerManagement && !empty($this->customer)){
			if($this->customer->customer_senderprofile_id == 0) $where[] = 'senderprofile_default = 1';
			else $where[] = 'senderprofile_id = '.intval($this->customer->customer_senderprofile_id);
		}

		$query = 'SELECT senderprofile_id, senderprofile_name, senderprofile_default FROM '.ACYSMS::table('senderprofile').' WHERE '.implode(' AND ', $where).' ORDER BY senderprofile_default DESC, senderprofile_name ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$this->values = $db->loadObjectList();

		if(count($this->values) ==  1 && empty($value)) $value = $this->values[0]->senderprofile_id;
		elseif($this->values[0]->senderprofile_default == 1 && empty($value)) $value = $this->values[0]->senderprofile_id;

		if($this->allSenderProfileOptions){
			$newElement = new stdClass();
			$newElement->senderprofile_id = 0;
			$newElement->senderprofile_name = JText::_('SMS_ALL_SENDER_PROFILE');
			array_unshift($this->values, $newElement);
		}

		if($this->displayDefaultSenderProfileOption){
			$newElement = new stdClass();
			$newElement->senderprofile_id = 0;
			$newElement->senderprofile_name = JText::_('SMS_DEFAULT_SENDER_PROFILE');
			array_unshift($this->values, $newElement);
		}

		if($this->includeJS) {
			$senderProfileClass = ACYSMS::get("class.senderprofile");
			$senderProfileHandleMMS = '';
			foreach($this->values as $oneSenderProfile) {
				if($senderProfileClass->getGateway($oneSenderProfile->senderprofile_id)->handleMMS)
					$senderProfileHandleMMS .= $oneSenderProfile->senderprofile_id.',';
			}
			$script = '
			window.addEvent("domready", function() {
				isHandlingMMS(document.getElementById("'.str_replace(array('[',']'),'',$map).'"));
			});

			function isHandlingMMS(dropdownGateway) {
				var gatewayForMMS = ['.trim($senderProfileHandleMMS,',').']
				for(var i=0; i<gatewayForMMS.length; i++) {
					if(dropdownGateway.value == gatewayForMMS[i]) {
						displayHideMMS(false);
						return;
					}
				}
				displayHideMMS(true);
				return;
			}

			function displayHideMMS(hide) {
				var divMMS = document.getElementsByClassName("sms_mms_upload");
				for(var i=0; i<divMMS.length; i++) {
					if(hide)
						divMMS[i].style.display = "none";
					else
						divMMS[i].style.display = "block";
				}
			}
			';

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($script);
			$onChangeFunction = 'isHandlingMMS(this)';
		}

		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" onchange="'.$onChangeFunction.'" style="max-width:200px" size="1" ', 'senderprofile_id', 'senderprofile_name', (int) $value );
	}
}
