<section>
    <div class="container">
    <?$i=0;?>
    <?php foreach ($list as $item):?>
        <?if($i==0):?><div class="row"><?endif;?>
        <div class="col-md-4 service-cat">
            <?$image_arr = json_decode($item->images);?>
            <a href="<?php echo $item->link; ?>">
                <div style="background: url('<?php echo $image_arr->image_intro; ?>')">
                    <h3><?php echo $item->title; ?></h3>
                </div>
            </a>
        </div>
        <?$i++;?>
        <?if($i==3):?></div><?$i=0;?><?endif;?>
    <?php endforeach; ?>
</div>
</section>