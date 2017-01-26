
<div class="well well-white" id="vku-editor-verlag">
  <h4>Vorlagen für Verkaufsunterlagen</h4>
  <p>Hier können Sie Vorlagen anlegen und verwalten.<br />Nach der Veröffentlichung stehen diese Dokumente Ihren Mitarbeitern zur weiteren Bearbeitung zur Verfügung.</p>

  <div class="btn-group">
     <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
       <span class="glyphicon glyphicon-plus"></span> Dokument erstellen <span class="caret"></span>
     </button>

     <ul class="dropdown-menu" role="menu">
       <?php while(list($preset, $info) = each($presets)): ?>
         <li>
           <a href="#" class="btn-document-create" data-preset="<?= $preset ?>">
             <?= $info['title']; ?>
           </a>
         </li>
       <?php endwhile; ?>
     </ul>
   </div>
   <hr />
  <div id="vku-editor-verlag-documents">
    <?php print $documents; ?>
  </div>
</div>    
