<?php

$programme = array();
_vku_load_order_node($node);

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
               //print '<div class="thumbnail thumbnail-blue pull-left"><a href="#" data-toggle="modal" data-target="#presentation-big-images">' . $image . '</div>';
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
    
    <?php
      // Anzeige des Kaufen-Buttons  
      if($node -> plzaccess == true AND $node -> online AND !lk_is_in_testverlag($user)) {
       ?>  
       <hr /> 
       <div class="direct-purchase">
       <a class="btn btn-lg btn-blue-arrow pull-left" href="#" id="purchase-show-link" onclick="jQuery('#purchaselink').toggle('slow'); return false;">Direktdownload <span class="glyphicon glyphicon-chevron-right"></span></a>
       <div style="display: none; background: #eeeeee; height: auto;" id="purchaselink">
      <h4 style="padding: 4px 4px; padding-left: 20px;">Wollen Sie diese Kampagne jetzt kostenpflichtig herunterladen?<br />
       
       
       <?php 
        
        $extra = '';
        
        if(lk_is_telefonmitarbeiter($user)){
          $account = _lk_user($user);  
       
           $ausgaben = array();
            
           if(isset($account->profile['mitarbeiter']->field_ausgabe['und'])){
              foreach($account->profile['mitarbeiter']->field_ausgabe['und'] as $a){
                $ausgaben[] = format_ausgabe_kurz($a["target_id"]);
              }
           }
          
          $link = '<a class="btn btn-sm btn-primary" href="'. url("user/" . $account -> uid . "/setplz", array("query" => drupal_get_destination())) .'"><span class="glyphicon glyphicon-globe"></span> Ausgaben wechseln</a>';
          
          $extra = ('<div style=" font-size: 16px; margin-top: 15px;">
           <div class ="row clearfix">
           <div class="col-xs-9">Sie bestellen die Kampagne für folgende Ausgaben: ' . implode(" ", $ausgaben) . '</div> 
           <div class="col-xs-3 text-center">'. $link .'</div></div></div>');
        
          print $extra;
        }     
       
       
       ?>
       
       
       
       <span style="font-size: 15px; display: block; line-height: 1.6em; margin-top: 10px;"><label style="font-weight: normal;"><input type="checkbox" onclick="jQuery('#purchase-button').toggle();" /> Hiermit bestätige ich, dass ich die Nutzungsbedingungen gelesen habe und diese akzeptiere. Die aktuellen Nutzungsbedingungen finden Sie <?php print l("hier", "node/257", array("attributes" => array("target" => "_blank"))); ?>.</label></span>
       
       </h4>
        <a class="btn btn-blue-arrow" style="display:none; margin: 15px; margin-top: 5px;" id="purchase-button" data-loading-text="Bitte warten..." onclick="lkpurchase(<?php print $nid; ?>)" nid="<?php print $nid; ?>"><span class="glyphicon glyphicon-shopping-cart"></span> Kostenpflichtig bestellen.</a>
       
       </div>
       </div>
       
       <?php 
      }
      // Downloadbare Lizenz liegt vor
      elseif($node -> plzaccess == false AND $node -> lizenz AND $node -> online){
        $url = _lk_generate_download_link($node -> lizenz -> id);
      
       ?>
        <hr /> 
        <?php print theme('node_page_lizenz_purchased', array("lizenz" => $node -> lizenz, 'url' => $url)); ?>
        
      
       <?php
      }
      else {
         ?> 
          <hr /> 
       <div class="direct-purchase">
            <a data-toggle="tooltip" title="Die Kampagne ist im Moment gesperrt" class="btn btn-lg btn-grey-disabled pull-left" href="javascript: void(0);" id="purchase-show-link">Direktdownload <span class="glyphicon glyphicon-chevron-right"></span></a>
       </div>
        
        <?php  
      }
      
      
    ?>
    
    
</div>
</div>  


<script>
   function lkpurchase(nid){
        
       if(jQuery('#purchase-button').attr('done') == 1){
        return ;
       }
        
        
        var btn = jQuery('#purchase-button')
        btn.button('loading');
        
        jQuery('#purchase-show-link').addClass('disabled');
        
       jQuery.ajax({
          url: '/vkudirekt/' + nid
      })
      .fail(function(  ) {
        jQuery('#purchase-show-link').removeClass('disabled');
        var btn = jQuery('#purchase-button')
        btn.button('reset');
      
        alert("Bei der Buchung ist ein unvorhersehbarer Fehler aufgetreten.");
        
      })
      .done(function( data ) {
          if(data.error == 1){
             
             lk_add_js_modal_optin('Hinweis', data.msg, '', '');
             
             //alert(data.msg);
             
             jQuery('#purchase-show-link').removeClass('disabled');
             var btn = jQuery('#purchase-button')
             btn.button('reset');
             return ;
          }                                                        
          
          jQuery( ".direct-purchase" ).replaceWith( data.theme );
      }); 
   }
   
   
   jQuery(document).ready(function(){
   
   
   });

</script>  