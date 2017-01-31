<?php
 // $sort medias
 
 // Serie, Medien umsortieren
if(count($node -> medien) > 2){
   $medien = array();
   $medien2 = array();
    
    reset($node -> medien);
    
   // Print  
   foreach($node -> medien as $media){
       $test = _lk_get_medientyp_print_or_online($media->field_medium_typ['und'][0]['tid']);
       
       if($test == 'print'){
         $medien[] = $media; 
       }
       else {
           $medien2[] = $media; 
       }
   }
   
   foreach($medien2 as $media){
      $medien[] = $media;
   }
    
  $node -> medien = $medien;     
}

?>


<div class="modal modal-lg" id="presentation-big-images" style="overflow:hidden">
    <div class="modal-dialog modal-lg" style="width:90%; max-width: 1024px;">
    <div class="modal-content">
     <div class="row clearfix preview_container clearfix" style="border-radius: 4px; background: White">
      
      
            <div class="stip col-xs-4 strip-navigation" style="padding: 0;">
            
            <div class="modal-header" style="background: White;">
          
        <h3 class="modal-title">Kampagnenmedien: <br /><?php print $title; ?></h3>
      </div>
            
        <div>
          
        <?php
          
           foreach($node -> medien as $delta => $medium){
         
            $resultset = array();
            $res = _lk_get_medientyp_print_or_online($medium->field_medium_typ['und'][0]['tid']);
            $node -> medien[$delta] -> mtype = $res;
            
             $varianten_titles = array();    
             foreach($medium ->field_medium_varianten['und'] as $vorschau){
                $varianten_titles[] = $vorschau["title"];
             }   
            
             $node -> medien[$delta] -> varianten_titles = $varianten_titles;
          
         
        ?>
           
        <a href="#pres_medium_<?php print $medium -> id; ?>" class="preview_link" onclick="activetePreview(<?php print $medium -> id; ?>); return false;">
          <div class="preview_item alert" id="pres_medium_item_<?php print $medium -> id; ?>">
          <p><span class="glyphicon glyphicon-chevron-right"></span> <strong><?php print $medium -> title; ?></strong></p>
          <p class="more"><?php  print _lk_get_medientyp_with_type($medium->field_medium_typ['und'][0]['tid']); ?></p>
          </div>
          </a>
        <?php
          }
        ?>
        </div>
        
      
      </div>
      
      
      
      
      
      
      
      
      <div class="image-preview col-xs-8" style="padding:0; ">
            <div class="modal-footer">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="float: none; ">&times; Ansicht schließen</button>
         </div>
      
          <?php
           foreach($node -> medien as $delta => $medium){
              ?>
               <div style="display: none; text-align: center;" class="previews" id="pres_medium_<?php print $medium -> id; ?>">
                
                <ul style="padding-top: 22px; position: relative; " id="tabs_<?php print $medium -> id; ?>" class="nav nav-tabs">
                  <li class="pull-right"><span class="varianten-title">Varianten</span></li>
                
                <?php
                  $x = 0;
                  foreach($medium ->field_medium_varianten['und'] as $vorschau){
                     ?>
                      <li <?php if($x == 0) print 'class="active"'; ?>>
                        <a href="#file_<?php print $vorschau["fid"]; ?>" data-toggle="tab"><span class="glyphicon glyphicon-plus-sign orange"></span> <?php print $vorschau["title"]; ?></a>
                      </li>
                     <?php 
                    $x++;
                  }   
             ?>
              </ul>
              <div id="tabs_<?php print $medium -> id; ?>Content" class="tab-content">
              <?php
                  $x = 0;
                  foreach($medium ->field_medium_varianten['und'] as $vorschau){
                     ?>
                     <div class="tab-pane fade <?php if($x == 0) print 'active in'; ?>" id="file_<?php print $vorschau["fid"]; ?>">
                     <div class="tab-inner">
                         <div class="inner">
                     <?php
                        $x++;
                        if($vorschau["filemime"]  == 'image/gif'){
                            
                            // Show Gif Animation  
                            $vorschau["path"] = $vorschau['uri'];
                            $image = theme('image', $vorschau);
                       }
                       else $image = theme('image_style',array(
                            'style_name' => 'varianten',
                            'path' => $vorschau['uri']));      
                            
                      print $image;
                     ?>
                      </div>
                     </div>
                     </div>
                     
                      
                     <?php
                  }
              ?>
           
              </div>
              </div>
            <?php
           }
          
          ?>
            
      
      </div>
      

     </div>

    </div>
  </div>
 </div>