<?php
  /** VKU PDF erstellen */  

  $url = url('user/' . $account -> uid . '/vku/' . $vku -> get("vku_id") . "/generate");

?>
<div class="row text-center">
 
 <div class="col-md-12">
 <div class="download-indicator well">
  <h3 style="margin-top: 0;">Ihre Verkaufsunterlage wird gerade erstellt.</h3> 
  <p>Die Generierung der Verkaufsunterlagen kann bis zu einer Minute dauern. Bitte haben Sie etwas Geduld.</p> 

 <div class="visualprogress"></div>
</div>
 
  <div class="download well" style="display: none;">
    <h3 style="margin-top: 0;">Ihre Verkaufsunterlage ist fertig</h3> 
    <p>Sie können die Verkaufsunterlage nun direkt herunterladen und verschicken.</p> 
  
    <a href="#" class="durl btn btn-yellow-arrow"><span class="glyphicon glyphicon-ok"></span> PDF jetzt downloaden (<span class="filesize"></span>)</a>
    <br />(<a href="<?php print url($vku -> url()); ?>">Weitere Informationen</a>)
  </div>
</div>
</div>

<style>
     .visualprogress {
      width:10px; height: 20px; background: url(/misc/progress.gif);
   }

</style>

<script>
   jQuery(document).ready(function(){
       jQuery('.pdfgenerate').hide();
       jQuery('.visualprogress').animate({width: '710px'}, 20000);
          
       jQuery.ajax({
                url : '<?php print $url; ?>',
                type: "get",
                data : {'ajax' : 1},
                success: function(data, textStatus, jqXHR){ 
                   if(!data.downloadlink){
                      jQuery('.visualprogress').hide();
                      lk_add_js_modal_optin('Sorry', 'Es tut uns leid. Wir haben Probleme mit dieser VKU festgestellt. <br /><br />Ein Mitarbeiter wird sich bei Ihnen dazu melden.<br /><br /><hr /><b>Response</b><br /><code style="white-space: normal;">'+ data +'</code>', '', '');
                   }
                   else {
                      jQuery('.download-indicator').hide();
                      jQuery('.alert.alert-block.alert-success').hide();
                      jQuery('.download a.durl').attr('href', data.downloadlink);
                      jQuery('.download').show();  
                      jQuery('.download a .filesize').html(data.filesize);
                   } 
                }
              });
        
   });

</script>





