<?php
 lokalkoenig_node_prepare_view($node);
 include(__DIR__ . "/include_varianten.php"); 
?>

<div class="clearfix node_<?php print $node -> nid; ?>">
  <div class="pull-left" style="width: 227px; margin-right: 20px;">
  <div class="thumbnail-noborder">
   <?php print render($content["field_kamp_teaserbild"]); ?>
  </div>
  </div>
  <div class="pull-left" style="width: 486px;">
    <a href="<?php print url("node/" . $nid); ?>" style="display: block;">
      <h3 style="margin-top: 0;"><?php print $title; ?></h3>
      <h4 style="font-weight: normal; margin-top: 0;">
          <?php print render($content["field_kamp_untertitel"]); ?>
      </h4>
   </a> 
   
    
    <ul class="list-inline">
      <li><?php print $overview_print; ?></li>
      <li><?php print $overview_online; ?></li>
      <li><span class="prodid"><?php print $node -> kid; ?></span></li>
      
      <?php
        foreach($node -> basic_links as $link):
            $options = array('html' => true, "attributes" => (array)$link["attributes"]);
            print '<li class="pull-right">'. l($link["title"], $link["href"], $options) .'</li>';
        endforeach;
     ?> 
    </ul>
    
    <div class="teaser-text" style="min-height: 100px;">
        <?php print render($content["field_kamp_teasertext"]); ?>
    </div>
    
   <?php
      // If there is a Notification about something  
      if($node -> sperre_hinweis):
          ?>
            <div class="well well-white"><?php print $node -> sperre_hinweis; ?></div>
          <?php
      endif;
   ?>
    
    <!--LINKS -->
    <ul class="list-inline list-kampa-options">
      <li class="pull-right list_link">
          <span class="badge"><a href="<?php print url('node/' . $nid); ?>">Detailansicht</a></span>
      </li>
      <li class="list_merkliste <?php if($node -> merkliste) { ?> hover<?php } ?>">
        <span class="badge"><span class="glyphicon glyphicon-ok"></span></span> 
        
        <?php 
            print l($node -> merkliste_link["title"], 
                $node -> merkliste_link["href"], 
                    array('attributes' => $node -> merkliste_link["attributes"])
              ); 
        ?>
      </li>
      <li class="list_vku <?php if($node -> vku_can == false): print ' list_vku_disabled'; endif;?> <?php if($node -> vku_active) print ' hover'; ?>"><span class="badge"><span class="glyphicon glyphicon-euro"></span></span>
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

<hr />
