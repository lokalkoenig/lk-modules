<?php

//$bg = image_style_url('presentation-image-background', ($node->field_kamp_pres_image_print['und'][0]['uri']));
//$image = getimagesize($bg);


//$bg2 = image_style_url('presentation-image-background', ($node->field_kamp_pres_image_online['und'][0]['uri']));
//$image2 = getimagesize($bg2);

  $x = 0;
  foreach($node -> medien as $media){
      //dpm($media);
      
      if(!isset($media->field_medium_bild['und'][0]['uri'])){
        continue;
        return ;
      }
  
  
      if($x % 2 == 0) $class = 'presentation_grey';
      else $class = 'presentation_white';
  
      $x++;
  
      $bg = image_style_url('presentation-image-background', ($media->field_medium_bild['und'][0]['uri']));
      $variantentitles = $media -> varianten_titles;
      $media_id = $media -> id;
      $title = $media -> title;
      $desc = $media->field_medium_beschreibung['und'][0]['safe_value'];
  
  ?>
  <div class="<?php print $class; ?>">
    <div class="width" style="margin-top: 0; width: 1200px; height: 390px;  background-position: right; background-image: url(<?php print $bg; ?>); background-repeat: no-repeat;">
    
    <div class="width" style="margin-top: 0">
      <div style="width: 390px; padding-top: 30px; margin-top: 0; ">
      <h2><?php print $title; ?></h2>
      <div style="height: 210px;"><p><?php print $desc; ?></p>
      
      <p><strong>Beinhaltet Farbvarianten: </strong><?php print implode(", ", $variantentitles); ?></p></div>
      <p><a class="btn btn-lg btn-blue-arrow" href="#" href="#" data-toggle="modal" onclick="activetePreview(<?php print $media_id; ?>)" data-target="#presentation-big-images">Vorschauansicht <span class="glyphicon glyphicon-chevron-right"></span></a></p>
    </div>
    </div>
    </div>
</div>
  
  
  
  <?php
  
  }
  
return ;  
?>


<div class="presentation_grey">
    <div class="width" style="width: 1200px; height: 390px;  background-position: right; background-image: url(<?php print $bg; ?>); background-repeat: no-repeat;">
    
    <div class="width">
      <div style="width: 390px; padding-top: 30px; ">
      <h2><?php print $node->field_kamp_pres_title_print['und'][0]['value']; ?></h2>
      <div style="height: 210px;"><p><?php print $node->field_kamp_pres_desc_print['und'][0]['value']; ?></p>
      
      <?php
        foreach($node -> medien as $media){
          if($media -> mtype == 'print'){
             $media_id = $media -> id;
            $variantentitles = $media -> varianten_titles;
            break;
          }
        }
      ?>
      
      <p><strong>Beinhaltet Farbvarianten: </strong><?php print implode(", ", $variantentitles); ?></p></div>
      <p><a class="btn btn-lg btn-blue-arrow" href="#" href="#" data-toggle="modal" onclick="activetePreview(<?php print $media_id; ?>)" data-target="#presentation-big-images">Vorschauansicht <span class="glyphicon glyphicon-chevron-right"></span></a></p>
    </div>
    </div>
    </div>
</div>


<div class="presentation_white">
    <div class="width" style="width: 1200px; height: 390px;  background-position: right; background-image: url(<?php print $bg2; ?>); background-repeat: no-repeat;">
    
    
    
    
    <div class="width">
      <div style="width: 410px; padding-top: 30px; ">
      <h2><?php print $node->field_kamp_pres_title_online['und'][0]['value']; ?></h2>
      <div style="height: 180px;"><p><?php print $node->field_kamp_pres_desc_online['und'][0]['value']; ?></p>
      
      <?php
        foreach($node -> medien as $media){
          if($media -> mtype == 'online'){
            $media_id = $media -> id;
            $variantentitles = $media -> varianten_titles;
            break;
          }
        }
      ?>
      
      <p><strong>Beinhaltet Farbvarianten: </strong><?php print implode(", ", $variantentitles); ?></p></p></div>
      <p><a class="btn btn-lg btn-blue-arrow" href="#" data-toggle="modal" onclick="activetePreview(<?php print $media_id; ?>)" data-target="#presentation-big-images">Vorschauansicht <span class="glyphicon glyphicon-chevron-right"></span></a></p>
    </div>
    </div>
    </div>
</div>