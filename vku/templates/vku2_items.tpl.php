<div class="vku-items">
    <div class="drop-zone drop-zone-top entry ui-sortable-placeholder"></div>    
    <?php

    foreach($items as $item):
    ?>
      <?php print theme('vku2_item', array("item" => $item, "vku" => $vku)); ?>     
    <?php
    endforeach;
    ?>
</div>

