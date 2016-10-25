<section>
    <div class="container">
    <?$i=0;?>
    <?php foreach ($list as $item):?>
        <?if($i==0):?><div class="row"><?endif;?>
        <?$image_arr = json_decode($item->images);?>
        <div class="col-md-4">


            <div class="appartaments">
                <a href="<?php echo $item->link; ?>"><img src="<?php echo $image_arr->image_intro; ?>" class="img-responsive" alt=""></a>
                <h3><a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a></h3>
            </div>

        </div>
        <?$i++;?>
        <?if($i==3):?></div><?$i=0;?><?endif;?>
    <?php endforeach; ?>
</div>
</section>