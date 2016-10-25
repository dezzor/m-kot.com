<?php

/**
 * @package    Switch Editor
 * @copyright  Copyright (C) 2012 Anything Digital. All rights reserved.
 * @copyright  Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
 * @license    GNU/GPLv2
 */
// no direct access
defined('_JEXEC') or die;

class pkg_SwitchEditorInstallerScript
{
	protected $db;
	
	public function __construct()
	{
		$this->db = JFactory::getDbo();
	}

	public function postflight($type, $parent)
	{
		if ('uninstall' == $type)
		{
			return;
		}
		// update the plugin
		$this->_fireQuery($this->db->getQuery(true)
				->update('#__extensions')
				->set($this->db->quoteName('enabled') . '=1')
				->where($this->db->quoteName('element') . '=' . $this->db->Quote('switcheditor'))
				->where($this->db->quoteName('type') . '=' . $this->db->Quote('plugin'))
		);
		// get the module id
		$this->db->setQuery((string) $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from('#__modules')
				->where($this->db->quoteName('module') . '=' . $this->db->Quote('mod_switcheditor'))
				->where($this->db->quoteName('client_id') . '=1')
		);
		$id = $this->db->loadResult();
		if ($id)
		{
			$version = new JVersion;
			$id = (int) $id;
			// update the module position & publication
			$this->_fireQuery($this->db->getQuery(true)
					->update('#__modules')
					->set($this->db->quoteName('published') . '=1')
					->set($this->db->quoteName('ordering') . '=' . ($version->isCompatible('3.0') ? '-' : '') . '9999')
					->set($this->db->quoteName('position') . '=' . $this->db->Quote('status'))
					->where($this->db->quoteName('id') . '=' . $id)
			);
			// remove any previous module menu entries
			$this->_fireQuery($this->db->getQuery(true)->delete('#__modules_menu')->where($this->db->quoteName('moduleid') . '=' . $id));
			// insert a new module menu entry
			$this->_fireQuery($this->db->getQuery(true)->insert('#__modules_menu')->values($id . ', 0'));
		}
	}

	private function _fireQuery($query)
	{
		$this->db->setQuery((string) $query);
		return $this->db->query();
	}

}
