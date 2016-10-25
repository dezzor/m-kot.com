<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_member_item_config {
	static function get_config() {
	        // member
	        return array(
                'icon' => array(
                    'type'    => 'icon',
                    'default' => 'icon: envelope-o',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ICON'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ICON_DESC'),
                    'child'   => array(
                        'icon_color' => array(
                            'type'    => 'color',
                            'default' => '#444444',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ICON_COLOR'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ICON_COLOR_DESC')
                        )
                    )
                ),
                'icon_title' => array(
                    'default' => '',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ICON_TITLE'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_TITLE_DESC'),
                    'child'   => array(
                        'icon_url' => array(
                            'default' => '',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ICON_URL'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_URL_DESC')
                        )
                    )
                )
	        );
	    }
	}

?>