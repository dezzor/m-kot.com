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

<?php foreach ($this->lead_items as &$item) : ?>
    <?$this->item = & $item;?>
    <?$fields = fieldattach::getArrayValue($this->item->id,$category = false);?>
    <?$imgs = fieldattach::getGalleryArrayValue(7,$this->item->id,$category = false);?>
    <?$image = json_decode($item->images, true);?>
    <?$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)); ?>
    <div class="row masters">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4">
                <a href="<?=$image['image_intro']?>" class="lightbox"><img src="<?=$image['image_intro']?>" class="img-responsive" alt="" /></a>
                <ul class="master-small-img">
                <?foreach($imgs as $small_img):?>
                    <li>
                        <a href="<?=$small_img['image1']?>" class="lightbox" rel="gallery<?=$this->item->id?>">
                            <img src="<?=$small_img['image1']?>" class="img-responsive" width="80" alt="" />
                        </a>
                    </li>
                <?endforeach;?>
                </ul>
            </div>
            <div class="col-md-4 masters-short-descr">
                <h2><a href="<?=$link?>"><?=$this->item->title?></a></h2>
                <?php echo $this->item->introtext; ?>
            </div>
            <div class="col-md-4 masters-full-descr"><?php echo $this->item->fulltext; ?></div>
        </div>




    </div>

    </div>

<?php endforeach; ?>
