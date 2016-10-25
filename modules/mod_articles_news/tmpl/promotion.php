<section class="promotions">
    <div class="container">
    <?$i=0;?>
    <?php foreach ($list as $item):?>
        <?if($i==0):?><div class="row"><?endif;?>
        <?$image_arr = json_decode($item->images);?>
        <div class="col-md-4" >
            <div class="promotion" style="background: url(<?=$image_arr->image_intro?>) no-repeat top center;background-size:100%">
                <div class="promotion_desc">
                    <?=$item->introtext?>
                </div>
            </div>
        </div>
        <?$i++;?>
        <?if($i==3):?></div><?$i=0;?><?endif;?>
    <?php endforeach; ?>
    </div>
</section>