<?php
/**
 * @package Sj Video Box
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;
JHtml::stylesheet('modules/mod_sj_videobox/assets/css/mod_sj_videobox.css');
JHtml::script('modules/mod_sj_videobox/assets/js/bootstrap-carousel.js');
if( !defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1" ){
	JHtml::script('modules/mod_sj_videobox/assets/js/jquery-1.8.2.min.js');
	JHtml::script('modules/mod_sj_videobox/assets/js/jquery-noconflict.js');
	define('SMART_JQUERY', 1);
}

$options=$params->toObject();
ImageHelper::setDefault($params);
$image_config=array(
		'type'			=> null,
		'width' 		=> $params->get('imgcfg_width'),
		'height' 		=> $params->get('imgcfg_height'),
		'quality' 		=> 90,
		'function' 		=> 'resize',
		'function_mode' => substr('resize_stretch', 7),
		'transparency'  => false,
		'background' 	=> '#ffffff'
);
$links = array();
$titles = array();
$imagest= array();
$descriptionst= array();
$suffix = rand().time();
$tag_id = 'sj_videobox'.$suffix;

if($params->get('video')!="") 			$links = preg_split ("/\n/", $params->get('video'));
if($params->get('titles')!="") 			$titles = preg_split ("/\n/", $params->get('titles'));
if($params->get('images')!="") 			$imagest = preg_split ("/\n/", $params->get('images'));
if($params->get('descriptions')!="") 	$descriptionst = preg_split ("/\n/", $params->get('descriptions'));

$items =array();
while(count($links)){
	$item = array();
	$item['url']= array_shift($links);
	if( count($titles) ){
		$item['title']= array_shift($titles);
	}else{
		$item['title']= "";
	}
	if( count($imagest) ){
		$item['image']= array_shift($imagest);
	}else{
		$item['image']= "";
	}
	if( count($descriptionst) ){
		$item['description']= array_shift($descriptionst);
	}else{
		$item['description']= "";
	}
	array_push($items, $item);
}
if( $params->get('autoplay') == 1 ){
	$auto = "?badge=0&autoplay=1";
}else {
	$auto = "";
}
$item_page = (int)$params->get('num_show', 3);
$count_item = count($items);
?>
<script type="text/javascript">
window.addEvent("domready", function(){
	if (typeof jQuery != "undefined" && typeof MooTools != "undefined" ) {
		Element.implement({
			slide: function(how, mode){
				return this;
			}
		});
	}
});
</script>
<?php if( !empty($items) ){?>
<div id="<?php echo $tag_id;?>" class="sj-videobox">
<?php if(!empty($options->pretext)){?>
	<div class="intro_text"><?php echo $options->pretext; ?></div>
<?php }?>
	<div class="sj-video-current">
	<?php foreach ($items as $key=>$value){$url = trim($value['url']);
	if( $key == 0 ){?>
		<iframe id="sj_videobox_show<?php echo $module->id;?>" width="100%" height="400px" src="<?php echo $url.$auto;?>" frameborder="0" allowfullscreen></iframe>
	<?php }}?>
	</div>
	<div id="myCarousel<?php echo $suffix;?>" class="sj-video-list  <?php if( $params->get('theme') == 1 ){echo " style2 carousel slide";}?>">
		<?php if( $params->get('show-description') == 1 || $params->get('show-title') == 1 || $params->get('show-image') == 1 ){?>
		<div class="sj-video-list-inner carousel-inner">
		<?php $i=0; foreach ($items as $value){ $i++; $url = trim($value['url']);?>
			<?php if( $i%$item_page == 1 && $params->get('theme') == 1 || $item_page == 1 && $params->get('theme') == 1 ){?>
			<div class="item <?php if( $i == 1 ){echo ' active ';}?> <?php echo "column".$item_page;?>">
			<?php }?>
				<div data-target="#sj_videobox_show<?php echo $module->id;?>" data-url="<?php echo $url.$auto; ?>" class="sj-video-list-item <?php if( $i%2 == 0 ){}if( $i%$item_page == 1 && $params->get('theme') == 1 ){echo ' first';}if( $i == 1 ){echo ' selected';}?>">
					<?php if( $params->get('show-image') == 1 ){?>
					<div class="sj-video-image">
						<?php if( trim($value['image']) == "" ){
							$img = array();
							$img['src'] = "modules/mod_sj_videobox/assets/img/nophoto.jpg";
							$img['alt'] ="";
							$img['title'] ="";
						}else{
							$img = array();
							$img['src'] = trim($value['image']);
							$img['alt'] ="";
							$img['title'] ="";
						}
						echo SjVideobox::imageTag($img, $image_config);?>
						<div class="bg-hover"></div>
					</div>
					<?php }?>
					<div class="sj-video-title">
						<?php if( $params->get('show-title') == 1 ){ echo $value['title'] ;}?>
					</div>
					<div class="sj-video-des">
						<?php if( $params->get('show-description') == 1 ){ echo $value['description'] ;}?>
					</div>
				</div>
			<?php if ( $i%$item_page == 0 && $params->get('theme') == 1 || $item_page == 1 && $params->get('theme') == 1 || $i == $count_item && $params->get('theme') == 1 ){?>
			</div>
			<?php }?>
		<?php }?>
		</div>
		<?php }?>
		<?php if( $params->get('theme') == 1 && $count_item >$item_page){
		if ( $params->get('show-description') == 1 || $params->get('show-title') == 1 || $params->get('show-image') == 1 ){?>
		<div class="sj-video-control">
		    <a class="carousel-control left" href="#myCarousel<?php echo $suffix;?>" data-slide="prev">&lsaquo;</a>
		    <a class="carousel-control right" href="#myCarousel<?php echo $suffix;?>" data-slide="next">&rsaquo;</a>
	    </div>
		<?php }}?>
	</div>
	<?php if(!empty($options->posttext)){?>
		<div class="footer_text"><?php echo $options->posttext; ?></div>
	<?php }?>
</div>

<?php $auto = $options->play;
	if($auto == 0){
		$interval = 0;
	}else {
		$interval = 5000;
	}
?>
<script type="text/javascript">
//<![CDATA[
    jQuery(document).ready(function($){
		$(document).on('click', '#<?php echo $tag_id;?> .sj-video-list [data-url]', function(e){
			e.preventDefault();
			var $this = $(this),data = $this.data();
			//console.log($this);
			$('#<?php echo $tag_id;?> .sj-video-list-item').removeClass('selected');
			$this.addClass('selected');
			if($(data.target).length){
				$(data.target).attr('src', data.url);
			};
		});
	    $('#myCarousel<?php echo $suffix;?>').carousel({
		    interval: <?php echo $interval;?>,
		    pause:'hover'
	    })

    });
//]]>
</script>
<?php }else{ echo JText::_('Has no content to show!');}?>

