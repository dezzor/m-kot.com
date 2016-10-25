<?php
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;

class YT_Shortcode_module_config {
static function get_config() {
        // accordion
         return array(
			'module' => array(
			    'type'=>'module',
				'default' => 'left',
				'name' => JText::_('Module'),
				'desc' => JText::_('Module'),
				),
			
			
			
		);
	}
}
?>