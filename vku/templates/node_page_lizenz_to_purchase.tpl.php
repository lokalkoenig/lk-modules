<div class="direct-purchase">
  <a class="btn btn-lg btn-blue-arrow pull-left" href="#" id="purchase-show-link" onclick="jQuery('#purchaselink').toggle('slow'); return false;">
    Direktdownload <span class="glyphicon glyphicon-chevron-right"></span>
  </a>
  <div style="display: none; background: #eeeeee; height: auto;" id="purchaselink">
    <h4 style="padding: 4px 4px; padding-left: 20px;">Wollen Sie diese Kampagne jetzt kostenpflichtig herunterladen?<br />
    <?php

    if($ausgaben):
      ?>
        <div style=" font-size: 16px; margin-top: 15px;">
          <div class ="row clearfix">
            <div class="col-xs-9">Sie bestellen die Kampagne für folgende Ausgaben: ' . implode(" ", $ausgaben) . '</div>
            <div class="col-xs-3 text-center"><a class="btn btn-sm btn-primary" href="<?php print url($link, ['query' => drupal_get_destination()])?>"><span class="glyphicon glyphicon-globe"></span> Ausgaben wechseln</a></div>
          </div>
        </div>
      <?php
    endif;
   ?>

    <span style="font-size: 15px; display: block; line-height: 1.6em; margin-top: 10px;"><label style="font-weight: normal;"><input type="checkbox" onclick="jQuery('#purchase-button').toggle();" /> Hiermit bestätige ich, dass ich die Nutzungsbedingungen gelesen habe und diese akzeptiere. Die aktuellen Nutzungsbedingungen finden Sie <?php print l("hier", "node/257", array("attributes" => array("target" => "_blank"))); ?>.</label></span>
    </h4>
    <a class="btn btn-blue-arrow" style="display:none; margin: 15px; margin-top: 5px;" id="purchase-button" data-loading-text="Bitte warten..." onclick="lkpurchase(<?php print $nid; ?>)" nid="<?php print $nid; ?>"><span class="glyphicon glyphicon-shopping-cart"></span> Kostenpflichtig bestellen.</a>
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