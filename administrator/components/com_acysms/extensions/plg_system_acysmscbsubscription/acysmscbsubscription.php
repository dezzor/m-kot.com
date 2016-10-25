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
defined('_JEXEC') or die('Restricted access');

if(!@include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	return;
};

if(!class_exists('cbTabHandler') || !method_exists($_PLUGINS,'registerFunction') || class_exists('getAcySMSTab')){
	return;
}

$_PLUGINS->registerFunction( 'onUserActive', 'userActivated','getAcySMSTab' );
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'userDelete','getAcySMSTab' );
$_PLUGINS->registerFunction( 'onBeforeUserBlocking', 'onBeforeUserBlocking','getAcySMSTab' );

class getAcySMSTab extends cbTabHandler {

	var $installed = true;
	var $errorMessage = 'This plugin can not work without the AcySMS Component.<br/>Please download it from <a href="http://www.acyba.com">http://www.acyba.com</a> and install it.';

	function getAcySMSTab(){
		if(!class_exists('acysms')){
			$this->installed = false;
		}

		$this->cbTabHandler();
	}

	function getDisplayRegistration($tab, $user, $ui, $postdata) {
		$return = array();

		$visibleGroups = $this->params->get('groups','All');
		$config = ACYSMS::config();

		$groupsClass = ACYSMS::get('class.group');
		$allGroups = $groupsClass->getLists('group_id');

		if(empty($allGroups)) return $return;

		$visibleGroupsArray = array();

		if(strpos($visibleGroups,',') OR is_numeric($visibleGroups)){
			$allvisiblelists = explode(',',$visibleGroups);
			foreach($allGroups as $oneGroup){
				if($oneGroup->published AND in_array($oneGroup->listid,$allvisiblelists)){ $visibleGroupsArray[] = $oneGroup->listid; }
			}
		}elseif(strtolower($visibleGroups) == 'all'){
			foreach($allGroups as $oneGroup){
				if($oneGroup->published){$visibleGroupsArray[] = $oneGroup->listid;}
			}
		}

		$checkedLists = $this->params->get('listschecked','All');
		if(strtolower($checkedLists) == 'all'){ $checkedListsArray = $visibleGroupsArray;}
		elseif(strpos($checkedLists,',') OR is_numeric($checkedLists)){ $checkedListsArray = explode(',',$checkedLists);}
		else{ $checkedListsArray = array();}

		if($this->params->get('addoverlay',false)) JHTML::_('behavior.tooltip');

		$label = $this->params->get('subcaption');
		if(empty($label)) $label = JText::_('SUBSCRIPTION');
		if(!empty($visibleGroupsArray)){
			$listsHtml = '<table style="border:0px;">';
			foreach($visibleGroupsArray as $oneGroup){
				$check = in_array($oneGroup,$checkedListsArray) ? 'checked="checked"' : '';
				$name = $this->params->get('addoverlay',false) ? ACYSMS::tooltip($allGroups[$oneGroup]->description,$allGroups[$oneGroup]->name, '', $allGroups[$oneGroup]->name) : $allGroups[$oneGroup]->name;
				$listsHtml .= '<tr style="border:0px;"><td style="border:0px;"><input type="checkbox" class="acysms_checkbox" id="acy_'.$oneGroup.'" name="acysms[subscription][]" '.$check.' value="'.$oneGroup.'"/></td><td style="border:0px;"><label for="acy_'.$oneGroup.'">'.$name.'</label></td></tr>';
			}
			$listsHtml .= '</table><input type="hidden" name="allVisibleGroups" value="' . implode(',', $visibleGroupsArray) . '" />';
			$return[] = cbTabs::_createPseudoField( $tab, $label, $listsHtml, '', 'acysmsgroups', false );
		}

		return $return;
	}

	function getDisplayTab( $tab, $user, $ui) {

		$my = JFactory::getUser();

		if(file_exists(JPATH_SITE.DS.'components'.DS.'com_cbprofilepro')){
			if(!empty($_REQUEST['task']) && $_REQUEST['task'] == 'userdetails') return $this->getEditTab($tab, $user, $ui); // Edit page
			if(empty($user->user_id) && empty($user->id)) return $this->getDisplayRegistration($tab, $user, $ui, ''); // Registration page
		}

		if(empty($my->id) || $my->id != $user->user_id) return;

		$userClass = ACYSMS::get('class.subscriber');
		$joomUser = $userClass->get($user->email);

		if(empty($joomUser->subid)) return;

		$doc = JFactory::getDocument();
		$config =& ACYSMS::config();
		$cssFrontend = $config->get('css_frontend','default');
		if(!empty($cssFrontend)){
			$doc->addStyleSheet( ACYMAILING_CSS.'component_'.$cssFrontend.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'component_'.$cssFrontend.'.css'));
		}

		if($joomUser->confirmed == 0 && $config->get('require_confirmation') == 1){
			$itemId = $config->get('itemid',0);
			$item = empty($itemId) ? '' : '&Itemid='.$itemId;
			$myLink = acymailing_frontendLink('index.php?subid='.$joomUser->subid.'&option=com_acymailing&ctrl=user&task=confirm&key='.urlencode($joomUser->key).$item, false);
			acymailing_display(JText::_('ACY_SUB_NOT_CONFIRMED') .' '.'<a target="_blank" href="'.$myLink.'">' . JText::_('CONFIRMATION_CLICK') . '</a>', 'warning');
		}

		$groupsClass = ACYSMS::get('class.list');
		$allGroups = $userClass->getSubscription($joomUser->subid,'listid');

		if(acymailing_level(1)){
			$allGroups = $groupsClass->onlyCurrentLanguage($allGroups);
		}

		if(acymailing_level(3)){
			foreach($allGroups as $listid => $oneGroup){
				if(!$allGroups[$listid]->published) continue;
				if(!acymailing_isAllowed($oneGroup->access_sub)){
					$allGroups[$listid]->published = false;
					 continue;
				}
			}
		}

		$lists=$this->params->get('listsprofile','All');

		$visibleGroupsArray = array();
		if(strpos($lists,',') OR is_numeric($lists)){
			$allvisiblelists = explode(',',$lists);
			foreach($allGroups as $oneGroup){
				if($oneGroup->published AND in_array($oneGroup->listid,$allvisiblelists)) {$visibleGroupsArray[] = $oneGroup->listid;}
			}
		}elseif(strtolower($lists) == 'all'){
			foreach($allGroups as $oneGroup){
				if($oneGroup->published){$visibleGroupsArray[] = $oneGroup->listid;}
			}
		}

		if(empty($visibleGroupsArray)) return;

		$return = '';
		$introText = $this->params->get('introtext');
		if(!empty($introText)){
			$return .= '<div class="acymailing_introtext" >'.$introText.'</div>';
		}

		if($this->params->get('display_htmlprofile',1)){
			$return .= '<table><tr><td class="titleCell">'.JText::_('RECEIVE').'</td><td class="fieldCell">'.JHTML::_('select.booleanlist', "acymailing[user][html]" ,'disabled="disabled"',empty($joomUser->subid) ? 1 : $joomUser->html,JText::_('HTML'),JText::_('JOOMEXT_TEXT').'&nbsp;&nbsp;').'</td></tr></table>';
		}

		$return .= '<table class="acycbsubscription"><tr><th>'.JText::_('SUBSCRIPTION').'</th><th>'.JText::_('LIST').'</th></tr>';
		$k = 0;
		$i = 0;
		foreach($visibleGroupsArray as $oneGroupid){
			$return .= '<tr class="row'.$k.'"><td align="center" valign="top"  nowrap="nowrap" ><input type="checkbox" disabled="disabled"'.(($allGroups[$oneGroupid]->status > 0) ? ' checked="checked" ' : '').'/></td>';
			$return .= '<td valign="top"><div class="list_name">'.$allGroups[$oneGroupid]->name.'</div><div class="list_description">'.$allGroups[$oneGroupid]->description.'</div></td></tr>';
			$k = 1-$k;
		}

		$return .= '</table>';

		return $return;
	}

	function saveRegistrationTab($tab, &$user, $ui, $postdata) {
		if((empty($user->id) && empty($user->user_id)) OR !$this->installed) return;

		$subscriberClass = ACYSMS::get('class.subscriber');
		$subscriberClass->checkVisitor = false;
		$subscriberClass->sendConf = false;
		$subscriber = $subscriberClass->get($user->email);

		$subscriber->email = $user->email;
		if(!empty($user->name)) $subscriber->name = $user->name;
		if(!empty($user->user_id)) $subscriber->userid = $user->user_id;
		elseif(!empty($user->id)) $subscriber->userid = $user->id;
		if(!empty($user->confirmed)) $subscriber->confirmed = $user->confirmed;
		if(!empty($user->block)) $subscriber->enabled = 0;

		if(!empty($postdata['acymailing']['user'])){
			$subscriberClass->checkFields($postdata['acymailing']['user'],$subscriber);
		}

		$this->assocFields($user,$subscriber);

		$subscriber->subid = $subscriberClass->save($subscriber);

		$config = ACYSMS::config();
		$statusAdd = (empty($user->confirmed) AND $config->get('require_confirmation',false)) ? 2 : 1;

		$currentSubscription = $subscriberClass->getSubscriptionStatus($subscriber->subid);

		$hiddenLists=$config->get('autosub','None');
		$groupsClass = ACYSMS::get('class.list');
		$allGroups = $groupsClass->getLists('listid');
		if(acymailing_level(1)){
			$allGroups = $groupsClass->onlyCurrentLanguage($allGroups);
		}

		if(strpos($hiddenLists,',') OR is_numeric($hiddenLists)){
			$hiddenListsArray = explode(',',$hiddenLists);
		}elseif(strtolower($hiddenLists) == 'all'){
			$hiddenListsArray = array_keys($allGroups);
		}

		$addlists = array();
		if(!empty($hiddenListsArray)){
			foreach($hiddenListsArray as $idOneList){
				if(!empty($allGroups[$idOneList]->published) && empty($currentSubscription[$idOneList]->status)) $addlists[$statusAdd][] = $idOneList;
			}
		}

		if(!empty($postdata['acymailing']['subscription'])){
			foreach($postdata['acymailing']['subscription'] as $idOneList){
				if(!empty($allGroups[$idOneList]->published) && empty($currentSubscription[$idOneList]->status)) $addlists[$statusAdd][] = $idOneList;
			}
		}

		$listsubClass = ACYSMS::get('class.listsub');
		if(!empty($user->gid)) $listsubClass->gid = $user->gid;
		if(!empty($addlists)){
			$listsubClass->addSubscription($subscriber->subid,$addlists);
		}

		if($this->params->get('updateonregister',0) && !empty($currentSubscription)){
			$updateLists = array();
			$allvisiblelists = JRequest::getString('allVisibleGroups');
			$allvisiblelistsArray = explode(',',$allvisiblelists);
			foreach($allvisiblelistsArray as $oneGroupId){
				if(empty($currentSubscription[intval($oneGroupId)]->status)) continue;

				if(!empty($postdata['acymailing']['subscription']) && in_array(intval($oneGroupId),$postdata['acymailing']['subscription'])){
					if($currentSubscription[intval($oneGroupId)]->status > 0) continue;

					$updateLists[$statusAdd][] = intval($oneGroupId);
				}else{
					if($currentSubscription[intval($oneGroupId)]->status < 0) continue;

					$updateLists[-1][] = intval($oneGroupId);
				}
			}

			if(!empty($updateLists)){
				$listsubClass->updateSubscription($subscriber->subid,$updateLists);
			}
		}

		return;
	}

	function userDelete($user, $success) {
		if(!$this->installed){ return $this->errorMessage;}

		if (!$success) return;

		$userClass = ACYSMS::get('class.subscriber');
		$subid = $userClass->subid($user->email);
		if(!empty($subid)){
			$userClass->delete($subid);
		}
	}

	function userActivated($user, $success) {
		if(!$this->installed){ return $this->errorMessage;}

		if (!$success) return;

		$userClass = ACYSMS::get('class.subscriber');
		$subid = $userClass->subid($user->email);
		if(!empty($subid)){
			if(empty($user->block)){
				$db = JFactory::getDBO();
				$db->setQuery('UPDATE `#__acymailing_subscriber` SET `enabled` = 1 WHERE `subid` = '.$subid.' LIMIT 1');
				$db->query();
			}
			$userClass->confirmSubscription($subid);
		}

		return;
	}

	function onBeforeUserBlocking($user,$block){
		$db =& JFactory::getDBO();
		$db->setQuery('UPDATE `#__acymailing_subscriber` SET `enabled` = '.(1-intval($block)).' WHERE `userid` = '.intval($user->id).' LIMIT 1');
		$db->query();
	}

	function getEditTab( $tab, $user, $ui) {
		if(!$this->installed){ return $this->errorMessage;}

		$app = JFactory::getApplication();

		$config =& ACYSMS::config();
		$cssFrontend = $config->get('css_frontend','default');
		$doc = JFactory::getDocument();
		if(!empty($cssFrontend)){
			$doc->addStyleSheet( ACYMAILING_CSS.'component_'.$cssFrontend.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'component_'.$cssFrontend.'.css'));
		}

		$return = '';

		$lists=$this->params->get('listsprofile','All');
		$displayhtml=$this->params->get('display_htmlprofile',1);

		$userClass = ACYSMS::get('class.subscriber');
		$joomUser = $userClass->get($user->email);

		$db = JFactory::getDBO();

		if(empty($joomUser->subid)){
		}elseif(!empty($user->id) AND (int)$joomUser->userid != (int)$user->id){
			$db->setQuery('UPDATE '.acymailing_table('subscriber').' SET userid = '.(int) $user->id.' WHERE subid = '.(int) $joomUser->subid.' LIMIT 1');
			$db->query();
		}

		if($joomUser->confirmed == 0 && $config->get('require_confirmation') == 1){
			$itemId = $config->get('itemid',0);
			$item = empty($itemId) ? '' : '&Itemid='.$itemId;
			$myLink = acymailing_frontendLink('index.php?subid='.$joomUser->subid.'&option=com_acymailing&ctrl=user&task=confirm&key='.urlencode($joomUser->key).$item, false);
			acymailing_display(JText::_('ACY_SUB_NOT_CONFIRMED') .' '.'<a target="_blank" href="'.$myLink.'">' . JText::_('CONFIRMATION_CLICK') . '</a>', 'warning');
		}

		$groupsClass = ACYSMS::get('class.list');
		if(!empty($joomUser->subid)){
			$allGroups = $userClass->getSubscription($joomUser->subid,'listid');
		}else{
			$allGroups = $groupsClass->getLists('listid');
		}

		if(!$app->isAdmin() AND acymailing_level(1)){
			$allGroups = $groupsClass->onlyCurrentLanguage($allGroups);
		}

		if(!$app->isAdmin() AND acymailing_level(3)){
			$my = JFactory::getUser();
			foreach($allGroups as $listid => $oneGroup){
				if(!$allGroups[$listid]->published) continue;
				if(!acymailing_isAllowed($oneGroup->access_sub)){
					$allGroups[$listid]->published = false;
					 continue;
				}
			}
		}

		if($app->isAdmin()){
			$visibleGroupsArray = array_keys($allGroups);
		}else{
			$visibleGroupsArray = array();
			if(strpos($lists,',') OR is_numeric($lists)){
				$allvisiblelists = explode(',',$lists);
				foreach($allGroups as $oneGroup){
					if($oneGroup->published AND in_array($oneGroup->listid,$allvisiblelists)) {$visibleGroupsArray[] = $oneGroup->listid;}
				}
			}elseif(strtolower($lists) == 'all'){
				foreach($allGroups as $oneGroup){
					if($oneGroup->published){$visibleGroupsArray[] = $oneGroup->listid;}
				}
			}
		}


		if($displayhtml){
			$return .= '<table><tr><td class="titleCell">'.JText::_('RECEIVE').'</td><td class="fieldCell">'.JHTML::_('select.booleanlist', "acymailing[user][html]" ,'',empty($joomUser->subid) ? 1 : $joomUser->html,JText::_('HTML'),JText::_('JOOMEXT_TEXT').'&nbsp;&nbsp;').'</td></tr></table>';
		}

		if(!empty($visibleGroupsArray)){
			if($app->isAdmin()){
				$status = ACYSMS::get('type.status');
			}else{
				$status = ACYSMS::get('type.festatus');
			}


			$return .= '<table class="acycbsubscription"><tr><th>'.JText::_('SUBSCRIPTION').'</th><th>'.JText::_('LIST').'</th></tr>';
			$k = 0;
			foreach($visibleGroupsArray as $oneGroupid){
				$return .= '<tr class="row'.$k.'"><td align="center" valign="top" nowrap="nowrap" >'.$status->display("acymailing[listsub][".$oneGroupid."][status]",@$allGroups[$oneGroupid]->status).'</td>';
				$return .= '<td valign="top"><div class="list_name">'.$allGroups[$oneGroupid]->name.'</div><div class="list_description">'.$allGroups[$oneGroupid]->description.'</div></td></tr>';
				$k = 1-$k;
			}

			$return .= '</table>';
		}

		return $return;
	}

	private function assocFields(&$user,&$subscriber){
		$params = $this->params->get('assocfields');
		if(empty($params)) return;

		$rules = explode(';',str_replace("\n",';',trim($params)));
		if(empty($rules)) return;

		foreach($rules as $oneRule){
			if(!strpos($oneRule,'=')) continue;
			list($acyField,$cbRule) = explode('=',trim($oneRule));
			$cbFields = explode('+',trim($cbRule));

			unset($subscriber->{$acyField});

			foreach($cbFields as $cbField){
				if(!isset($user->_comprofilerUser->{$cbField})) continue;

				if(empty($subscriber->{$acyField})){
					$subscriber->{$acyField} = '';
				}else{
					$subscriber->{$acyField} .= ' ';
				}

				$subscriber->{$acyField} .= $user->_comprofilerUser->{$cbField};
			}
		}
	}

	function saveEditTab($tab, &$user, $ui, $postdata) {

		$subscriberClass = ACYSMS::get('class.subscriber');
		$subscriberClass->triggerFilterBE = true;
		$subscriber = new stdClass();
		$subscriber->subid = $subscriberClass->subid($user->id);
		if(!empty($subscriber->subid)){
			$currentSubid = $subscriberClass->subid($user->email);
			if(!empty($currentSubid) && $subscriber->subid != $currentSubid){
				$subscriberClass->delete($currentSubid);
			}
		}

		if(!empty($postdata['acymailing']['user'])){
			$subscriberClass->checkFields($postdata['acymailing']['user'],$subscriber);
		}

		if(!empty($user->name)) $subscriber->name = $user->name;
		if(!empty($user->email)) $subscriber->email = $user->email;
		$subscriber->enabled = empty($user->block) ? 1 : 0;
		$subscriber->confirmed = $user->confirmed;
		$subscriber->userid = $user->id;

		$this->assocFields($user,$subscriber);

		$subscriber->subid = $subscriberClass->save($subscriber);

		if(empty($subscriber->subid)) return;

		if(!empty($postdata['acymailing']['listsub'])){
			$subscriberClass->saveSubscription($subscriber->subid,$postdata['acymailing']['listsub']);
		}
	}

	function groups($name,$value,$control_name){

		if(!$this->installed){ return $this->errorMessage;}

		JHTML::_('behavior.modal');
		$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=choosegroup&amp;task='.$name.'&amp;control='.$control_name.'&amp;values='.$value;
		$text = '<input class="inputbox" id="'.$control_name.$name.'" name="'.$control_name.'['.$name.']" type="text" size="20" value="'.$value.'">';
		$text .= '<a class="modal" style="display:inline-block;overflow:inherit;position:inherit;" id="link'.$control_name.$name.'" title="'.JText::_('Select one or several Groups').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button onclick="return false">'.JText::_('Select').'</button></a>';

		return $text;

	}
}

