<?php
    $current = \LK\current();
    $uid = $verlag -> getUid();
?>

<div class="panel panel-default panel-info">
  <div class="panel-body">
   <!--<div class="well well-white">Die verfügbaren Postleitzahlen der Ausgaben werden aus dem globale Postleitzahlenbereich des Verlages gezogen. Sollten die Postleitzahlen verändert werden, dann werden auch die Postleitzahlen der Mitarbeiter verädnert.</div>
       --> 
   <?php if($current -> hasRight('add ausgabe')): ?>    
        <a href="<?php print url('user/' . $uid . '/ausgaben', array("query" => array('action' => "new"))); ?>" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Neue Ausgabe anlegen</a>
         <hr />
   <?php endif; ?>
  
  <div class="well well-white">
      <p><strong>PLZ im Verlag</strong></p>
      <p><?php print $verlag -> getPlzSimplyfied(); ?> - <a href="javascript:void(0);" onclick="jQuery('.plz-show-all').toggle('slow');">Alle anzeigen</a></p>
      
      <div class="plz-show-all" style="display: none;">
          <hr />
          <small><?php print $verlag -> getPlzFormatted(); ?></small>
      </div>    
  </div>   
         

  <table class="views-table table table-striped table-hover">
         <thead>
      <tr>
        <th class="views-field views-field-php-1">Name</th>
        <th class="views-field views-field-access">PLZ</th>
        <th class="views-field views-field-access">Mitarbeiter</th>
            <?php if($current -> hasRight('edit ausgabe')): ?>
                  <th class="views-field views-field-php">Aktion</th>
              <?php endif; ?>    
              </tr>
    </thead>
    <tbody>
          <?php
          
          if(!$bereiche){
          
          ?>
          <tr><td><p>Sie haben bisher keine Bereiche angelegt.</p></td></tr>
          <?php
          
          }
          
          
  foreach($bereiche as $bereich){
    ?>                                        
      
      <tr>
        <td><?php print $bereich -> getTitle(); ?></td>
        <td><?php print $bereich -> getPlzFormatted(); ?></td>
        <td><?php print $bereich -> getUserCount(); ?> 
        </td>
          <?php if($current -> hasRight('edit ausgabe')): ?>
            <td><a href="<?php print url('user/' . $uid . '/ausgaben', array("query" => array('action' => "editausgabe", 'ausgabe' => $bereich -> getId()))); ?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-pencil"></span> Editieren</a></td>
         <?php endif; ?>
      </tr>       
    <?php
  }
  ?>
      </tbody>
</table>

</div>
</div>
