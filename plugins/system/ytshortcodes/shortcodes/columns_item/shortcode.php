<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function columns_itemYTShortcode($atts, $content = null ){
	global $grid;
	extract(ytshortcode_atts(array(
		"col" => 4,
		"offset" => '',
		"margin" => '0px',
		"padding" => '0px',
		"background" => '#fff',
		"color"		=> '#000'
	), $atts));
	$id = uniqid('columns_item_').rand().time(); 
	$css = '';
	if($grid == "yes")
	{
		$css .= '.'.$id.'{background:'.$background.'; margin: '.$margin.'; padding:'.$padding.'; color:'.$color.'}';
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration($css);
	}
	
	return '<div  class="yt-clearfix '.$id.' yt-col-xs-12 yt-col-sm-'.$col.' yt-col-md-' . $col. ' '.(($offset != '') ? ' yt-col-md-offset-' . $offset : '') .' yt-col-lg-'.$col.'">' . parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) . '</div>';
}
?>