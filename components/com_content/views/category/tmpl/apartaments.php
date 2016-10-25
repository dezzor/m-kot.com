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

<div class="row">
    <div class="col-md-12"><?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?></div>
</div>

<?$i=0;?>
<?php foreach ($this->lead_items as &$item) : ?>
    <?$this->item = & $item;?>
    <?$image = json_decode($item->images, true);?>
    <?$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)); ?>
    <?if($i==0):?><div class="row"><?endif;?>
    <div class="col-md-4">
        <div class="appartaments">
            <a href="<?=$link?>" class="lightbox"><img src="<?=$image['image_intro']?>" class="img-responsive" alt="" /></a>
            <h2><a href="<?=$link?>"><?=$this->item->title?></a></h2>
        </div>
    </div>
    <?$i++;?>
    <?if($i==3):?></div><?$i=0;?><?endif;?>
<?php endforeach; ?>
<?if($i==2):?><div class="col-md-4"></div></div><?endif;?>
<?if($i==1):?><div class="col-md-4"></div><div class="col-md-4"></div></div><?endif;?>