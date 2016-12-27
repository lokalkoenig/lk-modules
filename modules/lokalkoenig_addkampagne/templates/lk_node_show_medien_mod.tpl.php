 <?php

  $canadmin = true;
  
  if($node -> status == 1) $canadmin = false;
 
if(count($medien) == 0) { ?>
          <div class="well">Laden Sie Medien hoch. <div class="pull-right"><a class="btn btn-success btn-sm" href="<?php print url("node/" . arg(1) . "/addmedia")?>">Medium hochladen</a></div></div>
          
          <?php
         return ;  
}
else {
   if($info["print"] == 0 OR $info["online"] == 0){
    ?>
      <div class="well">Laden Sie weitere Medien hoch. <div class="pull-right"><a class="btn btn-success btn-sm" href="<?php print url("node/" . arg(1) . "/addmedia")?>">Medium hochladen</a></div></div>
     
    <?php
  }


}
        
if(!$canadmin){
?>  
<div class="alert alert-success"><strong>Diese Kampagne ist Online</strong>, wobei keine neuen Medien hinzugefügt und keine bestehenden gelöscht werden können. Um diese Optionen zu haben, müssen Sie die Kampagne in der Moderation wieder Offline stellen.</div>
<?php
}      
        
       
   ?>
<div class="well well-white">
      
<table class="views-table cols-4 table table-striped table-hover">
         <thead>
      <tr>
                  <th class="views-field views-field-field-medium-bild views-align-center">
            Bild          </th>
                  <th class="views-field views-field-title">
            Titel          </th>
                  <th class="views-field views-field-edit-link">
            Bearbeiten          </th>
                  
              </tr>
    </thead>
    <tbody>
          
    <?php
        foreach($medien as $med){
          $type = _lk_get_medientyp_print_or_online($med->field_medium_typ['und'][0]['tid']);
          
          // let it be
          if(isset($med -> field_medium_main_reference['und'][0]['target_id'])) continue;
        
          ?>
           <tr class="odd views-row-first">
                  <td class="views-field views-field-field-medium-bild views-align-center"><?php
                    $bild = $med -> field_medium_bild['und'][0]['uri'];
                    print $image = theme('image_style',array(
                            'style_name' => 'medium',
                            'path' => $bild));      
                  
                  ?></td>
                  <td class="views-field views-field-title"><strong><?php print $med -> title; ?></strong><br />
                  <small><?php print _lk_get_medientyp_with_type($med->field_medium_typ['und'][0]['tid']); ?></small>
                    
                    <?php 
                        
                      // Main-Medias  
                      if($info["select"] AND !isset($med -> field_medium_main_reference['und'][0]['target_id'])){
                          // get all possible Print Formats
                          $selects = array();
                          while(list($key, $val) = each($info["select"])){
                              $type_select = _lk_get_medientyp_print_or_online($key);
                              if($type_select == $type){
                                 $selects[$key] = $val;
                              }
                          }
                          reset($info["select"]);
                          if($selects AND $canadmin):
                              $links = array(); 
                              
                              while(list($key, $val) = each($selects)):
                                   $links[] = '<a href="'. url("node/" . arg(1) . "/addmedia", array("query" => array("parent" => $med -> id, "medium" => $key))) .'">'. $val .'</a>';
                               endwhile;
                       
                           ?>
                             <br /><br />
                             <?php print lk_generate_dropdown("Weitere ". ucfirst($type) ."-Medien hochladen", "cloud-upload", $links); ?>
                             <?php
                          endif;
                      }
                    
                    ?>
                    
                    
                  </td>
                  <td class="views-field views-field-edit-link">
                      <br />
                      <?php
                        $link = '<a class="btn btn-primary btn-sm" href="'. url('node/' . arg(1) . '/media/' . $med -> id . '/edit') .'"><span class="glyphicon glyphicon-pencil"></span> Editieren</a>';
                        print $link;    
                        
                        if($canadmin):
                            $m = new LKMedium($med -> id);
                            $test = LKMedium_get_possibile_varianten($m);

                            $links = array();
                            $possibile = $info["additional"][$type];

                            while(list($key, $val) = each($test)):
                                if($val["medium"] == 0){
                                      $links[] = '<a href="'. url("node/" . arg(1) . "/addmedia", array("query" => array("parent" => $med -> id, "variante" => 1, "medium" => $key))) .'">'. $val["title"] .'</a>';
                                }
                            endwhile;;

                            if($links):
                                print '<br /><br />' .lk_generate_dropdown("Formatvarianten", "plus", $links);
                            endif;
                        endif;
                      ?>
                  </td>
                  
              </tr>
              
              
              
              
              
              
          <?php
          
             foreach($medien as $med2){
               if(isset($med2 -> field_medium_main_reference['und'][0]['target_id']) AND $med2 -> field_medium_main_reference['und'][0]['target_id'] == $med -> id) {
                ?>
                   <tr class="odd views-row-first">
                       <td class="views-field views-field-field-medium-bild views-align-center">
                           <?php if($med2 -> variante): ?>
                           <em>Formatvariante</em>
                          <?php endif; ?>
                       </td>
                      <td class="views-field views-field-title"><strong><?php print $med2 -> title; ?></strong>
                       <br />
                      <small><?php print _lk_get_medientyp_with_type($med2->field_medium_typ['und'][0]['tid']); ?></small></td>
                      <td>
                          <a href="<?php print url('node/' . arg(1) . '/media/' . $med2 -> id . '/edit'); ?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-pencil"></span> Editieren</a>
                            <?php if($canadmin) { ?>
                              <a href="<?php print url('node/' . arg(1) . '/media/' . $med2 -> id . '/delete'); ?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span> Löschen</a>
                            <?php } ?>
                      </td>
                     
                   
                   
                   </tr>
               
                <?php
               }
             
             
             }
          
          
        }
     ?>     
          
    
    
    
  </tbody>
</table>
    </div>
     