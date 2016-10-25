<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function testimonial_itemYTShortcode($atts, $content = null){
	global $id_testimonial,$border_testimonial,$column_testimonial,$background_;
	extract(ytshortcode_atts(array(
		"author" => '',
		"position" => '',
		"avatar" => ''
	), $atts));
    $css='';
	$testimonial_item ='';
	$testimonial_avatar = '';
	if($avatar != '') {
        if(strpos($avatar,'http://')!== false){
            $avatar = $avatar;
        }else if( is_file($avatar) && strpos($avatar,'http://')!== true){
            $avatar = JURI::base().$avatar;
        }
        $testimonial_avatar .='<img src="' . $avatar . '" alt="'.$atts['author'].'" width="150" height="150" style="border-radius:50%; width:auto; margin:0 auto; max-width:150px; max-height:150px"/> ';
    };
	$testimonial_item = '<div class="item" style="border:'.$border_testimonial.'; '.(($background_!= '') ? "background:rgba(255,255,255,0.8)" : "").' ">
							<div class="item-wrap">
								<div class="item-wrap-inner">
									<div class="item-image">
										<div class="item-img-info">
											'.$testimonial_avatar.'
										</div>
										<div class="item-info">
											'.parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)).'
											<h5>'.$author.'</h5>
											<span class="position">'.$position.'</span>
										</div>
									</div>
								</div>
							</div>
						</div>';
	return $testimonial_item;
}
?>
