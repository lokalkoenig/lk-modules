<?php
  if(!isset($node -> online)){
     $node -> online = false;
  }
  lokalkoenig_node_prepare_view($node, 'grid');
  include(__DIR__ . "/include_varianten.php"); 
  
?>

 <div class="thumbnail-noborder tgrid pull-left" 
        style="  margin-bottom: 16px;  margin-right: 16px;">
 
    <div class="contenthover2" style="display: none;">
        <div style="padding: 10px;">
        <div style="height: 90px;">
          <a href="<?php print url("node/" . $nid); ?>" style="display: block;">
             <h2 style="margin-top: 0;" class="h4"><?php print $node -> title; ?></h2>
             <p class="clearfix"><?php print $node->field_kamp_untertitel['und'][0]['value']; ?></p>
          </a>
        </div>
   
  <ul class="list-kampa-options">
     
       <li class="list_merkliste <?php if($node -> merkliste) { ?> hover<?php } ?>">
        <span class="badge"><span class="glyphicon glyphicon-ok"></span></span> 
        
        <?php 
            print l($node -> merkliste_link["title"], 
                $node -> merkliste_link["href"], 
                    array('attributes' => $node -> merkliste_link["attributes"])
              ); 
        ?>
      </li>
      <li data-vku-nid="<?php print $nid; ?>" class="list_vku <?php if($node -> vku_can == false): print ' list_vku_disabled'; endif;?> <?php if($node -> vku_active) print ' hover'; ?>"><span class="badge"><span class="glyphicon glyphicon-euro"></span></span>
          <?php 
           print l($node -> vku_link["title"], 
                $node -> vku_link["href"], 
                    array('attributes' => $node -> vku_link["attributes"])
              ); 
          ?>
      </li>
      
      
       <li class="list_link">
          <?php
           if($node -> online AND !lk_is_moderator()): 
           ?>
            <div class="pull-right"><span class="prodid" data-toggle="tooltip" data-placement="top" title="Versenden Sie diese Kampagne an Ihre Mitarbeiter"><a href="#" class="recomendnode" nid="<?php print $nid; ?>" style="color: White;"><span class="glyphicon glyphicon-envelope"></span></a></span></div>
           <?php
           endif;
          ?>
       
          <span class="badge"><a href="<?php print url('node/' . $nid); ?>">Detailansicht</a></span>
      </li>
      
    </ul> 
    
   
    
  </div>
    
</div>
 
   
   <div class="showindicator" style=""> 
   <span class="glyphicon glyphicon-chevron-up"></span>
   </div>
   
  <a href="<?php print url("node/" . $nid); ?>" style="display: block;"> 
   <?php
      $bild = @$node->field_kamp_teaserbild['und'][0]['uri'];
      $img = image_style_url('kampagnen_uebersicht', $bild);
      
      ?>
     
      <?php
      print '<img src="'. $img . '" class="image-grid-hover" width="225" height="209" title="'.  $node -> title .'" />';  
   ?>
    <div class="grid-under-image">
         <ul class="list-inline">
            <li><?php print $overview_print; ?></li>
            <li><?php print $overview_online; ?></li>
        </ul>
     </div> 
   </a>
</div>