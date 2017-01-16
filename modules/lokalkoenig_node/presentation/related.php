
<div class="clearfix kampa-related" style="background: #ededed; background-image: linear-gradient(#ededed 0%, white 100%); margin-top: 40px;">
<div class="presentation_grey" style="background: transparent;">
 <div class="width" style="padding-top: 20px; padding-bottom: 20px;">
    <div class="row clearfix">
   <div class="col-md-3"> 
      <h3 style="margin:0; font-weight: bold; font-size:1.6em">Weitere Kategorien</h3>
      <hr />
      <p><strong>Die Kampagne ist in folgenden Kategorien eingeordnet:</strong></p>
     
     <?php
       $terms = array();
      
       $anlass = $node->field_kamp_themenbereiche['und'];
       foreach($anlass as $tax){
          $term = taxonomy_term_load($tax["tid"]);
          $terms[] = $term;
       }
          
            $config = array(
              'vid' => 3,
              'exclude_tid' => NULL,
              'root_term' => 0,
              'entity_count_for_node_type' => NULL
            );  
  
            $tax_bisher = $node->field_kamp_themenbereiche['und'];
  
            $selection = array();
            foreach($tax_bisher as $tax){
              $term = taxonomy_term_load($tax["tid"]);
              $selection[] = $tax["tid"];  
              // Hier noch die Line darstellen
              $values[] = $term -> name . ' [tid:'. $term -> tid  .']';
            }
  
            $taxes = _hierarchical_select_dropbox_reconstruct_lineages_save_lineage_enabled('hs_taxonomy', $selection, $config);
            
       
     ?>
      <ul class="list-unstyled">    
         <?php foreach($taxes as $term) { 
            $show = array();
            foreach($term as $t){
               $show[] = '';
               $t["label"] = l($t["label"], 'suche', array("query" => array('f' => array(0 => 'field_kamp_themenbereiche:' . $t["value"]))));
               if(count($show) == 1) $t["label"] = '<strong>' . $t["label"] . '</strong>'; 
               print '<li>'. implode("-", $show) .' '. $t["label"] .'</li>';
            }
            print '<li>&nbsp;</li>';
         } ?>
      </ul>
   </div>
   <div class="col-md-9">
    <div class="kampa-related">
      <h3 style="margin:0; font-weight: bold; font-size: 1.6em">Diese Kampagnen könnten Sie ebenfalls interessieren ...</h3>
      <hr />
     <?= $node -> mlt ?>
    </div>
  </div>
    </div>
</div>
</div>

</div>