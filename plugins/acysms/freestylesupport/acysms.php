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

class SupportActionsAcySMS extends SupportActionsPlugin
{
	var $title = "AcySMS Plugin";
	var $description = "This plugin will allow you to notify your user via SMS each time a ticket is submitted or answered";

	function User_Open($ticket, $params)
	{
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAcySMS_FreestyleSupportSendNotification', array($ticket, $params,'ticketCreated'));
	}

	function User_Reply($ticket, $params){
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAcySMS_FreestyleSupportSendNotification', array($ticket, $params,'ticketReplied'));
	}
}
