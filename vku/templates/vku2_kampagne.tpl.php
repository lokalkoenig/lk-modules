<?php
$node = node_load($nid);
$bild = $node->field_kamp_teaserbild['und'][0]['uri'];
$img = image_style_url('kampagne_klein', $bild);
?>


<div class="item dropable dropable-kampagne dropable-general" data-title="Kampagne: <?php print $node -> title; ?>" id="kampagne-<?php print $node -> nid; ?>">
  <div class="row clearfix">
      <div class="col-xs-3"><img src="<?php print $img; ?>" style="width: 50px; height: auto;"/></div>
      <div class="col-xs-9">
       <span class="prodid pull-right" style="margin-left: 15px;"><?php print $node -> sid; ?></span>
       <strong><?php print $node -> title; ?></strong><br />
       <small><?php print $node->field_kamp_untertitel['und'][0]['value']; ?></small>
      </div>
   </div>
 </div>