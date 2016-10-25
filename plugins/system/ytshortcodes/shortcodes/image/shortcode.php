<?php
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;

function imageYTShortcode($atts, $content = null){
	global $gwidth, $gheight, $imageArray, $margin_item, $padding_item,$id_uniq;
	$gwidth  = 100;
	$gheight = 100;

	JHtml::_('jquery.framework');
	JHtml::_('jquery.framework');
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/image/css/image.css");
	extract(ytshortcode_atts(array(
		"width"        => 100,
		"height"       => 100,
		"padding"      => '0px',
		"margin"      => '0 auto',
		"hover"        => '1',
		"image"     =>(isset($atts['image'])?$atts['image']:''),
		"title"     =>'',
		"link"     =>'#',
		"target_link"     =>'_self',
		"css_class"     =>'',
	), $atts));

	$padding_item   = ($padding =='0px') ? '' : "padding:".$padding."";
	$margin_item   = ($margin =='0px') ? '' : "margin:".$margin."";
	$id_uniq = uniqid("yt").rand().time();
	$gwidth  = $width;
	$gheight = $height;

	$small_image = array(
		'width' => $gwidth,
		'height' => $gheight,
		'function' => 'resize',
		'function_mode' => 'fill'
	);
	if(strpos($image,'http://')!== false){
		$simage = $src;
	}else if( is_file($image) && strpos($image,'http://')!== true){
		$simage = JURI::base().ImageHelper::init($image, $small_image)->src();
	}else
	{
		$simage =JURI::base(true).'/'.$image;
	}

	$image = '';
	$imageArray = array();
	parse_shortcode($content);
	$image .= '<div class="yt-image clearfix '.$css_class.'" style="'.$margin_item.'; '.$padding_item.'">';
	$image .= '<a href="'.$link.'" target="'.$target_link.'" title="'.$title.'" rel="">';
	$image .= '<img src="'.$simage.'" alt="'.$title.'">';
	$image .= '</a>';
	$image .= '</div>';
	return $image;
}
?>