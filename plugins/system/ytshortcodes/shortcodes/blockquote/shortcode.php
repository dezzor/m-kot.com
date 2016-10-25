<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('_JEXEC') or die;
function blockquoteYTShortcode($atts, $content = null){
	extract(ytshortcode_atts(array(
		"yt_title"  => '',
		"align"  => 'none',
		'border' =>'#666',
		'color'  =>'#fff',
		'width'  =>'auto',
	), $atts));
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/blockquote/css/blockquote.css");
	$source_yt_title = (($yt_title != '') ? "<small>".$yt_title. "</small>" : '');
	return '<blockquote class="yt-clearfix yt-boxquote pull-'. $align.'" style="width:'.$width.'%;border-color:'.$border.';color:'.$color.'">' . parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) . $source_yt_title. '</blockquote>';
}
?>