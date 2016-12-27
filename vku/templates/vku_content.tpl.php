<div style="width:470px; background: White;">
<div style="padding: 10px 20px" class="inner">
<div class="clearfix">
  <div class="pull-right"><a href="#" class="close" onclick="jQuery('#vkuload').slideToggle(); return false;">×</a></div>
  <h4 style="margin-top:0; ">Ihre aktuelle Verkaufsunterlage<br /><?php print $vku -> getTitle(); ?></h4>
</div>

<!--INFO DIV for messages -->
<div class="well" style="display: none;"></div>
<?php
   $hinweise = array();

  
   $max_nids = variable_get("lk_vku_add_max", 3);
   
   if($max_nids == count($nodes)){
      $hinweise[] = 'Maximal ' . $max_nids . ' Kampagnen pro Verkaufsunterlage möglich.';   
   }

   if($vku_count > 1){
     $hinweise[] = 'Sie haben mehrere ('. $vku_count . ') aktive Verkaufsunterlagen. Diese angezeigte ist die Aktuellste. <a href="' . url($vku -> userUrl()) . '">Übersicht aller Verkaufsunterlagen.</a>';   
   }

   if($hinweise) :
    ?>
      <ul class="alert alert-success" style="list-style: none;"><li><?php print implode('</li><li>', $hinweise); ?></li></ul>
      <hr />
    <?php endif; 
    
    if(!$nodes){
      ?>
      <p class="text-center">- Keine Kampagnen -</p>
      <?php  
    }
    
    while(list($key, $node) = each($nodes)){
     ?>
     <div class="clearfix node-in-cart node_<?php print $node -> nid; ?>" style="padding-bottom: 10px">
        
        <div class="pull-left" style="width: 100px;">
          <a href="<?php print url('node/'  . $node -> nid); ?>" style="display: block;">
            <?php
                $bild = $node->field_kamp_teaserbild['und'][0]['uri'];
                $img = image_style_url('kampagne_klein', $bild);
                
                  
            ?> 
            <img src="<?php print $img; ?>" />
            </a>
        </div>
        <div class="pull-left" style="width: 330px;">
             <p class="pull-right text-center">
              <span class="prodid"><?php print _lk_get_kampa_sid($node); ?></span>
              <br /><br />
               <!--<a href="<?php print url("vku/node/delete/" . $node -> nid); ?>" class="delete-node-from-cart" onclick="deleteFromCart(this); return false;" nid="<?php print $node -> nid; ?>"><span class="glyphicon glyphicon-trash"></span></a>-->
             </p>
           
            <a href="<?php print url('node/'  . $node -> nid); ?>" style="display: block;"> 
              <p><strong><?php print $node -> title; ?></strong></p>        
              <p><?php print $node->field_kamp_untertitel['und'][0]['value']; ?></p>
            </a>
            
        </div>
         
     </div> 
     <?php 
  }
?>

<div class="text-right"><a class="btn btn-primary" href="<?php print url('vku/'. $vku -> getId()); ?>">PDF generieren <span class="glyphicon glyphicon-chevron-right"></span></a></div>

</div>
</div>