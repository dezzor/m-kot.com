<?php

defined('_JEXEC') or die;
?>
<section>
    <div class="container">
    <? foreach ($list as $item) : ?>
        <?if($i==0):?><div class="row"><?endif;?>
        <div class="col-md-4 service-cat service-main">
            <span><?php echo htmlspecialchars($item->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?>&nbsp;</span>
            <a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($item->id, $item->language));?>">
                <div style="background: url('<?php echo $item->getParams()->get('image'); ?>')">
                    <h3><?php echo $item->title; ?></h3>
                </div>
            </a>
        </div>
        <?$i++;?>
        <?if($i==3):?></div><?$i=0;?><?endif;?>
    <?php endforeach; ?>
</div>
</section>