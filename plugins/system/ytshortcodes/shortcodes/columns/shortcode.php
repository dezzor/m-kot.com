<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function columnsYTShortcode($atts, $content = null ){
	global $grid;
	extract(ytshortcode_atts(array(
		"grid" => 'no'
	), $atts));
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/columns/css/columns.css");
	return '<div class="yt-clearfix yt-show-grid">' . parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) . '</div>';
}
?>