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

abstract class modSwitchEditorHelper
{

	/**
	 * static method to determine if our plugin is enabled
	 * 
	 * @return  bool
	 */
	static public function isPluginEnabled()
	{
		jimport('joomla.plugin.helper');
		return JPluginHelper::isEnabled('system', 'switcheditor');
	}

	/**
	 * static method to get the list of available editors
	 * 
	 * @return  mixed
	 */
	static public function getEditorOptions()
	{
		static $editors;
		if (is_null($editors))
		{
			$db = JFactory::getDBO();
			$db->setQuery((string) $db->getQuery(true)
					->select('element, name')
					->from('#__extensions')
					->where($db->quoteName('type') . ' = ' . $db->Quote('plugin'))
					->where($db->quoteName('folder') . ' = ' . $db->Quote('editors'))
					->where($db->quoteName('enabled') . ' = 1')
			);
			$editors = $db->loadObjectList();
			// load the language files
			if (!empty($editors))
			{
				foreach ($editors as &$editor)
				{
					JFactory::getLanguage()->load($editor->name . '.sys', JPATH_ADMINISTRATOR);
					$editor->name = JText::_($editor->name);
					// strip of any prefixed "Editor - " bits
					if (false !== strpos('-', $editor->name))
					{
						list($tmp, $name) = explode('-', $editor->name, 2);
						if (isset($name) && !empty($name))
						{
							$editor->name = trim($name);
						}
					}
				}
			}
			// add the "default"
			if (!is_array($editors))
			{
				$editors = array();
			}
			array_unshift($editors, JHtml::_('select.option', '', JText::_('MOD_SWITCHEDITOR_SELECT_EDITOR'), 'element', 'name'));
		}
		return $editors;
	}

	/**
	 * static method to save the user's editor preferences
	 */
	static public function setEditor()
	{
		$user   = JFactory::getUser();
		$editor = JFactory::getApplication()->input->get('adEditor');
		if (!empty($editor) && !$user->guest)
		{
			$user->setParam('editor', $editor);
			return $user->save(true);
		}
		return false;
	}

}
