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

class ACYSMSmenuHelper{
	function display($selected = ''){

		if(version_compare(JVERSION,'1.6.0','<')){
			$doc = JFactory::getDocument();
			$doc->addStyleDeclaration(" #submenu-box{display:none !important;} ");
		}

		$selected = substr($selected,0,5);

		$config = ACYSMS::config();
		$integration = ACYSMS::getIntegration();
		$mainmenu = array();
		$submenu = array();

		if(ACYSMS::isAllowed($config->get('acl_receivers_manage','all'))){
			$mainmenu['receiver'] = array(JText::_('SMS_RECEIVERS'), 'index.php?option=com_acysms&ctrl=receiver','smsicon-16_receiver');
			$submenu['receiver'] = array();
			$submenu['receiver'][] = array(JText::_('SMS_RECEIVERS'), 'index.php?option=com_acysms&ctrl=receiver','smsicon-16_receiver');

			if(ACYSMS::isAllowed($config->get('acl_groups_manage','all')))	$submenu['receiver'][] = array(JText::_('SMS_GROUPS'), 'index.php?option=com_acysms&ctrl=group','smsicon-16_group');

			if(ACYSMS::isAllowed($config->get('acl_receivers_import','all')) && $integration->componentName == 'acysms')	$submenu['receiver'][] = array(JText::_('SMS_IMPORT'), 'index.php?option=com_acysms&ctrl=data&task=import','smsicon-16_import');
			if(ACYSMS::isAllowed($config->get('acl_receivers_export','all')) && $integration->componentName == 'acysms')	$submenu['receiver'][] = array(JText::_('SMS_EXPORT'), 'index.php?option=com_acysms&ctrl=data&task=export','smsicon-16_export');
		}

		if(ACYSMS::isAllowed($config->get('acl_messages_manage','all'))){
			$mainmenu['message'] = array(JText::_('SMS_MESSAGES'), 'index.php?option=com_acysms&ctrl=message','smsicon-16_message');
			$submenu['message'] = array();
			$submenu['message'][] = array(JText::_('SMS_MESSAGES'), 'index.php?option=com_acysms&ctrl=message','smsicon-16_message');
			if(ACYSMS::isAllowed($config->get('acl_categories_manage','all')))	$submenu['message'][]= array(JText::_('SMS_CATEGORIES'), 'index.php?option=com_acysms&ctrl=category','smsicon-16_categories');
		}

		if(ACYSMS::isAllowed($config->get('acl_answers_manage','all'))){
			$mainmenu['answer'] = array(JText::_('SMS_ANSWERS'), 'index.php?option=com_acysms&ctrl=answer','smsicon-16_answer');
			$submenu['answer'] = array();
			$submenu['answer'][] = array(JText::_('SMS_ANSWERS'), 'index.php?option=com_acysms&ctrl=answer','smsicon-16_answer');
			if(ACYSMS::isAllowed($config->get('acl_answers_trigger_manage','all'))) $submenu['answer'][] = array(JText::_('SMS_ANSWERS_TRIGGER'), 'index.php?option=com_acysms&ctrl=answertrigger','smsicon-16_answertrigger');
		}

		if(ACYSMS::isAllowed($config->get('acl_queue_manage','all'))) $mainmenu['queue'] = array(JText::_('SMS_QUEUE'), 'index.php?option=com_acysms&ctrl=queue','smsicon-16_queue');

		if(!ACYSMS_J16 || JFactory::getUser()->authorise('core.admin', 'com_acysms')){
			if(ACYSMS::isAllowed($config->get('acl_stats_manage','all')))	$mainmenu['stats'] = array(JText::_('SMS_STATS'), 'index.php?option=com_acysms&ctrl=stats','smsicon-16_stats');
		}

		if(ACYSMS::isAllowed($config->get('acl_configuration_manage','all'))){
			$mainmenu['config'] = array(JText::_('SMS_CONFIGURATION'), 'index.php?option=com_acysms&ctrl=cpanel','smsicon-16_config');
			$submenu['config'] = array();
			$submenu['config'][] = array(JText::_('SMS_CONFIGURATION'), 'index.php?option=com_acysms&ctrl=cpanel','smsicon-16_config');
			$submenu['config'][] = array(JText::_('SMS_EXTRA_FIELDS'), 'index.php?option=com_acysms&ctrl=fields','smsicon-16_fields');
			if(ACYSMS::isAllowed($config->get('acl_sender_profiles_manage','all')))	$submenu['config'][] = array(JText::_('SMS_SENDER_PROFILES'), 'index.php?option=com_acysms&ctrl=senderprofile','smsicon-16_sender');
			$submenu['config'][] = array(JText::_('SMS_CUSTOMERS'), 'index.php?option=com_acysms&ctrl=customer','smsicon-16_customers');
		}

		$doc = JFactory::getDocument();
		$doc->addStyleSheet( ACYSMS_CSS.'acysmsmenu.css');

		if(!ACYSMS_J30) {
			$menu = '<div id="acysmsmenutop"><ul>';
			foreach($mainmenu as $id => $oneMenu){
				$menu .= '<li class="acysmsmainmenu'.(!empty($submenu[$id]) ? ' parentmenu' : ' singlemenu').'"';
				if($selected == substr($id,0,5)) $menu .= ' id="acysmsselectedmenu"';
				$menu .= ' >';
				$menu .= '<a class="acysmsmainmenulink '.$oneMenu[2].'" href="'.$oneMenu[1].'" >'.$oneMenu[0].'</a>';
				if(!empty($submenu[$id])){
					$menu .= '<ul>';
					foreach($submenu[$id] as $subelement){
						$menu .= '<li class="acysubmenu "><a class="acysubmenulink '.$subelement[2].'" href="'.$subelement[1].'" title="'.$subelement[0].'">'.$subelement[0].'</a></li>';
					}
					$menu .= '</ul>';
				}
				$menu .= '</li>';
			}
			$menu .= '</ul></div><div style="clear:left"></div>';
		} else {
			$menu = '<div id="acysmsnavbar" class="navbar"><div class="navbar-inner" style="display:block !important;"><div class="container"><div class="nav"><ul id="acysmsmenutop_j3" class="nav">';
			foreach($mainmenu as $id => $oneMenu) {
				$sel = '';
				if($selected == substr($id,0,5)) $sel = ' sel';
				$menu .= '<li class="dropdown'.$sel.'"><a class="dropdown-toggle'.$sel.'" '.(!empty($submenu[$id]) ? 'data-toggle="dropdown"' : '').' href="'.(!empty($submenu[$id]) ? '#' : $oneMenu[1]).'"><i class="'.$oneMenu[2].'"></i> '.$oneMenu[0]. (!empty($submenu[$id]) ? '<span class="caret"></span>' : '') . '</a>';
				if(!empty($submenu[$id])){
					$menu .= '<ul class="dropdown-menu">';
					foreach($submenu[$id] as $subelement){
						$menu .= '<li class="acysubmenu "><a class="acysubmenulink" href="'.$subelement[1].'" title="'.$subelement[0].'"><i class="'.$subelement[2].'"></i> '.$subelement[0].'</a></li>';
					}
					$menu .= '</ul>';
				}
				$menu .= '</li>';
			}
			$menu .= '</ul></div></div></div></div>';
		}
		return $menu;

	}
}
