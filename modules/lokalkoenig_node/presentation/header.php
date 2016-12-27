<?php
  
if(!isset($node -> online)){
    $node -> online = false;
}

lokalkoenig_node_prepare_view($node, 'full');

?>
<div class="clearfix width">
<div class="pull-left" style="width: 282px; margin-right: 40px;">
  <div class="thumbnail-noborder">
   <?php print render($content["field_kamp_teaserbild"]); ?>
  </div>
</div>
    
<div class="pull-left" style="width: 678px;">
    <h2 style="margin-top: 0; font-weight: bold;"><?php print $title; ?></h2>
    <h3 style="font-weight: normal; margin-top: 0; margin-bottom: 20px;"><?php print render($content["field_kamp_untertitel"]); ?></h3>
    
    <ul class="list-inline status-icons">
     <li><span class="prodid prodid_big"><?php print _lk_get_kampa_sid($node); ?></span></li> 
      <li><img src="/sites/all/themes/bootstrap_lk/design/icon-printanzeige.png" alt="Printanzeige" /> Printanzeige</li>
      <li><img src="/sites/all/themes/bootstrap_lk/design/icon-webanzeige.png" alt="Webanzeige" /> Onlineanzeige</li>
 
      <?php
        foreach($node -> basic_links as $link):
            $options = array('html' => true, "attributes" => (array)$link["attributes"]);
            print '<li>'. l($link["title"], $link["href"], $options) .'</li>';
        endforeach;
     ?> 
    </ul>
    
    <div class="kampagnen-teaser" style="height: 100px;">
      <?php print render($content["field_kamp_teasertext"]); ?>
    </div>
   
     <ul class="list-inline list-kampa-options list-kampa-options-big">
      <li class="list_merkliste <?php if($node -> merkliste) { ?> hover<?php } ?>">
        <span class="badge"><span class="glyphicon glyphicon-ok"></span></span> 
        
        <?php 
            print l($node -> merkliste_link["title"], 
                $node -> merkliste_link["href"], 
                    array('attributes' => $node -> merkliste_link["attributes"])
              ); 
        ?>
      </li>
      <li class="list_vku <?php if($node -> vku_can == false): print ' list_vku_disabled'; endif;?><?php if($node -> vku_active) print ' hover'; ?>"><span class="badge"><span class="glyphicon glyphicon-euro"></span></span>
          <?php 
           print l($node -> vku_link["title"], 
                $node -> vku_link["href"], 
                    array('attributes' => $node -> vku_link["attributes"])
              ); 
          
          ?>
      </li>
    </ul>  
  </div>
</div>

<?php 
    
 // If there is a Notification about something  
if($node -> sperre_hinweis):
?>
<div class="width">
    <div class="well well-white">
         <?php print $node -> sperre_hinweis; ?>
    </div>
</div>   
<?php
endif;
?>