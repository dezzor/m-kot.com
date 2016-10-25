<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
	class YT_Shortcode_charts_item_config {
	static function get_config() {
	        // charts
	        return array(
				'label_name_line' => array(
					'default' => '',
					'group'	  => 'line',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABEL_NAME'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABEL_NAME_DESC'),
					'child' => array(
						'data_line' => array(
							'default' => '',
							'group'	  => 'line',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA_DESC'),
						),
					)
				),
				'label_name_bar' => array(
					'default' => '',
					'group'	  => 'bar',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABEL_NAME'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABEL_NAME_DESC'),
					'child' => array(
						'data_bar' => array(
							'default' => '',
							'group'	  => 'bar',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA_DESC'),
						),
					)
				),
				'label_name_radar' => array(
					'default' => '',
					'group'	  => 'radar',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABEL_NAME'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABEL_NAME_DESC'),
					'child' => array(
						'data_radar' => array(
							'default' => '',
							'group'	  => 'radar',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA_DESC'),
						),
					)
				),
	        	'fill_color_line' => array(
					'type'	  => 'color',	
					'default' => '#f00',
					'group'	  => 'line',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_FILL_COLOR'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_FILL_COLOR_DESC'),
					'child' => array(
						'stroke_color_line' => array(
							'type'	  => 'color',
							'group'	  => 'line',
							'default' => '#f00',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_STROKE_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_STROKE_COLOR_DESC'),
						),
						'point_color_line' => array(
							'type'	  => 'color',
							'group'	  => 'line',
							'default' => '#f00',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_POINT_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_POINT_COLOR_DESC'),
						),
					),
				),
				'fill_color_bar' => array(
					'type'	  => 'color',	
					'default' => '#f00',
					'group'	  => 'bar',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_FILL_COLOR'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_FILL_COLOR_DESC'),
					'child' => array(
						'stroke_color_bar' => array(
							'type'	  => 'color',
							'group'	  => 'bar',
							'default' => '#f00',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_STROKE_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_STROKE_COLOR_DESC'),
						),
						'point_color_bar' => array(
							'type'	  => 'color',
							'group'	  => 'bar',
							'default' => '#f00',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_POINT_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_POINT_COLOR_DESC'),
						),
					),
				),
				'fill_color_radar' => array(
					'type'	  => 'color',	
					'default' => '#f00',
					'group'	  => 'radar',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_FILL_COLOR'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_FILL_COLOR_DESC'),
					'child' => array(
						'stroke_color_radar' => array(
							'type'	  => 'color',
							'group'	  => 'radar',
							'default' => '#f00',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_STROKE_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_STROKE_COLOR_DESC'),
						),
						'point_color_radar' => array(
							'type'	  => 'color',
							'group'	  => 'radar',
							'default' => '#f00',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_POINT_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_POINT_COLOR_DESC'),
						),
					),
				),
				'labels_doughnut' => array(
					'group'   => 'doughnut',
					'default' => 'January',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABELS'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABELS__DESC'),	
					'child' => array(
						'data_doughnut' => array(
							'default' => '',
							'group'	  => 'doughnut',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA__DESC'),
						),
						'highlight_doughnut' => array(
							'default' => '#f00',
							'group'	  => 'doughnut',
							'type'	  => 'color',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_HIGHLIGHT'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_HIGHLIGHT_DESC'),
						),
						'color_doughnut'  => array(
							'default' => '#f00',
							'group'	  => 'doughnut',
							'type'	  => 'color',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_COLOR_DESC'),
						),
					),
				),
				'labels_pie' => array(
					'group'   => 'pie',
					'default' => 'January',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABELS'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABELS__DESC'),	
					'child' => array(
						'data_pie' => array(
							'default' => '',
							'group'	  => 'pie',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA__DESC'),
						),
						'highlight_pie' => array(
							'default' => '#f00',
							'group'	  => 'pie',
							'type'	  => 'color',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_HIGHLIGHT'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_HIGHLIGHT_DESC'),
						),
						'color_pie'  => array(
							'default' => '#f00',
							'group'	  => 'pie',
							'type'	  => 'color',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_COLOR_DESC'),
						),
					),
				),
				'labels_polar' => array(
					'group'   => 'polar',
					'default' => 'January',
					'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABELS'),
					'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_LABELS__DESC'),	
					'child' => array(
						'data_polar' => array(
							'default' => '',
							'group'	  => 'polar',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_DATA__DESC'),
						),
						'highlight_polar' => array(
							'default' => '#f00',
							'group'	  => 'polar',
							'type'	  => 'color',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_HIGHLIGHT'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_HIGHLIGHT_DESC'),
						),
						'color_polar'  => array(
							'default' => '#f00',
							'group'	  => 'polar',
							'type'	  => 'color',
							'name' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_COLOR'),
							'desc' => JText::_('PLG_SYSTEM_YOUTECH_SHORTCODES_CHARTS_COLOR_DESC'),
						),
					),
				),
	        );
	    }
	}

?>