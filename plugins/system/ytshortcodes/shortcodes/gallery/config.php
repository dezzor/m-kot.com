<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_gallery_config {
	static function get_config() {
	        // gallery
	        return array(
				'caption' => array('type' => 'select',
					'default' => '0',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CAPTION_STYLE'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CAPTION_STYLE_DESC'),
					'values' => array(
						'0'  => 'default',
						'1'  => 'Caption 1',
						'2'  => 'Caption 2',	
					),
					'child'=> array(
						'hover' => array(
							'type' => 'select',
							'default' => '1',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_HOVER'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_HOVER_DESC'),
							'values' => array(
								'1' => 'Hover 1',
								'2' => 'Hover 2'
							),
						),
						'align' => array('type' => 'select',
							'default' => 'center',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ALIGN'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ALIGN_DESC'),
							'values' => array(
								'left'   => 'Left',
								'center' => 'Center',
								'right'  => 'Right'	,
							),
						)
					),
				),
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
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_PADDING'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_PADDING_DESC')
						),
					),
				),	
				
				'border' => array(
					'type' => 'border',
					'default' => '0px solid #4e9e41',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_BORDER'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_BORDER_DESC'),
				),
				'columns_0' =>array(
					'type' 		=> 'select',
					'default' 	=> '4',
					'values' 	=> YT_Data::columns(),
					'name' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS0'),
					'desc' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS0_DESC'),
					'child' 	=> array(
						'columns_1' => array(
							'type' 		=> 'select',
							'default' 	=> '4',
							'values' 	=> YT_Data::columns(),
							'name' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS1'),
							'desc' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS1_DESC'),
						),
						'columns_2' => array(
							'type' 		=> 'select',
							'default' 	=> '3',
							'values' 	=> YT_Data::columns(),
							'name' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS2'),
							'desc' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS2_DESC'),
						),
					),
				),
				'columns_3' =>array(
					'type' 		=> 'select',
					'default' 	=> '2',
					'values' 	=> YT_Data::columns(),
					'name' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS3'),
					'desc' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS3_DESC'),
					'child' 	=> array(
						'columns_4' => array(
							'type' 		=> 'select',
							'default' 	=> '1',
							'values' 	=> YT_Data::columns(),
							'name' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS4'),
							'desc' 		=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_GALLERY_COLUMNS4_DESC'),
						)
					),
				),
	        );
	    }
	}

?>