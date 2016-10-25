<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function memberYTShortcode($atts = null, $content = null) {
		global $text_align_member;
        $atts = ytshortcode_atts(array(
            'style'       => '1',
            'background'   => '#ffffff',
            'color'        => '#333333',
            'shadow'       => '',
            'border'       => '1px solid #cccccc',
            'radius'       => '0',
            'text_align'   => 'left',
            'photo'        => '',
            'name'         => 'John Doe',
            'role'         => 'Designer',
            'url'          => '',
            'class'        => ''
        ), $atts, 'yt_member');
        $icons = array();
        $id = uniqid('ytm').rand().time();
        $css = '';
		$text_align_member = $atts['text_align'];
        $box_shadow = ($atts['shadow']) ? 'box-shadow:' . $atts['shadow'] . '; -webkit-box-shadow:' . $atts['shadow'] . ';' : '';
        $radius = ($atts['radius']) ? 'border-radius:' . $atts['radius'] . ';' : '';

        $css .= '#'.$id.'.yt-member {background-color:' . $atts['background'] . '; color:' . $atts['color'] . '; border:' . $atts['border'] .';'. $radius . $box_shadow .'}';
		$icons = parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content));
		
		
		$icons = (count($icons)) ? '<div class="yt-member-icons" style="text-align:' . 
		$text_align_member . ';"><div class="yt-member-ic">' . $icons . '</div></div>' : '';
        
		
		$multi_photo = array();
        $multi_photo = explode(',',$atts['photo'], 2);

        $member_photo ='';
		if($multi_photo[0] == '')
		{
			$multi_photo[0] = 'plugins/system/ytshortcodes/assets/images/member.svg';
		}
        $member_photo = '<a href="'.$atts['url'].'" title="'.$atts['name'].'"><img src="' . yt_image_media($multi_photo[0]) . '" alt="" /></a>';

       if(isset($multi_photo[1]) )
        $member_photo .= '<a href="'.$atts['url'].'" title="'.$atts['name'].'"><img src="' . yt_image_media($multi_photo[1]) . '" alt=""  /></a>';

        $title = '<span class="yt-member-name">' . $atts['name'] . '</span><span class="yt-member-role">' . $atts['role'] . '</span>';



        // Adding asse
        $doc = JFactory::getDocument();
		$doc->addStyleDeclaration($css);
        JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/member/css/membar.css",'text/css');
        // HTML Layout 
        $return = '<div id="'.$id.'" class="yt-member yt-member-style-'. $atts['style'] .'" data-url="' . $atts['url'] . '">';
            $return .= '<div class="yt-member-photo">'. $member_photo;
                if ($atts['style'] == '2' or $atts['style'] == '4') { $return .= $icons; }
            $return .= '</div>';

            $return .= '<div class="yt-member-info" style="text-align:' . $atts['text_align'] . '">';
                $return .= $title;
            $return .= '</div>';

            if ($atts['style'] != '2' and $atts['style'] != '4') { $return .= $icons; }

        $return .= '</div>';
        return $return;
    }
?>