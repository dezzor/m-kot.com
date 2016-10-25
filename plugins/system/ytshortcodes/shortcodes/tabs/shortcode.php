<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
$tab_array = array();
function tabsYTShortcode($atts, $content = null){
	global $tab_array;
	global $index_tab;
	extract(ytshortcode_atts(array(
		"style"	=> '',
		"type"	=> 'basic',
		"width"	=> '100%',
		"align"	=> '',
		"height"=> '200px'
	), $atts));
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/tabs/css/tabs.css");
	parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content));
	$tabs_style =(($style != '')? "style-".$style : "");
	$num = count($tab_array);
	$width != '' ? $width : '100%';
	$height != '' ? $height : '150px';
	$tab = "<div class='yt-tabs ".$type." ".$tabs_style." pull-".$align." yt-clearfix' style='width:".$width."; height:".$height."'><ul class='nav-tabs clearfix'>";

	for($i = 0; $i < $num; $i ++) {
		$active = ($i == 0) ? 'active' : '';
		$tab_id = str_replace(' ', '-', $tab_array[$i]["title"]);

		$tab .= '<li class="'.$active.'"><a href="#' . $tab_id  . $index_tab . '" class="';
		$tab .= $active .'" >'.$tab_array[$i]["icon"].'' . $tab_array[$i]["title"] . '</a></li>';
	}

	$tab .= "</ul>";
	$tab .= "<div class='tab-content'>";

	for($i = 0; $i < $num; $i ++) {
		$active = ($i == 0) ? 'active' : '';
		$tab_id = str_replace(' ', '-', $tab_array[$i]["title"]);

		$tab = $tab . '<div id="' . $tab_id . $index_tab . '" class="clearfix ';
		$tab = $tab . $active . '" >' . $tab_array[$i]["content"] . '</div>';
	}
	$index_tab ++;
	$tab .= "</div></div>";
	$tab_array= array();
	return $tab;

}
?>