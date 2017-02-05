<?php
/** Template für alle Kampagnen vor den Lizenzen */
  print $admin;  
 
  $id = $vku -> getId();
  $vku = new VKUCreator($id);
  $file = $vku -> get('vku_ready_filename');
  $status = $vku -> getStatus();
  
  $ppt = false;
  
  if(vku_is_update_user_ppt()){
      $ppt = $vku -> get("vku_ppt_filename");
      if($ppt){
        $ppt = true;
      }     
   }   
      
   $company = $vku -> get("vku_company");  
  
  
?>

<div class="clearfix"><a class="btn btn-default pull-right" href="<?php print url($vku -> userUrl()); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Zurück zur Übersicht</a></div>

<hr />

<div class="row">
  <div class="col-xs-6">

  <dl class="dl-horizontal">
    <dt>Titel</dt>
    <dd><?php print $vku -> get("vku_title"); ?></dd>
    
    <?php if($company): ?>
        <dt>Unternehmen</dt>
        <dd><?php print $vku -> get("vku_company"); ?></dd>
    <?php endif; ?>
        
    <dt>Erstellt am</dt>
    <dd><?php print format_date($vku -> get("vku_created")); ?></dd>
    
    <dt>Zuletzt geändert</dt>
    <dd><?php print format_date($vku -> get("vku_changed")); ?></dd>
  </dl>

 


  </div>

  <div class="col-xs-6">
     
      <?php if($status == 'active'): ?>
        <h3 style="margin-top: 0;"><span class="glyphicon glyphicon-star"></span> Aktive Verkaufsunterlage</h2>
        <p>Diese Verkaufsunterlage ist noch nicht fertig.</p> 

      <?php elseif($status == 'deleted') : ?>
        <h3 style="margin-top: 0;"><span class="glyphicon glyphicon-trash"></span> Gelöschte Verkaufsunterlage</h2>
        <p>Diese Verkaufsunterlage wurde gelöscht.</p> 
      <?php elseif($status == 'created') : ?>
        <h3 style="margin-top: 0;"><span class="glyphicon glyphicon-trash"></span> Aktive Verkaufsunterlage</h2>
        <p>Diese Verkaufsunterlage wurde vom Benutzer fertig gemacht. Eine PDF wird gerade erstellt.</p> 
      <?php else : ?>
        <h3 style="margin-top: 0;">Aktionen</h3>  
      <?php endif; ?>


     <ul class="list-unstyled well">
      <?php if($file AND !$ppt) : ?>
       <li style="margin-bottom: 20px;"><strong><a href="<?php print url($vku -> downloadUrl()); ?>"><span class="glyphicon glyphicon-download"></span> Verkaufsunterlagen herunterladen</a></strong> (<?php print format_size($vku -> get("vku_ready_filesize")); ?>)</li>
      <?php endif; ?>
       
      <?php if($ppt) : ?>
       <li style="margin-bottom: 20px;"><strong><span class="glyphicon glyphicon-download"></span> Verkaufsunterlagen herunterladen</strong><br /> 
           <ul class="list-inline" style="margin-top: 10px; line-height: 30px;">
               <li>
                   <a class="btn btn-primary btn-hollow" href="<?php print url($vku -> downloadUrl()); ?>">PDF <small>(<?php print format_size($vku -> get("vku_ready_filesize")); ?>)</small></a>
               </li>
               <li>
                   <a class="btn btn-primary btn-hollow" href="<?php print url($vku ->downloadUrlPPT()); ?>">Powerpoint <small>(<?php print format_size($vku -> get("vku_ppt_filesize")); ?>)</small></a> 
               </li>
           </ul>   
       </li>
           
       
      <?php endif; ?> 
       
       
       
      <?php if($status != 'deleted') :?> 
        <li  style="margin-bottom: 20px;"><strong><a class="optindelete" optintitle="Ja, Verkaufsunterlagen wirklich löschen" optin="Sind Sie sicher, dass Sie die Verkaufsunterlagen löschen möchten?" href="<?php print url('user/' . $account -> uid . "/vku/" . $id . "/delete"); ?>"><span class="glyphicon glyphicon-trash"></span> Verkaufsunterlagen verwerfen</a></strong><br />
        <small>Die Verkaufsunterlage wird aus der Übersicht gelöscht und wird automatisch nach 30 Tagen gelöscht.</small>
        </li>
      <?php endif; ?>
    
       <li><a href="<?php print url($vku -> renewUrl()); ?>"><strong><span class="glyphicon glyphicon-refresh"></span> Verkaufsunterlagen erneuern</strong></a><br />
       <small>Die Verkaufsunterlage wird verworfen und Sie können die Verkaufsunterlage neu erstellen und bspw. andere Kampagnen noch hinzufügen.</small>
      
      
      </li>
   </ul>   
  </div>
</div>

<?php if($form) : ?>

<hr />

<h3>Medienlizenzen bestellen</h3>
<p class="well">Hier können Sie die Lizenzen für die Kampagnen bestellen. Sie können eine aber auch mehrere Lizenzen buchen und danach die Quelldateien zur Weiterverarbeitung herunterladen.</p> 

<?php print $form; ?>


<script>
      //
      jQuery('.form-checkboxes input[type=checkbox]').each(function(){
          if(jQuery(this).is(":checked")){
             jQuery('label[for=' + jQuery(this).attr('id') + ']').addClass('checked');
          }
          else {
             jQuery('label[for=' + jQuery(this).attr('id') + ']').removeClass('checked');
          }
      
      });  
      
      jQuery('.form-checkboxes input[type=checkbox]').hide();
      
      jQuery('.form-checkboxes input[type=checkbox]').click(function(){
          if(jQuery(this).is(":checked")){
             jQuery('label[for=' + jQuery(this).attr('id') + ']').addClass('checked');
          }
          else {
             jQuery('label[for=' + jQuery(this).attr('id') + ']').removeClass('checked');
          }
      });
      

</script>

<?php else: ?>


<?php 
  $nodes = $vku -> getKampagnen();

 if(!isset($nodes)) return ; 

 ?>

<hr />
<h3>Enthaltene Kampagnen</h3>

<hr />
<?php 

foreach($nodes as $node){
   $load = node_load($node);
   $node_view = node_view($load, 'teaser');
   print render($node_view);
}
?>



<?php endif; ?>