<?php $sid = $item["cid"] . "-" . $item["id"]; ?>

<?php
    if(!isset($item["active"])) $item["active"] = 1;
    
    $class = $item["class"];
    
    if($item["collapsed"]) {
       $class[] = 'entry-collapsed';
    }
    
    if(!$item["active"]){
       $class[] = 'state-deactivate';
    }
   
    
    
?>


<div class="entry <?php print implode(" " , $class); ?>" data-orig="<?php print $item["orig-id"]; ?>" data-pages="<?php print $item["pages"]; ?>" data-sid="<?php print $sid; ?>" data-cid="<?php print $item["cid"]; ?>" data-id="<?php print $item["id"]; ?>"> 
<div class="clearfix entry-item">
            <?php if($item["collapsed"]):
                    ?>
                    <div class="caret-toggle pull-right small">Bearbeiten <span class="caret"></span></div>
                    <?php 
                endif;
                  ?>      
    
            <?php if($item["container"] == false): ?>
                <?php if($item["single_toggle"] == false): ?>
                <div class="btn-group pull-right btn-actions">
                    <ul class="list-inline" role="menu" data-sid="<?php print $sid; ?>">
                        <?php if($item["delete"]): ?>
                        <li class="action-delete"><a href="#" class="btn btn-default btn-sm" data-action="delete">Entfernen</a></li>
                        <?php else: ?>
                        <li class="action-deactivate"><a href="#" class="btn btn-default btn-sm" data-action="deactivate">Deaktivieren</a></li>
                        <li class="action-activate"><a href="#" class="btn btn-default btn-sm" data-action="activate">Aktivieren</a></li>
                       <?php endif; ?>
                            
                        <?php if($item["preview"]): ?>    
                            <li class="action-preview" data-sid="<?php print $sid; ?>" data-preview-title="<?php print strip_tags($item["title"]); ?>" data-preview-url="<?php print url("vku/". $vku -> getId() ."/preview/" . $item["id"]); ?>"><a href="#" class="btn btn-default btn-sm" data-action="preview">Vorschau</a></li>
                        <?php endif; ?>

                        <?php if($item['edit-handler']): ?>
                         <li class="action-edit"><?= $item['edit-handler']; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
    
                <?php else: ?>
                     <div class="pull-right btn-actions btn-actions-toggle">
                         <ul role="menu" data-sid="<?php print $sid; ?>">
                            <li class="action-deactivate"><a href="#" class="btn btn-default btn-sm" data-action="deactivate">Deaktivieren</a></li>
                            <li class="action-activate"><a href="#" class="btn btn-default btn-sm" data-action="activate">Aktivieren</a></li>
                         </ul>
                     </div>    
    
                <?php endif; ?>
            
            <?php endif; ?>
            
    <div class="part-title">
       
      
        <?php print $item["title"]; ?>
        
            <?php if($item["has_children"]) :?>
            <span class="child-count small">(<?php $count = count($item["children"]); if($count == 1) print $count . " Seite"; else print $count . " Seiten";  ?>)</span>
        <?php endif; ?>
            
            <?php if(isset($item["additional_title"])): ?>
                <?php print $item["additional_title"]; ?>
            <?php endif; ?>
      
        <span class="deactivated">(Deaktiviert)</span>            
    </div>
     
     <?php if($item["delete"]): ?>
            <div class="restore text-right">
                <!--
                <button class="btn btn-delete btn-danger pull-right">Endgültig löschen</button>--> 
                <a class="btn-restore" href="#">Entfernen rückgängig machen</a> 
            </div>
        <?php endif; ?> 
    </div>
    
    
        <?php if($item["has_children"]) :?>
            <div class="children">
                
                <?php 
                   if($item["drop-zone"]){
                    ?>        
                        <div class="<?php print $item["drop-zone"]; ?> drop-zone-top entry ui-sortable-placeholder"></div>       
                     <?php       
                    }
                ?>
                
               <?php foreach($item["children"] as $child): ?>
                    <?php 
                        $child["cid"] = $item["cid"]; 
                        print theme('vku2_item', array('item' => $child, 'vku' => $vku));
                    ?>
                <?php endforeach; ?>
                
                        
                <?php if($item["kampagne"] == false): ?>        
                    <div class="entry entry-dummy">
                        <div class="part-title"><em>Keine Dokumente</em></div>
                    </div>
                <?php endif; ?>        
            </div>     
        <?php endif; ?>
</div>
    