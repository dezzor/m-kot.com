<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_image_config {
	static function get_config() {
	        // gallery
	        return array(
				'width' => array(
					'default' => '500',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_WIDTH'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_WIDTH_DESC'),	
					'child' => array(
						'height' => array(
							'default' => '500',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_HEIGHT'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_HEIGHT_DESC')	
						),
						'padding' => array(
							'default' => '0px',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_IMAGE_PADDING'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_IMAGE_PADDING_DESC')
						),
						'margin' => array(
							'default' => '0 auto',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MARGIN'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MARGIN_DESC')
						),
					),
				),
				'image' => array(
					'type' => 'media',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_IMAGE_ITEM_SRC'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_IMAGE_ITEM_SRC_DESC'),
					'default' => '',
					'child' => array(
						'title' => array(
							'default' => '',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TITLE_IMAGE'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TITLE_IMAGE_DESC')
						),
					),
				),
				'link' => array(
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LINK'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LINK_DESC'),
					'default' => '#',
					'child' => array(
						'target_link' => array('type' => 'select',
							'default' => 'center',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TARGET_LINK'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TARGET_LINK_DESC'),
							'values' => array(
								'_self'   => 'Same window',
								'_blank' => 'New window',
							),
						),
						'css_class' => array(
							'default' => '',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CSS_CLASS'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CSS_CLASS_DESC')
						),
					),
				)
	        );
	    }
	}

?>