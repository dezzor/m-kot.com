<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JLoader::register('fieldattach', 'components/com_fieldsattach/helpers/fieldattach.php');

JHtml::_('behavior.caption');
?>

<?php if ($this->params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
    </div>
<?php endif; ?>

<?$i=0;?>
<?php foreach ($this->lead_items as &$item) : ?>
    <?$this->item = & $item;?>
    <?$fields = fieldattach::getArrayValue($this->item->id,$category = false);?>
    <?$image = json_decode($item->images, true);?>
    <?if($i==0):?><div class="row"><?endif;?>
    <div class="col-md-4 service">
        <a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
          <img src="<?=$image['image_intro']?>" class="img-responsive" alt="" />
        </a>
        <?php echo $this->item->event->beforeDisplayContent; ?>
        <h2>
          <a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
            <?=$this->item->title?>
          </a>
        </h2>
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
    <?$i++;?>
    <?if($i==3):?></div><?$i=0;?><?endif;?>

<?php endforeach; ?>
