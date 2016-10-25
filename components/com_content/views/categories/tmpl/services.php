<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
$lang  = JFactory::getLanguage();
?>

<?echo JLayoutHelper::render('joomla.content.categories_default', $this);?>

<?$i=0;?>
<?foreach($this->items[$this->parent->id] as $id => $item) : ?>
    <?if($i==0):?><div class="row"><?endif;?>
    <div class="col-md-4 service-cat">

        <span><?php echo htmlspecialchars($item->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?></span>
        <a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($item->id, $item->language));?>">
            <div style="background: url('<?php echo $item->getParams()->get('image'); ?>')">
                <h3><?php echo $this->escape($item->title); ?></h3>
            </div>
        </a>
    </div>
    <?$i++;?>
    <?if($i==3):?></div><?$i=0;?><?endif;?>
<?php endforeach; ?>