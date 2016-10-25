<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function progress_barYTShortcode($atts,$content = null)
{
	$atts = ytshortcode_atts(array(
			'type_change'		   => '',
            'style_linear'        => 'default',
            'percent'      => 75,
            'show_percent' => 'yes',
            'yt_text'         => '',
            'bar_color'    => '',
            'fill_color'   => '',
            'text_color'   => '',
            'animation'    => 'easeInOutExpo',
            'duration'     => 1.5,
            'delay'        => 0.3,
            'class'        => '',
			'data_type'    =>'',
			'style_circle' =>'circle1',
			'icon_circle'  =>''
        ), $atts, 'progress_bar');
		$id = uniqid('ytp').rand().time();
		$css='';
		$return ='';
        $classes = array('yt-progress-bar', 'yt-progress-bar-style-' . $atts['style_linear'] );
        JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/progress_bar/css/progress-bar.css",'text/css',"screen");
		if($atts['type_change']=="circle")
		{
			if($atts['style_circle']=="circle4")
			{
				$return .='<div class="circle4"><div class="'.$atts['style_circle'].' progress-radial progress-'.$atts['percent'].'"></div>
								<div class="overlay">
									<ul>
										<li><i class="fa fa-'.$atts['icon_circle'].'"></i><b>'.$atts['percent'].'</b>%</li>
										<li>'.$atts['yt_text'].'</li>
									</ul>
								</div></div>';
			}else{
				$return .='<div class="yt-clearfix '.$atts['style_circle'].' progress-radial progress-'.$atts['percent'].'">
							<div class="overlay">
								<ul>
									<li><i class="fa fa-'.$atts['icon_circle'].'"></i><b>'.$atts['percent'].'</b>%</li>
									<li>'.$atts['yt_text'].'</li>
								</ul>
							</div>
					   </div>';
			}
			if($atts['percent'] < 50)
			{
				$css .= '.circle2.progress-'. $atts['percent'].' {background-image: linear-gradient(90deg, #ededed 50%, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0)), linear-gradient('.(int)($atts['percent'] *3.6 +90).'deg, '.$atts['fill_color'].' 50%, #ededed 50%, #ededed);}';
				$css .= '.progress-'. $atts['percent'].',.circle4.progress-'. $atts['percent'].'{background-image: linear-gradient(90deg, #ededed 50%, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0)), linear-gradient('.(int)($atts['percent'] *3.6 +90).'deg, '.$atts['fill_color'].' 50%, #ededed 50%, #ededed);}';
			}else if($atts['percent'] >= 50 && $atts['percent'] < 75)
			{
				$css .= '.circle2.progress-'. $atts['percent'].' {background-image: linear-gradient(-'.(int)(90-($atts['percent']-50) *3.6).'deg, '.$atts['fill_color'].' 50%, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0)), linear-gradient(270deg, '.$atts['fill_color'].' 50%, #ededed 50%, #ededed);}';
				$css .= '.progress-'. $atts['percent'].',.circle4.progress-'. $atts['percent'].'{background-image: linear-gradient(-'.(int)(90 -($atts['percent']-50) *3.6).'deg, '.$atts['fill_color'].' 50%, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0)), linear-gradient(270deg, '.$atts['fill_color'].' 50%, #ededed 50%, #ededed);}';
			}else if($atts['percent'] >= 75)
			{
				$css .= '.circle2.progress-'. $atts['percent'].' {background-image: linear-gradient('.(int)(($atts['percent']-75) *3.6).'deg, '.$atts['fill_color'].' 50%, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0)), linear-gradient(270deg, '.$atts['fill_color'].' 50%, #ededed 50%, #ededed);}';
				$css .= '.progress-'. $atts['percent'].',.circle4.progress-'. $atts['percent'].'{background-image: linear-gradient('.(int)(($atts['percent']-75) *3.6).'deg, '.$atts['fill_color'].' 50%, rgba(0, 0, 0, 0) 50%, rgba(0, 0, 0, 0)), linear-gradient(270deg, '.$atts['fill_color'].' 50%, #ededed 50%, #ededed);}';
			}
			$css .= '.circle1.progress-radial .overlay,.circle2.progress-radial .overlay{background-color:'.$atts['bar_color'].'; color:'.$atts['text_color'].'}.circle3.progress-radial .overlay,.circle4 .overlay ul li{ color:'.$atts['text_color'].'}.circle4.progress-radial{border-color:'.$atts['bar_color'].';color:'.$atts['text_color'].'}';
		}else
		{
			if ($atts['bar_color']) {
				$css .= '#'.$id.'.yt-progress-bar { background-color:' . $atts['bar_color'] . '; border-color:' .darken($atts['bar_color'], '10%') . ';'.'}';
			}
			if (($atts['fill_color']) or ($atts['text_color'])) {
				$fill_color = ($atts['fill_color']) ? 'background-color:' . $atts['fill_color'] . ';' : '';
				$text_color = ($atts['text_color']) ? 'color:' . $atts['text_color'] . ';' : '';
				$css .= '#'.$id.'.yt-progress-bar > span {'.$fill_color. $text_color . '}';
			}

			$text = ($atts['yt_text']) ? '<span class="yt-pb-text">' . $atts['yt_text'] . '</span>' : '';
			$show_percent = ($atts['show_percent'] !== 'no') ? '<span class="yt-pb-percent">'. $atts['percent'] . '%</span>' : '';
			// Add CSS and JS in head
			JHtml::_('jquery.framework');
			JHtml::script(JUri::base()."plugins/system/ytshortcodes/assets/js/jquery.easing.js");
			JHtml::script(JUri::base()."plugins/system/ytshortcodes/assets/js/jquery.appear.js");
			JHtml::script(JUri::base()."plugins/system/ytshortcodes/shortcodes/progress_bar/js/progress-bar.js");
        	$return = '<div id="'.$id.'" class="'.yt_acssc($classes).'"><span class="yt-pb-fill" data-percent="' . $atts['percent'] . '" data-animation="' . $atts['animation'] . '" data-duration="' . $atts['duration'] . '" data-delay="' . $atts['delay'] . '">'.$text.$show_percent.'</span></div>';
		}
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration($css);
        return $return;
}
?>