<?php
 /** Zeigt Lizenz-Downloads an */
  print $admin;  
  
  $status = $vku -> getStatus();
  $vku_purchased_date = $vku -> get("vku_purchased_date");  
  $tage = variable_get('lk_vku_max_download_time', 30);
  $company = $vku -> get("vku_company")
  
?>
<div class="clearfix"><a class="btn btn-default pull-right" href="<?php print url("user/" . $account -> uid . "/vku"); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Zurück zur Übersicht</a></div>
<hr />

<div class="row">
<div class="col-md-6">

<dl class="dl-horizontal">
  <dt>Titel</dt>
  <dd><?php print $vku -> get("vku_title"); ?></dd>
  
  <?php if($company) { ?>
    <dt>Unternehmen</dt>
    <dd><?php print $company; ?></dd>
  <?php } ?>
  
  <?php if($vku -> get("vku_generic")) { ?>
    <dt>&nbsp;</dt>
    <dd><em>Direktdownload</em></dd>
  <?php } ?>
  
  
  <dt>Erstellt am</dt>
  <dd><?php print format_date($vku -> get("vku_created")); ?></dd>
  
  <dt>Lizensiert am</dt>
  <dd><?php print format_date($vku -> get("vku_purchased_date")); ?></dd>  
</dl>
</div>

<div class="col-md-6">
  <?php if($status == 'purchased') : ?>
   <div class="well">Sie können auf dieser Seite die Lizenzen für <?php print $tage; ?> Tage downloaden. Zusätzlich können Sie einen Download-Link generieren, den Sie an Mitarbeiter schicken können.</div>
  <?php else : ?>
    <div class="well">Die Downloads der vorhandenen Lizenzen sind abgelaufen.</div>
  <?php endif; ?> 
</div>
</div>

<hr />


<h4><span class="glyphicon glyphicon-download"></span> Lizenzen herunterladen</h4>

<div class="panel panel-default panel-info">
<div class="panel-body">
      <div class="view-content">
<table class="table table-striped table-hover">
  <tr>
    <th width="50%">Kampagne</th>
    <th class="text-center" width="10%">Downloads</th>
    <th class="text-center" width="40%">Download</th>
   </tr>
    
<?php 
  foreach($lizenzen as $l){
     
    $bild = $l -> node->field_kamp_teaserbild['und'][0]['uri'];
    $img = image_style_url('kampagne_klein', $bild);
    $sid = $l -> node -> sid;
    ?>
   
    <tr>
      <td>
        <div class="pull-left" style="margin-right: 10px;"><img src="<?php print $img; ?>" /></div>
        <strong><?php print $l -> node -> title; ?></strong><br />
        <span class="label label-primary"><?php print $sid; ?></span>
      </td>
      <td class="text-center"><?php print $l -> lizenz_downloads ?> von <?php print $l -> download_max; ?></td>
      <td class="text-center">
        
      <?php  if($l -> can_download): ?>
          <a class="btn btn-primary btn-sm" href="<?php print $l -> download_link_direct; ?>"><span class="glyphicon glyphicon-hdd"></span> Direktdownload ZIP (<?php print format_size($l -> lizenz_download_filesize); ?>)</a>
      <?php 
        else: 
        
          print '<em>' . $l -> can_download_info["reason"] . '</em>'; 
        
        endif; 
      ?>
      
      </td>
     </tr> 
    
    <?php if($l -> can_download): ?>
        <tr><td colspan="3"><div class="alert">
        <b>Downloadlink:</b> <input style="width: 75%; display: inline; margin-left: 10px;" type="text" class="form-control form-text" value="<?php print $l -> download_link_external; ?>" />
         <small>(Downloads gültig bis zum <?php print format_date($l -> lizenz_until); ?>)</small>
        </div>
        
        </td></tr>
        <?php
        
     endif;
  }
?>
</table>
</div></div></div>


<?php $nodes = $vku -> getKampagnen(); ?>

<?php if($nodes) : ?>
<hr />
<h4>Kampagnen der Verkaufsunterlage</h4>

<div class="well">
  <a href="<?php print url($vku -> renewUrl()); ?>"><strong><span class="glyphicon glyphicon-refresh"></span> Verkaufsunterlage duplizieren</strong></a><br />
  Die aktuelle Verkaufsunterlage wird kopiert und Sie können eine neue Verkaufsunterlage mit den vorhandenen Daten erstellt.
</div>

<hr />
<?php 

foreach($nodes as $node){
  $node_load = node_load($node);  
  $node_view = node_view($node_load, 'teaser');  
   print render($node_view);
}
endif; 

