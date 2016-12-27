
<?php
    $vku = new VKUCreator($vku -> getId());
    $title = $vku -> get("vku_title");
    $status = $vku ->getStatus();
?>

<div class="vku-generator vku-generator-template" data-template-url="<?php print url('user/'. $vku ->getAuthor() . "/vkusettings"); ?>" data-signature="<?php print $vku -> get("vku_changed"); ?>" data-status="<?php print $status; ?>" data-preview-url="<?php print url('vku/'. $vku ->getId() .'/callback', array("query" => array("preview" => 1)));?>" data-save-url="<?php print url('vku/'. $vku ->getId() .'/callback');?>">
  
    <div class="item item-active" id="vku-content">
        <div class="pull-right hide-active"><button id="edit-vku-content" class="btn btn-default" onclick="vku2.goto('vku-content');">Bearbeiten <span class="caret"></span></button></div>
        <div class="title-wrapper">
            <h2><span>Inhalte der Vorlage</span></h2> 
        </div>
       <div class="content well well-white">
           <div class="row">
               <div class="col-xs-8">
                   
                  <p>
                      Ziehen Sie jedes Element an die bevorzugte Position. Klicken Sie auf Bearbeiten <span class="caret"></span>, um die einzelnen Elemente zu bearbeiten. 
                   </p>
                    
                   
                   <div id="vku2_items_container">
                     <?php print $items; ?>
                   </div>      
                      <div class="vku2-document-empty">
                            Fügen Sie Dokumente Ihrer Vorlage über <br />das Menü rechts hinzu.
                      </div>
            <hr />

              
            <div class="hinweis-save-data">
                <div class="well">
                <div class="row">
                    <div class="col-xs-1 text-center">
                        <span class="glyphicon glyphicon-exclamation-sign"></span>
                    </div>   
                    <div class="col-xs-11">
                        <p><strong>Sie haben noch nicht alle Änderungen gespeichert</strong><br />
                            Bitte speichern Sie Ihre Zwischenschritte ab, bevor Sie weitermachen</p>
                        
                         <div class="last-saved-time">
                            <span class="small">(Ihre Verkaufsunterlage wurde zuletzt
                                <span class="last-saved-time-data"><?php print format_date($vku -> get("vku_changed"), "short"); ?></span>
                                gespeichert.)
                            </span>
                        </div>
                    </div> 
                    </div>    
                </div>    
            </div>
            
            
            <div class="clearfix">
                <div class="page-count-wrapper pull-right">Aktueller Umfang:<br /><span class="page-count">-</span></div>
                <button id="save-all" class="btn btn-success btn-md" onclick="vku2.saveAll();">Speichern</button>
                <button id="save-all-fin" class="btn btn-success btn-lg" onclick="vku2.saveAllFinish();">Vorlage abschließen</button> 
            </div>    
                       
                    
               </div>    
               <div class="col-xs-4">
                   
                   
                    <div class="field-type-text form-wrapper form-group">
                        <label for="edit_vku_title" style="font-weight: bold;">Titel der Vorlage <sup class="form-required">(Pflichtfeld)</sup></label>
                        <input class="text-full form-control form-text required" type="text" id="edit_vku_title" name="vku_title" value="<?php print $vku -> get("vku_title"); ?>" size="60" maxlength="75">
                   </div>
                   
                    
                   <h3>Verfügbare Dokumente</h3>
                   <?php print $dokumente; ?>
               </div>    
           </div>    
       </div>
    </div>    
</div>

<div class="generate-info generate-info-loading" style="display: block">
    <div>
        <div>
            <div class="well">Der VKU-Editor wird geladen.</div>
        
        </div>
    </div>       
</div>  

<div class="generate-info generate-info-error">
    <div>
        <div>
            <div class="well">
                <h2 style="margin-top: 0;"><span class="glyphicon glyphicon-bell"></span> Hinweis</h2>
                <p>Wir haben einen Fehler festgestellt. Eventuell haben Sie ein weiteres Browser-Fenster geöffnet 
                    oder Sie haben die Zurück-Funktion des Browsers benutzt.</p>
                <p><a class="btn btn-primary" href="<?php $vku -> url(); ?>">Seite aktualisieren</a></p>
            </div>
        
        </div>
    </div>       
</div>  



<div id="preview-container">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
    <ul class="preview-navigation"></ul>    
    <div class="well well-white"></div>
</div>    

<div class="remove-vku-link">
   
    <a href="<?php print url($vku -> removeUrl()); ?>" class="optindelete small pull-right" optintitle="Ja, Verkaufsunterlagen wirklich löschen" optin="Sind Sie sicher, dass Sie die Verkaufsunterlagen löschen möchten?">(Verkaufsunterlage verwerfen)</a>
</div>