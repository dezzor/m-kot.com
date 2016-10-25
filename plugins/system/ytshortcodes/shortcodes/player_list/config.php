<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_player_list_config {
	static function get_config() {
	        // player list
	        return array(
	        	'yt_title'=> array(
	        		'name'=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_PLAYER_LIST_TITLE'),
	        		'desc'=> JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_PLAYER_LIST_TITLE_DESC'),
	        		'default' => 'Title player list',
					'child'		=> array(
						'style' => array(
							'type' => 'select',
							'values' => array(
								'dark' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_DARK'),
								'light'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LIGHT')
							),
							'default' => 'dark',
							'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE'), 
							'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE_DESC')
						),
					),
	        	),
				
				'autoplay' => array(
					'type'    => 'bool',
					'default' => 'no',
					'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_AUTOPLAY'),
					'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_AUTOPLAY_DESC'),
					'child'  => array(                        
						'loop' => array(
							'type'    => 'bool',
							'default' => 'no',
							'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LOOP'),
							'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LOOP_DESC')
						),
						'volume' => array(
							'type'    => 'slider',
							'min'     => 0,
							'max'     => 100,
							'step'    => 2,
							'default' => 50,
							'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_VOLUME'),
							'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_VOLUME_DESC')
						)
					)
				),
	        );
	    }
	}

?>