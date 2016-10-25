<?php
defined('_JEXEC') or die;

$i=0;
$x=0;
?>
<div id="carousel-example-generic" class="carousel slide carousel-fade" data-ride="carousel">

    <ol class="carousel-indicators">
        <?php foreach ($list as $item):?>
        <li data-target="#carousel-example-generic" data-slide-to="<?=$x?>" class="<? echo $x==0 ? 'active':''; $x++;?>"></li>
        <?endforeach;?>
    </ol>

    <!-- Содержимое слайдов -->
    <div class="carousel-inner">
    <?php foreach ($list as $item):?>
        <div class="item <? echo $i==0 ? 'active':''; $i++;?>">
            <?$image_arr = json_decode($item->images);?>
            <?$urls_arr = json_decode($item->urls);?>
            <a href="<?=$urls_arr->urla?>">
                <img src="/<?=$image_arr->image_intro;?>">
            </a>
        </div>
    <?endforeach;?>
    </div>

    <!-- Controls -->
    <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
    </a>
    <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
    </a>


</div>
