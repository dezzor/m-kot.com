<?php

/**
 * @package    Switch Editor
 * @subpackage mod_switcheditor
 * @copyright  Copyright (C) 2012 Anything Digital. All rights reserved.
 * @copyright  Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
 * @license    GNU/GPLv2
 */
// no direct access
defined('_JEXEC') or die;

// include the language
JFactory::getLanguage()->load('mod_switcheditor.sys', JPATH_ADMINISTRATOR);

// include the helper only once
require_once dirname(__FILE__) . '/helper.php';

if (modSwitchEditorHelper::isPluginEnabled())
{
	$version = new JVersion();
	$options = modSwitchEditorHelper::getEditorOptions();
	$value = JFactory::getUser()->getParam('editor');
	$path = JModuleHelper::getLayoutPath('mod_switcheditor', $params->get('layout', 'default'));
	if (JFile::exists($path))
	{
		JHtml::_('behavior.framework', true);
		$doc = JFactory::getDocument();
		$doc->addScript('../media/switcheditor/js/switcheditor' . ($version->isCompatible('3.0') ? '' : '-legacy') . '.js');
		$doc->addStyleSheet('../media/switcheditor/css/switcheditor.css');
		require $path;
	}
}
