<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function charts_itemYTShortcode($atts,$content = null)
{
	global $type_change_chart;
	extract(ytshortcode_atts( array(
		'data_line'  			=>'',
		'label_name_line'		=>'',
		'fill_color_line'		=>'',
		'stroke_color_line'		=>'',
		'point_color_line'		=>'',
		'data_bar'  			=>'',
		'label_name_bar'		=>'',
		'fill_color_bar'		=>'',
		'stroke_color_bar'		=>'',
		'point_color_bar'		=>'',
		'data_radar'  			=>'',
		'label_name_radar'		=>'',
		'fill_color_radar'		=>'',
		'stroke_color_radar'	=>'',
		'point_color_radar'		=>'',
		'labels_doughnut'		=>'',
		'data_doughnut'			=>'',
		'color_doughnut'  		=>'',
		'highlight_doughnut'	=>'',
		'labels_pie'			=>'',
		'data_pie'				=>'',
		'color_pie'  			=>'',
		'highlight_pie'			=>'',
		'labels_polar'			=>'',
		'data_polar'			=>'',
		'color_polar'  			=>'',
		'highlight_polar'		=>'',
	),$atts));
	$data           = '';
	$fill_color 	= '';
	$stroke_color 	= '';
	$point_color 	= '';
	if(isset($data_line) && ($data_line != ''))
	{
		$data 			= $data_line;
		$label_name		= $label_name_line;
		$fill_color 	= $fill_color_line;
		$stroke_color 	= $stroke_color_line;
		$point_color 	= $point_color_line;
	}
	if(isset($data_bar) && ($data_bar != ''))
	{
		$data 			= $data_bar;
		$label_name		= $label_name_bar;
		$fill_color 	= $fill_color_bar;
		$stroke_color 	= $stroke_color_bar;
		$point_color 	= $point_color_bar;
	}
	if(isset($data_radar) && ($data_radar != ''))
	{
		$data 			= $data_radar;
		$label_name		= $label_name_radar;
		$fill_color 	= $fill_color_radar;
		$stroke_color 	= $stroke_color_radar;
		$point_color 	= $point_color_radar;
	}
	if(isset($data_doughnut) && ($data_doughnut != ''))
	{
		$labels			= $labels_doughnut;
		$data 			= $data_doughnut;
		$color			= $color_doughnut;
		$highlight		= $highlight_doughnut;
	}
	if(isset($data_pie) && ($data_pie != ''))
	{
		$labels			= $labels_pie;
		$data 			= $data_pie;
		$color			= $color_pie;
		$highlight		= $highlight_pie;
	}
	if(isset($data_polar) && ($data_polar != ''))
	{
		$labels			= $labels_polar;
		$data 			= $data_polar;
		$color			= $color_polar;
		$highlight		= $highlight_polar;
	}
	//TH1
	if(strtolower($type_change_chart)=='doughnut' || strtolower($type_change_chart)=='pie' || strtolower($type_change_chart)=='polar')
	{
		$return = "{ value:$data,color:\"$color\",highlight:\"$highlight\",label:\"$labels\"},";
	}
	//TH2
	else
	{
		$return = '{ label: "'.$label_name.'",
					fillColor: "'.$fill_color.'",
					strokeColor: "'.$stroke_color.'",
					pointColor: "'.$point_color.'",
					pointStrokeColor: "#fff",
					pointHighlightFill: "#000",
					pointHighlightStroke: "rgba(220,220,220,1)",
					data: ['.$data.']},';
	

	}
	
	return $return;
}
?>