<?php
    global $user;
?>
<div class="well well-white">
    
    <?php if(lk_is_agentur()): ?>
        <p>Keine Kampagnen gefunden.</p>  
    <?php else: ?>
    
    <h4 style="margin-top: 0">Ihre Suche <?php if($terms) print ' nach <em>' . $terms . '</em>';  ?> hatte leider keine Ergebnisse.</h4>
    <p>Sie können sich per <strong>Alert</strong> über neue Kampagnen im Lokalkönig benachrichtigen lassen. Sie werden automatisch per E-Mail benachrichtigt, sobald für Ihren Suchbegriff eine neue Kampagne zur Verfügung steht. Sie können die Alerts jederzeit löschen.</p>
    
    <div>
        <a href="<?php print url("user/" . $user -> uid . "/alerts", array("query" => array("action" => 'add', "search_api_views_fulltext" => $terms, "result" => 0))); ?>" class="btn btn-primary"><span class="glyphicon glyphicon-flag"></span> Alert erstellen</a> 
        <a href="<?php print url("suche"); ?>" class="btn btn-primary pull-right">Zurück zur Suche</a>
    </div>
    <p>&nbsp;</p>
    
    <hr />
    
    <h4>Sie können gerne eine E-Mail an den Lokalkönig schreiben.</h4>
    <div class="panel-remove">
        <?php print drupal_render($form); ?>
    </div>
    <?php endif; ?>
</div>


  


