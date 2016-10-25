<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_member_config {
	static function get_config() {
	        // member
	        return array(
	        	'style' => array(
                    'type'   => 'select',
                    'values' => array(
                        '1' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE1'),
                        '2' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE2'),
                        '3' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE3'),
                        '4' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE4')
                    ),
                    'default' => 'iframe',
                    'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE'),
                    'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_STYLE_DESC'),
                    'child' => array(
                    	'background' => array(
		                    'type'    => 'color',
		                    'default' => '#ffffff',
		                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_BACKGROUND'),
		                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_BACKGROUND_DESC'),
	                    ),
	                    'color' => array(
                            'type'    => 'color',
                            'default' => '#333333',
                            'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_COLOR'),
                            'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_COLOR_DESC')
                        )
                    ),
                ),
                'border' => array(
                    'type'    => 'border',
                    'default' => '1px solid #cccccc',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_BORDER'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_BORDER_DESC')
                ),
                'shadow' => array(
                    'type'    => 'shadow',
                    'default' => '0 0 0 #eeeeee',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_SHADOW'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_SHADOW_DESC')
                ),
                'radius' => array(
                    'default' => '0px',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_RADIUS'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MEMBER_RADIUS_DESC'),
                    'child'   => array(
                    	'text_align' => array(
	                        'type'    => 'select',
	                        'default' => 'left',
	                        'values'  => array(
		                        'left'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_LEFT'),
		                        'center'  => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CENTER'),
		                        'right'   => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_RIGHT')
		                    ),
		                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ALIGN'),
		                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ALIGN_DESC')
		                ),
                    ),
                ),
                
                'photo' => array(
                    'type'    => 'media',
                    'default' => '',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MEMBER_PHOTO'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_MEMBER_PHOTO_DESC'),
                    'child'   => array(
                    	'name' => array(
		                    'default' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_NAME_DEFAULT'),
		                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_NAME'),
		                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_NAME_DESC')
		                ),
		                'role' => array(
		                    'default' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ROLE_DEFAULT'),
		                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ROLE'),
		                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_ROLE_DESC')
		                ),
                    ),
                ),
                'url' => array(
                    'default' => '',
                    'name'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_URL'),
                    'desc'    => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_URL_DESC')
                ),
                'content' => array(
                	'default' => 'Add content here',
                	'name' => 'Content',
                	'type' => 'textarea'
                ),
	        );
	    }
	}

?>