<?php 
     
     $seiten = array();
     $vku_status = $vkunew -> getStatus();
     $vku_author = $vkunew -> getAuthor();

     $pages = $vkunew -> getPages();
     foreach($pages as $page){
         $seiten[] = vku_create_item_desc($vkunew, $page);
     } 

     $id = $vkunew -> getId();
      $page = 0; 
    ?>


<script>

var vkunodeorderurl = '<?php print url('vku/' . $id . '/changeorder'); ?>';
var vku_url = '<?php print url('vku'); ?>';

</script>


<style>
    .special>li>.row>.col-md-2>.glyphicon.glyphicon-move {
        margin-top: 15px;
    }  
    
    </style>


<div class="row edit-form">
 <div class="col-md-12 col-xs-12">
 
  <ul class="list-group special" style="list-style: none;">
    <li>
        <div class="well well-white" style="margin-bottom: 0;">
        <div class="row">
          <div class="col-md-6 col-xs-6">
              <?php if($vku_status == 'template'): ?>  
              <p><strong>Wiederverwendbare Verkaufsunterlage</strong></p>      
              
              <p>Erstellen Sie <strong>personalisierte Vorlagen</strong> und verwenden Sie diese bei ihrer nächsten Verkaufsunterlage.</p>
             <?php print $template_form; ?>
             
             <hr />
             
             <p><a href="<?php print url('user/' . $vku_author . "/vkusettings"); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Zurück zu Ihren Vorlagen</p>
           
              <?php else :?>
              <p><strong style="font-size: 18px;">Nur noch eine Minute bis zu Ihrer Verkaufsunterlage</strong></p>      
              <p>Generieren Sie nun Ihre Verkaufsunterlage. Sie können...</p> 
              
              <ul>
                  <li>weitere verkaufsaktive Dokumente hinzufügen</li>
                  <li>Reihenfolge der Dokumente verändern</li>
                  <li>Dokumente nach Ihren Wünschen erweitern</li>
                  <li>Ihre Kontaktinformationen anfügen.</li>
              </ul>
              
              <?php endif; ?>
          </div>
          <div class="col-xs-6">
            <?php  print $addons; ?>
              
            <?php 
                if(lk_is_moderator()):
                  
                 ?>  
              <div class="well well-white">
              <strong>
                  <a href="<?php print url("vku/" . $id . "/ppt"); ?>"><span class="glyphicon glyphicon-gift"></span> APLHA: Powerpoint-Test generieren</a>
              </strong>
              </div>
                 <?php
                endif;
            
            ?>
              
        </div>
      </div>
        </div>
    </li>
    
   
   </ul>     
    <ul class="list-group special-moveable optionalpages special" style="padding-top: 20px; list-style: none;">
     <?php foreach($seiten as $seite) { 
        if($seite["candeactivate"] AND $seite["data_active"] == 0){
        
        } 
        else $page = $page + $seite["pages"];   
     ?>
     
     <li class="single page <?php if($seite["data_active"] == 0) print ' disabled'; ?>" pages="<?php print $seite["pages"]; ?>" id="page_<?php print $seite["id"]; ?>">
      <div class="row clearfix">
        <div class="col-md-2 col-xs-2">
             <span class="glyphicon glyphicon-move"></span>
            <!--<span class="glyphicon glyphicon-<?php print $seite["icon"]; ?>"></span>-->
           Seite <span class="page">
            <?php 
            if($seite["pages"] > 1) print ($page - $seite["pages"] + 1) . " - " . $page; 
            else print $page; ?>

            </span><br />
           <!--<strong><?php print $seite["short"]; ?></strong>-->
        </div>
        <div class="col-md-10 col-xs-10">
            <div class="clearfix">
                <ul class="list-inline pull-right">
                 <?php if($seite["candeactivate"]) { ?>
                  <li class="pull-right">
                      <a href="<?php print url($seite["candeactivate_url"]); ?>" class="btn-inverse-red ajax" data-toggle="tooltip"  onclick="vkuchangestatus(this); return false;" title="Seite deaktivieren"><span class="btn btn-danger"><span class="glyphicon glyphicon-remove-circle"></span></span></a>
                      <a href="<?php print url($seite["candeactivate_url"]); ?>" class="btn-inverse-green ajax" data-toggle="tooltip" onclick="vkuchangestatus(this); return false;" title="Seite aktivieren">Aktivieren &nbsp;<span class="btn btn-success"><span class="glyphicon glyphicon-ok-sign"></span></span></a>
                  </li>
                 <?php } ?>


                  <li><a data-toggle="tooltip" class="btn btn-primary btn-sm" onclick="startgenerate_pdf(this); return false;" preview="<?php print url($seite["preview"]); ?>" href="#" class="" title="PDF Vorschau generieren"><span class="glyphicon glyphicon-print"></span></a></li>
                 <?php if($seite["edit"]){
                   ?>
                 <li><a data-toggle="tooltip" class="btn btn-primary btn-sm" href="<?php print url($seite["edit"], array("query" => drupal_get_destination())); ?>" class="" title="<?php print $seite["edit_title"]; ?>"><span class="glyphicon glyphicon-pencil"></a></li>
                  <?php
                 } ?>
                    
                 <?php if(isset($seite["edit_form"])){
                   ?>
                 <li><a data-toggle="tooltip" class="btn btn-primary btn-sm show-form" href="#" title="<?php print $seite["edit_title"]; ?>"><span class="glyphicon glyphicon-pencil"></a></li>
                  <?php
                 } ?>
                 
                 
                <?php if($seite["candelete"]) : 
                     ?>
                    <li><a data-toggle="tooltip" class="btn btn-danger btn-sm optindelete-vku" href="<?php print url($seite["candelete_url"]); ?>" optintitle="Löschen" optin="<?php print ($seite["candelete_desc"]); ?>" title="Seite löschen"><span class="glyphicon glyphicon-trash"></span></a></li>
                  <?php endif; ?>

               </ul>
               <h4 style="margin-top: 5px;"><?php print $seite["title"]; ?></h4>
               <?php 
               if($seite["data_module"] == 'node'): 
               ?>
                <p><?php print $seite["desc"]; ?></p>
                
                <?php 
                if(isset($seite["edit_form"])):
                    print $seite["edit_form"];
                endif;
                ?>
                
                <?php endif; ?>
            </div>
        </div>
        </div>
    </li>
    <?php } ?>
   </ul>
  
    
  
 
  <div class="clearfix well" style="margin-top: 20px;"> 
  <div class="pull-right todo" style="<?php if($submitform): ?>padding-top: 26px;<?php else: ?>padding-top: 5px; <?php endif; ?> font-size: 1.8em; padding-right: 10px;"><?php print $page; ?> Seiten</div>
  <?php
    if($vku_status == 'template'){
        ?>
        <a href="<?php print url($vkunew -> renewUrl()); ?>" class="btn btn-success">Vorlage jetzt verwenden</a>
        <?php
    }
 
    print $submitform; 
  ?>
  </div>
  <a href="<?php print url('vku/'.  $id .'/delete'); ?>" class="optindelete small pull-right" optintitle="Ja, Verkaufsunterlagen wirklich löschen" optin="Sind Sie sicher, dass Sie die Verkaufsunterlagen löschen möchten?">(Verkaufsunterlage verwerfen)</a>
  
  
 