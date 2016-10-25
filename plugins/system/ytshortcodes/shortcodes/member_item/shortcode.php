<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function member_itemYTShortcode($atts = null, $content = null) {
	global $text_align_member;
        $atts = ytshortcode_atts(array(
            'icon'       => 'icon: home',
            'icon_url'   => '',
            'icon_color' => '#444444',
            'icon_title' => ''
        ), $atts, 'yt_member_item');
		$icons = '';
		$atts['icon'] = ($atts['icon'] != '' ? $atts['icon'] : 'icon: home');
		if (strpos($atts['icon'], 'icon:') !== false) { $icon = '<i class="fa fa-' . trim(str_replace('icon:', '', $atts['icon'])) . '" style="color:' . $atts['icon_color'] . '"></i>';}
		else { $icon = '<img src="' . yt_image_media($atts['icon']) . '" width="16" height="16" alt="" />'; }
		$icons = '<a href="' . $atts['icon_url'] . '" title="' . $atts['icon_title'] . '" class="yt-memeber-icon yt-m-' . trim(str_replace('icon:', '', $atts['icon'])) . '" target="_blank" style="text-align:center"> ' . $icon . ' </a>';
		
        return $icons;
    }
?>