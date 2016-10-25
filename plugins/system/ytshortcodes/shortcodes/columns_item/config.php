<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
class YT_Shortcode_columns_item_config {
static function get_config() {
	// columns item
	return array(
		'col' => array('type' => 'slider',
			'default' => 4,
			'min' => 1,
			'max' => 12,
			'step' => 1,
			'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_COLUMNS_COL'),
			'desc' => JText::_("PLG_SYSTEM_YOUTECH_SHORTCODES_COLUMNS_COL_DESC"),
			'child' => array(
				'offset' => array('type' => 'slider',
					'default' => 0,
					'min' => 0,
					'max' => 12,
					'step' => 1,
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_COLUMNS_OFFSET'),
					'desc' => JText::_("PLG_SYSTEM_YOUTECH_SHORTCODES_COLUMNS_OFFSET_DESC"),
				),
			),
		),
		'margin' => array(
			'default' => '0px',
			'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MARGIN'),
			'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MARGIN_DESC'),
			'child'	  => array(
				'padding' => array(
					'default' => '0px',
					'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_PADDING'),
					'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_PADDING_DESC'),
				),
			)	
		),
		'background'=>array(
			'type' => 'color',
			'default'=> '#e1e1e1',
			'name' =>JText::_("PLG_SYSTEM_YOUTECH_SHORTCODES_BACKGROUND"),
			'desc' => JText::_("PLG_SYSTEM_YOUTECH_SHORTCODES_BACKGROUND_DESC"),
			'child' => array(
				'color' => array(
					'type' => 'color',
					'default' => '#ccc',
					'name' => JText::_("PLG_SYSTEM_YOUTECH_SHORTCODES_COLOR"),
					'desc' => JText::_("PLG_SYSTEM_YOUTECH_SHORTCODES_COLOR_DESC"),		
				),
			),
		),		
		'content'  => array(
			'type' => 'textarea',
			'default' => 'Add content here',
			'name' => 'Content'
		),	
	);
}
}

?>