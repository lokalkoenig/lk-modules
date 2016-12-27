
<div class="suche-in-msg well well-white clearfix">
    <h3 style="margin-top: 0;">Suchergebnisse <small class="pull-right"><a href="<?php print $link; ?>">
    Suche anzeigen <span class="glyphicon glyphicon-chevron-right"></span></a></small></h3>
    <span class="label label-primary pull-right"><?php print $count; ?> Ergebnisse</span>
    <?php print $title; ?> 
    <hr>
 
    <div class="clearfix view-content">
        <?php print implode('', $nodes); ?>
    </div>    
</div>    
