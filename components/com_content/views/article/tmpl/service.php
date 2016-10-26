<!--?php
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JLoader::register('fieldattach', 'components/com_fieldsattach/helpers/fieldattach.php');
JHtml::_('behavior.caption');
?-->
<?php
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JLoader::register('fieldattach', 'components/com_fieldsattach/helpers/fieldattach.php');
// Create shortcuts to some parameters.
$params  = $this->item->params;
$images  = json_decode($this->item->images);
$urls    = json_decode($this->item->urls);
$canEdit = $params->get('access-edit');
$user    = JFactory::getUser();
$info    = $params->get('info_block_position', 0);
JHtml::_('behavior.caption');

?>
<?$fields = fieldattach::getArrayValue($this->item->id,$category = false);?>
<?$imgs = fieldattach::getGalleryArrayValue(8,$this->item->id,$category = false);?>
<?$image = json_decode($this->item->images, true);?>
<?$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)); ?>
<?$i=0;?>
<div class="item-page<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Article">
  <meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? JFactory::getConfig()->get('language') : $this->item->language; ?>" />
  <h1><?php echo $this->escape($this->item->title); ?></h1>
  <?echo $this->item->pagination;?>
  <div class="row">
    <div class="col-md-6"><a href="<?=$images->image_intro?>" class="lightbox"><img src="<?=$images->image_intro?>" class="img-responsive" alt="" /></a></div>
    <div class="col-md-6">
      <?php echo $this->item->event->beforeDisplayContent; ?>
      <?foreach($fields as $field):?>
        <?if($field->fieldsid == 3):?>
        <p><i><?=$field->title?></i>: <span class="strikethrough"><?=$field->value?></span></p>
        <?else:?>
        <p><i><?=$field->title?></i>: <span class="white"><?=$field->value?></span></p>
        <?endif;?>
      <?endforeach;?>
      <h3><i><?php echo JText::_('SERVICE_CONTENTS'); ?>:</i></h3>
      <?php echo $this->item->introtext; ?>
    </div>
  </div>
  <?php echo $this->item->event->afterDisplayContent; ?>
</div>