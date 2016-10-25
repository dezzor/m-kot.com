<script>
    jQuery(document).ready(function() {
        jQuery('.multiple-items').slick({
            dots: true,
            infinite: true,
            speed: 500,
            slidesToShow: 2,
            slidesToScroll: 1,
            adaptiveHeight: true,

        });
    });
</script>
<section>
    <div class="container">
        <div class="multiple-items">
        <?php foreach ($list as $item):?>
            <?$image_arr = json_decode($item->images);?>
            <div>
                <a href="<?php echo $item->link; ?>">
                    <img src="<?php echo $image_arr->image_intro; ?>" style="margin:0 auto;" alt=""/>
                </a>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>