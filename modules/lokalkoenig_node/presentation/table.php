<?php

$programme = array();

foreach($node -> medien as $delta => $medium){
    $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $prog = taxonomy_term_load($medium->field_medium_file_program['und'][0]['tid']);
    $parent = taxonomy_get_parents($prog -> tid);
    $parent = taxonomy_term_load(key($parent));
    $programme[$parent -> name]  = $parent -> name;
}
?>

<div style="width: 1200px; margin: 0 auto;">

<div class="width">
  <div class="tabstyle">
     <span>Lieferumfang</span>
  
  </div>


  <div class="kampagnen_medien">
 <h3 class="grey"><span class="orange">Im Paket enthalten</span> <span style="font-size: 0.8em;">Zur Anpassung wird benötigt: <?php print implode(", ",  $programme); ?></span></h3>
 <table class="table table-hover">
      <thead>
        <tr>
          <th style="width: 34%;">Beschreibung</th>
          <th style="width: 15%;" colspan="2">Format</th>
           <th style="width: 15%;">Programmversion</th>
          <th style="width: 30%;" colspan="2">Varianten</th>
          
        </tr>
      </thead>
 
      <tbody>
        <?php
         foreach($node -> medien as $delta => $medium){
           $prog = taxonomy_term_load($medium->field_medium_file_program['und'][0]['tid']);
           $parent = taxonomy_get_parents($prog -> tid);
           $parent = taxonomy_term_load(key($parent));
        ?>
        <tr>
          <td>
           <?php if($medium -> variante == 1): ?>
             <strong><a href="#" data-toggle="modal" onclick="activetePreview(<?php print $medium -> id; ?>)" data-target="#presentation-big-images"><?php print $medium -> title; ?></a></strong>
                    <br /><em>Formatvariante</em>
           <?php else: ?>   
              <strong><a href="#" data-toggle="modal" onclick="activetePreview(<?php print $medium -> id; ?>)" data-target="#presentation-big-images"><?php print $medium -> title; ?></a></strong>
              
           <?php endif; ?>   
          </td>
          <td>
             <?php 
              if($medium -> mtype == 'print') print '<img src="/sites/all/themes/bootstrap_lk/design/icon-printanzeige.png" width="19" height="19" />';
              else print '<img src="/sites/all/themes/bootstrap_lk/design/icon-webanzeige.png" width="19" height="19" />';
            ?>
          </td>
          <td>
            <?php
            $tax = taxonomy_term_load($medium->field_medium_typ['und'][0]['tid']);
            $tax_name = $tax -> name;
            
            if($tax -> description){
                $tax_name = $tax -> description;
            }
            
            print ' <span class="grey">' . $tax_name . '</span>'; ?>
          </td>
          <td width="1%"><img style="margin-right: 15px; width:19px; height:19px;" data-placement="top" data-toggle="tooltip" src="<?php print file_create_url($parent->field_programm_icon['und'][0]['uri']); ?>" title="<?php print $prog -> name; ?>" /> <span class="grey"><?php print $prog->field_programm_kurzversion['und'][0]['value'] ?></span></td>
          <td class="center">
            <?php 
             $varianten = count($medium -> varianten_titles);  
          ?>
        
          <?php
            $varianten_titles = array();    
            foreach($medium ->field_medium_varianten['und'] as $vorschau){
                 $varianten_titles[] = $vorschau["title"];
                 print '<a href="#" data-toggle="modal" onclick="activetePreview(' . $medium -> id . ','. $vorschau["fid"] .'); return false;" data-target="#presentation-big-images">' . $vorschau["title"] . ' <span class="glyphicon glyphicon-plus-sign orange"></span></a>&nbsp;&nbsp;';
             }   
            ?>
          </td>
          <td data-placement="top" data-toggle="tooltip" title="Detailansicht"><a href="#" data-toggle="modal" onclick="activetePreview(<?php print $medium -> id; ?>)" data-target="#presentation-big-images"><img src="/sites/all/themes/bootstrap_lk/design/lupe.png" alt="Detailansicht anzeigen" /></a></td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
    </div>

   <?php if($node -> purchase_button): ?>
     <hr />
     <?= $node -> purchase_button; ?>
   <?php endif; ?>
</div>
</div>  

