<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function carouselYTShortcode($atts, $content = null){
$return = '';
    $atts = ytshortcode_atts(array(
        'style'                 => '1',
        'source'                => '',
        'limit'                 => 5,
        'items_column0'         => 4,
        'items_column1'         => 4,
        'items_column2'         => 3,
        'items_column3'         => 2,
        'items_column4'         => 1,
        'items'                 => 4,
        'image'                 => 'yes',
        'quality'               => 95,

        'yt_title'              => 'yes',
        'title_link'            => 'yes',
        'title_limit'           => '',
        'intro_text'            => 'yes',
        'intro_text_limit'      => '60',
        'background'            => '',
        'color'                 => '',
        'title_color'           => '',

		'yt_title_k2'           => 'yes',
        'title_link_k2'         => 'yes',
        'title_limit_k2'        => '',
        'intro_text_k2'         => 'yes',
        'intro_text_limit_k2'   => '60',
        'background_k2'         => '',
        'color_k2'              => '',
        'title_color_k2'        => '',

		'yt_title_vm'           => 'yes',
        'title_link_vm'         => 'yes',
        'title_limit_vm'        => '',
        'intro_text_vm'         => 'yes',
        'intro_text_limit_vm'   => '60',
        'background_vm'         => '',
        'color_vm'              => '',
        'title_color_vm'        => '',
        'price_vm'              => 'yes',
        'rating_vm'             => 'yes',
        'add_to_cart_vm'        => 'yes',
        'readmore_vm'           => 'yes',
        'text_readmore_vm'      => 'Read more',
        'description_vm'        => 'yes',
        'description_max_vm'    => '100',

        'date'                  => 'no',
        'category'              => 'no',
        'image_width'           => 360,
        'image_height'          => 320,
        'margin'                => 10,
        'scroll'                => 1,
        'arrows'                => 'no',
        'arrow_position'        => 'default',
        'pagination'            => 'yes',
        'autoplay'              => 'no',
        'delay'                 => 4,
        'speed'                 => 0.35,
        'hoverpause'            => 'no',
        'lazyload'              => 'no',
        'loop'                  => 'yes',
        'class'                 => ''
    ), $atts, 'yt_carousel');

    $id = uniqid('ytc').rand().time();
    $title = "";
    $image = "";
    $intro_text='';
    $description_vm='';
    $css = '';
    $background = '';
    $color = '';
    if(isset($atts['yt_title_vm']) && $atts['yt_title_vm'] !=''){
        $atts['yt_title'] = $atts['yt_title_vm'];
    }elseif(isset($atts['yt_title_k2']) && $atts['yt_title_k2'] !=''){
        $atts['yt_title'] = $atts['yt_title_k2'];
    }else{
        $atts['yt_title'] = $atts['yt_title'];
    }

    if(isset($atts['title_link_vm']) && $atts['title_link_vm'] !=''){
        $atts['title_link'] = $atts['title_link_vm'];
    }elseif(isset($atts['title_link_k2']) && $atts['title_link_k2'] !=''){
        $atts['title_link'] = $atts['title_link_k2'];
    }else{
        $atts['title_link'] = $atts['title_link'];
    }

    if(isset($atts['title_limit_vm']) && $atts['title_limit_vm'] !=''){
        $atts['title_limit'] = $atts['title_limit_vm'];
    }elseif(isset($atts['title_limit_k2']) && $atts['title_limit_k2'] !=''){
        $atts['title_limit'] = $atts['title_limit_k2'];
    }else{
        $atts['title_limit'] = $atts['title_limit'];
    }

    if(isset($atts['intro_text_vm']) && $atts['intro_text_vm'] !=''){
        $atts['intro_text'] = $atts['intro_text_vm'];
    }elseif(isset($atts['intro_text_k2']) && $atts['intro_text_k2'] !=''){
        $atts['intro_text'] = $atts['intro_text_k2'];
    }else{
        $atts['intro_text'] = $atts['intro_text'];
    }

    if(isset($atts['intro_text_limit_vm']) && $atts['intro_text_limit_vm'] !=''){
        $atts['intro_text_limit'] = $atts['intro_text_limit_vm'];
    }elseif(isset($atts['intro_text_limit_k2']) && $atts['intro_text_limit_k2'] !=''){
        $atts['intro_text_limit'] = $atts['intro_text_limit_k2'];
    }else{
        $atts['intro_text_limit'] = $atts['intro_text_limit'];
    }

    if(isset($atts['background_vm']) && $atts['background_vm'] !=''){
        $atts['background'] = $atts['background_vm'];
    }elseif(isset($atts['background_k2']) && $atts['background_k2'] !=''){
        $atts['background'] = $atts['background_k2'];
    }else{
        $atts['background'] = $atts['background'];
    }

    if(isset($atts['color_vm']) && $atts['color_vm'] !=''){
        $atts['color'] = $atts['color_vm'];
    }elseif(isset($atts['color_k2']) && $atts['color_k2'] !=''){
        $atts['color'] = $atts['color_k2'];
    }else{
        $atts['color'] = $atts['color'];
    }

    if(isset($atts['title_color_vm']) && $atts['title_color_vm'] !=''){
        $atts['title_color'] = $atts['title_color_vm'];
    }elseif(isset($atts['title_color_k2']) && $atts['title_color_k2'] !=''){
        $atts['title_color'] = $atts['title_color_k2'];
    }else{
        $atts['title_color'] = $atts['title_color'];
    }

	//$atts['yt_title'] = ((isset($atts['yt_title_k2']) && $atts['yt_title_k2'] !='') ? $atts['yt_title_k2'] : $atts['yt_title']);
	//$atts['title_link'] = ((isset($atts['title_link_k2'])&& $atts['title_link_k2'] !='') ? $atts['title_link_k2'] : $atts['title_link']);
	//$atts['title_limit'] = ((isset($atts['title_limit_k2'])&& $atts['title_limit_k2'] !='') ? $atts['title_limit_k2'] : $atts['title_limit']);
	//$atts['intro_text'] = ((isset($atts['intro_text_k2'])&& $atts['intro_text_k2'] !='') ? $atts['intro_text_k2'] : $atts['intro_text']);
	//$atts['intro_text_limit'] = ((isset($atts['intro_text_limit_k2'])&& $atts['intro_text_limit_k2'] !='') ? $atts['intro_text_limit_k2'] : $atts['intro_text_limit']);
	//$atts['background'] = ((isset($atts['background_k2'])&& $atts['background_k2'] !='') ? $atts['background_k2'] : $atts['background']);
	//$atts['color'] = ((isset($atts['color_k2'])&& $atts['color_k2'] !='') ? $atts['color_k2'] : $atts['color']);
	//$atts['title_color'] = ((isset($atts['title_color_k2'])&& $atts['title_color_k2'] !='') ? $atts['title_color_k2'] : $atts['title_color']);
	
    $lang = JFactory::getLanguage();
    $lang = ($lang->isRTL()) ? 'true' : 'false';

    $slides = (array)get_slides($atts);


    if (($atts['background']) or ($atts['color'])) {

        $background = ($atts['background']) ? 'background-color:'.$atts['background'].';' : '';
        $color = ($atts['color']) ? 'color:'.$atts['color'].';' : '';

        $css .= '.'.$id.' .yt-carousel-slide {' . $background . $color .'}';

        if ($atts['style'] == 3) {
            $css .= '.'.$id.'.yt-carousel-style-3 .yt-carousel-caption:after {border-bottom-color: '.$atts['background'].';}';
        }
    }

    if ($atts['title_color']) {
        $css .= '.'.$id.'.yt-carousel-slide .yt-carousel-slide-title a {color: '.$atts['title_color'].';}';
        $css .= '.'.$id.'.yt-carousel-slide .yt-carousel-slide-title a:hover {color: '.yt_lighten($atts['title_color'],'10%').';}';
    }

    if (count($slides) and ($atts['yt_title'] == 'yes' or $atts['image']  == 'yes' or  $atts['intro_text'] === 'yes')) {
        $source = substr($atts['source'], 0, 5);
        if ($source == 'media'){
            $atts['class'] .= ' yt-carousel-media';
        }
        $return .= '<div id="' . $id . '" class="yt-clearfix ' . $id . ' '.$atts['class'].' yt-carousel yt-carousel-style-'.$atts['style'].' yt-carousel-title-' . $atts['yt_title'] .' arrow-'. $atts['arrow_position'].'" data-autoplay="' . $atts['autoplay'] .'" data-delay="' . $atts['delay'] . '" data-speed="' . $atts['speed'] . '" data-arrows="' . $atts['arrows'] .'" data-pagination="' . $atts['pagination'] . '" data-lazyload="' . $atts['lazyload'] . '" data-hoverpause="' . $atts['hoverpause'] . '" data-items="' . $atts['limit'] . '" data-items_column0="' . $atts['items_column0'] . '" data-items_column1="' . $atts['items_column1'] . '" data-items_column2="' . $atts['items_column2'] . '"  data-items_column3="' . $atts['items_column3'] . '" data-items_column4="' . $atts['items_column4'] . '" data-margin="' . $atts['margin'] . '" data-scroll="' . $atts['scroll'] . '" data-loop="' . $atts['loop'] . '" data-rtl="' . $lang . '"  ><div class="'.$id.' yt-carousel-slides">';
        $limit = 1;
        foreach ((array) $slides as $slide) {
			$image_url ='';
			if($slide['image'])
			{
            	$image_url = yt_image_resize($slide['image'], $atts['image_width'], $atts['image_height'], $atts['quality']);
			}
			else
			{
				$image_url =yt_image_resize('plugins/system/ytshortcodes/assets/images/URL_IMAGES.png',$atts['image_width'], $atts['image_height'], $atts['quality']);
			}

            if($atts['yt_title'] == 'yes' && $slide['title'] ) {

                $title = stripslashes($slide['title']);

                if ($atts['title_limit']) {
                    $title = yt_char_limit($title, $atts['title_limit']);
                }

                if ($atts['title_link'] == "yes") {
                    $title = '<a href="'.$slide['link'].'">'.$title.'</a>';
                }
                $title = '<h3 class="yt-carousel-slide-title">' . $title . '</h3>';
            }

            if ($atts['intro_text'] === 'yes' and isset($slide['introtext'])) {

                $intro_text = $slide['introtext'];

                if ($atts['intro_text_limit']) {
                    $intro_text = yt_char_limit($intro_text, $atts['intro_text_limit']);
                }

                $intro_text =  '<div class="yt-carousel-item-text">'.parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $intro_text)).'</div>';
            }
           
            if($atts['description_vm'] == 'yes' and isset($slide['description']))
            {
                $description_vm = $slide['description'];
                $description_vm =  '<div class="yt-carousel-item-text">'.parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $description_vm)).'</div>';
            }
            $return .= '<div class="'.$id.' yt-carousel-slide">';

                if (isset($image_url) && $atts['image']  == 'yes') {
                    $return .= '<div class="yt-carousel-image">';

                        if (isset($image_url)) {
                            $return .= '<div class="yt-carousel-links">
                                <a class="yt-lightbox-item" href="'. yt_image_media($slide['image']) .'" title="'. strip_tags($title).'">
                                    <i class="fa fa-search"></i>
                                </a>';

                                if ($source != 'media') {
                                    $return .= '<a class="yt-carousel-link" href="'.$slide['link'].'" title="'. strip_tags($title).'">
                                        <i class="fa fa-link"></i>
                                    </a>';
                                }
                            $return .= '</div>';
                        }

                        $return .= '<img src="' . yt_image_media($image_url['url']) . '" alt="' . strip_tags($title) . '" />';
                    $return .= '</div>';
                }

                if (($title) or ($intro_text)) {
                    $return .= '<div class="yt-carousel-caption">'.$title . $intro_text. $description_vm.'</div>';
                }


            $return .= '</div>';

            if ($limit++ == $atts['limit']) break;
        }

        $return .= '</div>';
		JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/assets/css/magnific-popup.css",'text/css',"screen");
        JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/carousel/css/carousel.css");
		JHtml::_('jquery.framework');
        JHtml::script(JUri::base()."plugins/system/ytshortcodes/assets/js/magnific-popup.js");
       
		if (!defined ('OWL_CAROUSEL'))
		{
			JHtml::script(JUri::base()."plugins/system/ytshortcodes/assets/js/owl.carousel.js");
			JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/assets/css/owl.carousel.css",'text/css',"screen");
			define( 'OWL_CAROUSEL', 1 );
		}
        $return .= '</div>';
        $doc = JFactory::getDocument();
		$doc->addStyleDeclaration($css);
        $doc->addScriptDeclaration('
		    jQuery(document).ready(function ($) {
				// Enable carousels
					jQuery(\'.'.$id.'.yt-carousel\').each(function () {
						// Prepare data
						var $carousel = $(this),
							$slides = $(\'.'.$id.'.yt-carousel-slides\'),
							$slide = $(\'.'.$id.'.yt-carousel-slide\'),
							data = $carousel.data();
						// Apply Swiper
						var $owlCarousel = $slides.owlCarousel({
							responsiveClass: true,
							mouseDrag: true,
							autoplayTimeout: data.delay * 1000,
							smartSpeed: data.speed * 1000,
							lazyLoad: (data.lazyload == \'yes\') ? true : false,
							autoplay: (data.autoplay == \'yes\') ? true : false,
							autoplayHoverPause: (data.hoverpause == \'yes\') ? true : false,
							center: (data.center == \'yes\') ? true : false,
							loop: (data.loop == \'yes\') ? true : false,
							margin: data.margin,
							navText: [\'\',\'\'],
							rtl: data.rtl,
                            dots: (data.pagination == \'yes\') ? true : false,
                            nav: (data.arrows == \'yes\') ? true : false,
							responsive:{
						        0:{
						            items: data.items_column4,
						            margin: 0,
						            nav: (data.items > data.items_column4) ? true : false
						        },
                                480: {
                                    items: data.items_column3,
                                    nav: (data.items > data.items_column3) ? true : false
                                },
                                768: {
                                    items: data.items_column2,
                                    nav: (data.items > data.items_column2) ? true : false
                                },
                                992: { 
                                    items: data.items_column1,
                                    nav: (data.items > data.items_column1) ? true : false
                                },
                                1200: {
                                    items: data.items_column0,
                                    nav: (data.items > data.items_column0) ? true : false
                                }
						    }
                           
						});

						// Lightbox for galleries (slider, carousel, custom_gallery)
						$(this).find(\'.yt-lightbox-item\').magnificPopup({
							type: \'image\',
							mainClass: \'mfp-zoom-in mfp-img-mobile\',
							tLoading: \'\', // remove text from preloader
							removalDelay: 400, //delay removal by X to allow out-animation
							gallery: {
								enabled: true,
								navigateByImgClick: true,
								preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
							},
							callbacks: {
								open: function() {
									//overwrite default prev + next function. Add timeout for css3 crossfade animation
									$.magnificPopup.instance.next = function() {
										var self = this;
										self.wrap.removeClass(\'mfp-image-loaded\');
										setTimeout(function() { $.magnificPopup.proto.next.call(self); }, 120);
									}
									$.magnificPopup.instance.prev = function() {
										var self = this;
										self.wrap.removeClass(\'mfp-image-loaded\');
										setTimeout(function() { $.magnificPopup.proto.prev.call(self); }, 120);
									}
								},
								imageLoadComplete: function() {
									var self = this;
									setTimeout(function() { self.wrap.addClass(\'mfp-image-loaded\'); }, 16);
								}
							}
						});

					});
				});
		');
    }

    else
        $return = yt_alert_box('Carousel content not found, please check carousel source settings.', 'warning');
    return $return;
}
?>