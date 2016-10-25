<?php 
/*
* @package   YouTech Shortcodes
* @author    YouTech Company http://smartaddons.com/
* @copyright Copyright (C) 2015 YouTech Company
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;
function player_listYTShortcode($atts, $content = null){
	global $playerlist_count ;
	extract(ytshortcode_atts(array(
		"yt_title"		=> '',
		"src"		=> '',
		'style'      => 'dark',
		'url'      => false,
		'width'    => '100%',
		'title'    => '',
		'autoplay' => 'no',
		'volume' => 50,
		'loop'     => 'no',
		'class'    => ''
	), $atts));
	$playerlist_count ++;

	if($playerlist_count == 1){
		
		$id = uniqid('yt_playlist').rand().time();
		$audio_script  = "<script>
		jQuery(document).ready(function($) {
			// Load in the first track
			var audio = $('#". $id ."_container'),
				first = $('ul.yt-playlist a').attr('data-src');
			$('ul.yt-playlist li').first().addClass('playing');
			audio.attr('data-audio', first);
		});
			
		</script>";
	}
	JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/player_list/css/play_list.css");
	// Add CSS JS file in head
	
	if (!defined ('JPLAYER'))
	{		
		JHtml::stylesheet(JUri::base()."plugins/system/ytshortcodes/shortcodes/player_list/css/jplayer.skin.css");
		JHtml::_('jquery.framework');
		JHtml::script(JUri::base()."plugins/system/ytshortcodes/shortcodes/player_list/js/jplayer.js");
		define( 'JPLAYER', 1 );
	}
	$audiojs = '<script> jQuery(document).ready(function ($) {

	$(".yt-playlist").each(function (index) {

		var $this = $(this),
			id = $this.data("id"),
			selector = "#" + id,
			$player = $(selector),
			audio = $this.data("audio"),
			volume = $this.data("volume"),
			swf = $this.data("swf");
		
	    $player.jPlayer({
			
	        // Extra Settings
	        swfPath: swf,
	        solution: "html, flash",
	        volume: volume/100,
	        smoothPlayBar: false,
	        keyEnabled: true,
	        remainingDuration: false,
	        toggleDuration: false,
	        errorAlerts: false,
	        warningAlerts: false,
	        supplied: "mp3, ogg",

	        // CSS Selectors
	        size: {
	            width: "100%",
	            height: "auto"
	        },
	        cssSelectorAncestor: selector + "_container",
	        cssSelector: {
	            play: ".play",
	            pause: ".pause",
	            seekBar: ".seekBar",
	            playBar: ".playBar",
	            mute: ".right-volume .mute",
	            unmute: ".right-volume .unmute",
	            volumeBar: ".volume-control",
	            volumeBarValue: ".volume-control .volume-value",
	            currentTime: ".timer.current",
	            duration: ".timer.duration",
	            gui: ".controls",
	            noSolution: ".noSolution"
	        },

	        ready: function (event) {

	            if(event.jPlayer.status.noVolume) {
	                // Add a class and then CSS rules deal with it.
	                $player.find(".controls .jpprogress-block").css({ margin: "0 10px 0 45px"});
	            }

	            $(this).jPlayer("setMedia", {
	                title: "Big Buck Bunny Trailer",
	               	mp3: audio
	            });
	            if ($this.data("autoplay") === "yes") $player.jPlayer("play");

	            if ($this.data("loop") === "yes") $player.bind($.jPlayer.event.ended + ".repeat", function () {
	            	$player.jPlayer("play");
	            });
				// Load in a track on click
				$("ul.yt-playlist li").click(function(e) {
				var audio_ = $("#'. $id .'_container");
					e.preventDefault();
					$(this).addClass("playing").siblings().removeClass("playing");
					audio_.attr("data-audio", $("a", this).attr("data-src"));
					$player.jPlayer("setMedia", {
						title: "Big Buck Bunny Trailer",
						mp3: $("a", this).attr("data-src")
					});
					$player.jPlayer("pause");
					$player.jPlayer("play");
				});
	        },
	        play: function() {
	            //$this.find(".playerScreen .video-play").stop(true,true).fadeOut(150);
	            $(this).on("click", function() { $(this).jPlayer("pause");});
	            //$(this).jPlayer("pauseOthers");
				
				
	        },
	        pause: function() {
	            //$this.find(".playerScreen .video-play").stop(true,true).fadeIn(350);
	            $(this).unbind("click");
	        }
	    });
	});
});</script>';
	// Output HTML
        $output = '<div id="' . $id . '_container" class="yt-clearfix yt-playlist jPlayer audioPlayer jPlayer-'.$style.'" data-id="' . $id . '" data-audio="'.yt_image_media($url) . '" data-swf="' . (JUri::root() . 'plugins/system/ytshortcodes/other/jplayer.swf') . '" data-autoplay="' . $autoplay . '" data-volume="' . $volume . '" data-loop="' . $loop . '">
                        <div class="playerScreen">
                            <div id="' . $id . '" class="jPlayer-container"></div>
                        </div>
                        <div class="controls">
                            <div class="controlset left">
                                <a tabindex="1" href="#" class="play smooth"><i class="fa fa-play"></i></a>
                                <a tabindex="1" href="#" class="pause smooth"><i class="fa fa-pause"></i></a>
                            </div>
                            <div class="controlset right-volume">
                                <a tabindex="1" href="#" class="mute smooth"><i class="fa fa-volume-up"></i></a>
                                <a tabindex="1" href="#" class="unmute smooth"><i class="fa fa-volume-off"></i></a>
                            </div>
                            <div class="volumeblock">
                                <div class="volume-control"><div class="volume-value"></div></div>
                            </div>
                            <div class="jpprogress-block">
                                <div class="timer current"></div>
                                <div class="timer duration"></div>
                                <div class="jpprogress">
                                    <div class="seekBar">
                                        <div class="playBar"></div>
                                    </div>
                                </div>
                            </div>
         
                        </div>
                    </div>';
					
	
	$playerlist  = '<div class="audio_player">';
	$playerlist  .= (!empty($yt_title) && $yt_title != null) ?'<h4>'.$yt_title.'</h4>' : '';
	//$playerlist  .= '<audio src="'.$src.'" preload="auto"/></div>';
	$playerlist .= $output;
	$playerlist  .= '<ul class="yt-playlist">'.parse_shortcode(str_replace(array("<br/>", "<br>", "<br />"), " ", $content)) .'</ul>';
	$playerlist	.= $audio_script;
	$playerlist .= $audiojs;
	
	return $playerlist;
}

?>