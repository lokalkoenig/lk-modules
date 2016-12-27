<?php

// Gets Ausgaben-Object

$title = $account -> getTitle();
$plz = $account -> getPlzFormatted();

?>
<div class="well well-white clearfix">
    <span class="glyphicon glyphicon-check pull-right"></span>
    <p><strong><?php print $title; ?></strong></p>
    <p><strong>PLZ-Bereich:</strong><?php print $plz; ?></p>
</div>