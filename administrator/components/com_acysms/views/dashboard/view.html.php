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
class dashboardViewDashboard extends acysmsView
{
	function display($tpl = null){
		$config = ACYSMS::config();
		$filters = new stdClass();
		$buttons = array();
		$desc = array();
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();
		$toggleClass = ACYSMS::get('helper.toggle');

		$app = JFactory::getApplication();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration',	'', 'string' );
		$integration = ACYSMS::getIntegration($currentIntegration);


		$desc['subscriber'] = '<ul><li><a href="'.ACYSMS::completeLink('receiver&task=add').'">'.JText::_('SMS_USERS_DESC_ADD').'</a></li>';
		$desc['subscriber'] .= '<li><a href="'.ACYSMS::completeLink('receiver').'">'.JText::_('SMS_USERS_DESC_MANAGE').'</a></li>';
		$desc['subscriber'] .= '<li><a href="'.ACYSMS::completeLink('group').'">'.JText::_('SMS_GROUPS_DESC_MANAGE').'</a></li>';
		$desc['subscriber'] .= '<li><a href="'.ACYSMS::completeLink('data&task=import').'">'.JText::_('SMS_USERS_DESC_IMPORT').'</a></li>';
		$desc['subscriber'] .= '<li><a href="'.ACYSMS::completeLink('data&task=export').'">'.JText::_('SMS_USERS_DESC_EXPORT').'</a></li></ul>';
		$desc['message'] = '<ul><li><a href="'.ACYSMS::completeLink('message&task=add').'">'.JText::_('SMS_MESSAGE_DESC_CREATE').'</a></li>';
		$desc['message'] .= '<li><a href="'.ACYSMS::completeLink('message').'">'.JText::_('SMS_MESSAGE_DESC_MANAGE').'</a></li>';
		$desc['message'] .= '<li><a href="'.ACYSMS::completeLink('answer').'">'.JText::_('SMS_ANSWER_DESC_MANAGE').'</a></li>';
		$desc['message'] .= '<li><a href="'.ACYSMS::completeLink('category').'">'.JText::_('SMS_CATEGORY_DESC_MANAGE').'</a></li></ul>';
		$desc['queue'] = '<ul><li><a href="'.ACYSMS::completeLink('queue').'">'.JText::_('SMS_QUEUE_DESC_MANAGE').'</a></li></ul>';
		$desc['senderprofile'] = '<ul><li><a href="'.ACYSMS::completeLink('senderprofile&task=add').'">'.JText::_('SMS_SENDER_PROFILES_DESC_CREATE').'</a></li>';
		$desc['senderprofile'] .= '<li><a href="'.ACYSMS::completeLink('senderprofile').'">'.JText::_('SMS_SENDER_PROFILES_DESC_MANAGE').'</a></li>';
		$desc['statistics'] = '<ul><li><a href="'.ACYSMS::completeLink('stats').'">'.JText::_('SMS_STAT_DESC_CONFIG').'</a></li></ul>';
		$desc['cpanel'] = '<ul><li><a href="'.ACYSMS::completeLink('cpanel').'">'.JText::_('SMS_CONFIG_DESC_CONFIG').'</a></li></ul>';


		$buttons[] = array('link'=>'subscriber','image'=>'receiver','text'=>JText::_('SMS_RECEIVERS'));
		$buttons[] = array('link'=>'message','image'=>'message','text'=>JText::_('SMS_MESSAGE'));
		$buttons[] = array('link'=>'queue','image'=>'queue','text'=>JText::_('SMS_QUEUE'));
		$buttons[] = array('link'=>'statistics','image'=>'stat','text'=>JText::_('SMS_STATS'));
		$buttons[] = array('link'=>'senderprofile','image'=>'sender','text'=>JText::_('SMS_SENDER_PROFILES'));
		$buttons[] = array('link'=>'cpanel','image'=>'smsconfig','text'=>JText::_('SMS_CONFIGURATION'));
		$htmlbuttons = array();
		foreach($buttons as $oneButton){

				$htmlbuttons[] = $this->_quickiconButton($oneButton['link'],$oneButton['image'],$oneButton['text'],$desc[$oneButton['link']]);

		}
		$users10 = $integration->getDashboardQuery();
		if(!empty($users10)){
			foreach($users10 as $oneUser){
				$phone = $phoneHelper->getValidNum($oneUser->receiver_phone);
				if(!$phone) continue;
				else $phoneArray[] = $db->Quote($phone);
			}
		}

		if(!empty($phoneArray)){
			$query = 'SELECT phone_number FROM #__acysms_phone WHERE phone_number IN ('.implode(',',$phoneArray).')';
			$db->setQuery($query);
			$phones = $db->loadObjectList('phone_number');
		}



		ACYSMS::setTitle( ACYSMS_NAME , 'acysms' ,'dashboard' );
		$bar = JToolBar::getInstance('toolbar');
		if(ACYSMS_J16 && JFactory::getUser()->authorise('core.admin', 'com_acysms')) {
			JToolBarHelper::preferences('com_acysms');
		}
		$bar->appendButton( 'Pophelp','dashboard');
		$this->assignRef('buttons',$htmlbuttons);

		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('users',$users10);
		$this->assignRef('phones',$phones);
		$tabs = ACYSMS::get('helper.tabs');
		$tabs->setOptions(array('useCookie' => true));
		$this->assignRef('tabs',$tabs);
		$this->assignRef('phoneHelper',$phoneHelper);
		$this->assignRef('config',$config);
		$this->assignRef('integration',$integration);

		parent::display($tpl);
	}
	function _quickiconButton( $link, $image, $text,$description)
	{
		$html = '<div style="float:left;width: 100%;"  class="icon"><table width="100%"><tr><td style="text-align: center;" width="100px">';
		$html .= '<span class="icon-48-'.$image.'" style="background-repeat:no-repeat;background-position:center;width:auto;height:48px" title="'.$text.'"> </span>';
		$html .= '<span>'.$text.'</span></td><td style="text-align:left;">'.$description.'</td></tr></table>';
		$html .= '</div>';
		return $html;
	}
}
