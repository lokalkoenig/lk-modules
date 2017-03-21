
<?php
    $vku = new VKUCreator($vku -> getId());
    $title = $vku -> get("vku_title");
    $status = $vku ->getStatus();   
    
    $goto_finalize = false;
    
    if(arg(2) == 'title'){
        $title = null;
    }    
    
    if(arg(2) == 'finalize' AND $title AND $status != 'new'){
        $goto_finalize = true;
    }
?>

<div class="vku-generator vku-generator-<?php print $status; ?>" data-signature="<?php print $vku -> get("vku_changed"); ?>" data-status="<?php print $status; ?>" data-preview-url="<?php print url('vku/'. $vku ->getId() .'/callback', array("query" => array("preview" => 1)));?>" data-save-url="<?php print url('vku/'. $vku ->getId() .'/callback');?>">
    <div class="item <?php if(empty($title)) print ' item-active'; ?>" id="vku-title-wrapper">
         <div class="title-wrapper">
            <div class="action-wrapper hide-active"><button id="edit-title" class="btn btn-default" onclick="vku2.goto('vku-title-wrapper');">Bearbeiten <span class="caret"></span></button></div>
            <h2><span>Titelseite</span></h2>
         </div>
        
        <div class="content well well-white">
            <div class="row">
            
            <div class="col-xs-8">
                    <div class="field-type-text form-wrapper form-group">
                        <label for="edit_vku_title" style="font-weight: bold;">Titel des Angebots <sup class="form-required">(Pflichtfeld)</sup></label>
                        <input class="text-full form-control form-text required" type="text" id="edit_vku_title" name="vku_title" value="<?php print $vku -> get("vku_title"); ?>" size="60" maxlength="75">
                        <p class="help-block">Maximallänge: 75 Zeichen</p>  
                    </div>
                    
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="field-type-text form-wrapper form-group">
                                <label for="edit_vku_company">Name des Unternehmens <sup class="optional">(optional)</sup></label>
                                <input class="text-full form-control form-text required" type="text" id="edit_vku_company" name="vku_company" value="<?php print $vku -> get("vku_company"); ?>" size="60" maxlength="50">
                                <p class="help-block">Maximallänge: 50 Zeichen</p>  
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="field-type-text form-wrapper form-group">
                                <label for="edit_vku_untertitel">Untertitel <sup class="optional">(optional)</sup></label>
                                <input class="text-full form-control form-text required" type="text" id="edit_vku_untertitel" name="vku_untertitel" value="<?php print $vku -> get("vku_untertitel"); ?>" size="60" maxlength="50">
                                <p class="help-block">Maximallänge: 50 Zeichen</p>  
                            </div>
                        </div>
                    </div> 

                    <div style="margin-top: 20px;">
                      <button class="btn btn-success" id="basic_information">Speichern und weiter zum nächsten Schritt</button>
                   </div>
            </div>    
            <div class="col-xs-4">
                <p><strong>Vorlage auswählen <sup class="optional">(optional)</sup></strong></p>
                <p class="help-block">Die Vorlage wird in Ihrer Verkaufsunterlage verwendet. Aktuell verwendete Dokumente werden verworfen.</p>
           
                <ul class="templates">
                    <?php if(!$templates): ?>
                        <li class="template selected no-templates" data-id="0">
                              <em>Sie haben bisher keine Vorlagen erstellt</em>
                        </li>    
                     <?php else: ?>
                             <li class="template selected" data-id="0">
                                    Keine Vorlage
                             </li>
                            
                            <?php foreach($templates as $template): ?>
                                <li class="template" data-id="<?php print $template -> vku_id; ?>">
                                    <span class="pull-right"><?php print date("d.m.Y", $template -> vku_changed); ?></span>
                                    <?php print $template -> vku_title; ?>
                                </li>
                            <?php endforeach; ?>
                                
                            <?php if(count($templates) > 3): ?>
                                <li class="control" onclick="jQuery('ul.templates').toggleClass('list-all');">
                                    
                                    <span class="closed"><span class="glyphicon glyphicon-chevron-down pull-right"></span>
                                    Mehr anzeigen</span>
                                    <span class="opened"><span class="glyphicon glyphicon-chevron-up pull-right"></span>
                                    Weniger anzeigen</span>
                                </li>
                             <?php endif; ?>   
                                
                     <?php endif; ?>   
                  </ul>   
            </div> 
            </div>    
        </div>
    </div>    
    <div class="item <?php if(!empty($title) AND !$goto_finalize) print ' item-active'; ?>" id="vku-content">
        <div class="title-wrapper">
            <div class="action-wrapper hide-active"><button id="edit-vku-content" class="btn btn-default" onclick="vku2.goto('vku-content');">Bearbeiten <span class="caret"></span></button></div>
            <h2><span>Inhalte der Verkaufsunterlage</span></h2> 
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
                            Fügen Sie Kampagnen und Dokumente Ihrer Verkaufsunterlage über <br />das Menü rechts oder über die Suchmaske hinzu.
                      </div>
            <hr />
            
            <div class="kampagnen-count-wrapper">
                Sie haben bereits <strong><span class="kampagnen-count">-</span> von 3 möglichen Kampagnen</strong> hinzugefügt
            </div>
        
            
            <div class="hinweis-save-data">
                <div class="well well-white">
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
                <div class="page-count-wrapper pull-right text-right">Aktueller Umfang:<br /><span class="page-count">-</span></div>
                <button id="save-all" class="btn btn-success btn-md" onclick="vku2.saveAll();">Speichern</button>
                <button id="save-all-fin" class="btn btn-success btn-lg" onclick="vku2.saveAllFinish();">Speichern und weiter zum nächsten Schritt</button> 
            </div>
            
          
            
                       
                   
               </div>    
               <div class="col-xs-4">
                   <h3>Verfügbare Dokumente</h3>
                   <?php print $dokumente; ?>
               </div>    
           </div>    
       </div>
    </div>    

    <div class="item <?php if($goto_finalize) print 'item-active'; ?>" id="vku-final">
          
        <div class="title-wrapper">
            <h2><span>Verkaufsunterlage fertigstellen</span></h2> 
        </div>
        
        <div class="content well well-white">
            
            <div class="finalize-vku-wrapper">
               <p>
                Nach dem Finalisieren können Sie keine Änderungen mehr an der Verkaufsunterlage vornehmen. Sie können die finalisierte Verkaufsunterlage anschließend in verschiedene Dateiformate exportieren.
               </p>
          
                <?php
                    if($ausgaben):
                        ?>
                       <div id="vku_ausgaben" class="well well-white">
                            <?php print $ausgaben; ?>
                       </div>    
                    <?php
                    endif;    
                ?>

                <div class="pull-right">
                    <button class="btn btn-primary btn-lg" id="preview-all">Vorschau</button>
                </div>

                <button class="btn btn-warning btn-lg" id="finalize-vku">Verkaufsunterlage finalisieren</button>
            </div>
            
            <div style="display: none;" class="download-wrapper">
                <p>Sie können Ihre Verkaufsunterlage jetzt herunterladen.</p>
                <h3 style="padding-left: 0;">Herunterladen als</h3>
                <div class="pull-right"><a href="#" class="btn btn-success vku-detail-link">Details anzeigen</a></div>
                <a class="btn btn-primary btn-pdf-download" href="#">PDF Datei (<span class="file-size"></span>)</a> 
                <a class="btn btn-primary btn-ppt-download">Powerpoint (<span class="file-size"></span>)</a>
            </div>
       </div>
    </div>    
</div>


<div class="remove-vku-link">
    <hr />
    <a href="<?php print url($vku -> removeUrl()); ?>" class="optindelete small pull-right" optintitle="Ja, Verkaufsunterlagen wirklich löschen" optin="Sind Sie sicher, dass Sie die Verkaufsunterlagen löschen möchten?">(Verkaufsunterlage verwerfen)</a>
</div>


<div class="generate-info generate-info-end">
    <div>
        <div>
            <div class="well">Bitte haben Sie etwas Geduld. Ihre Verkaufsunterlage wird gerade generiert. Das kann bis zu einer Minute dauern.</div>
        
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