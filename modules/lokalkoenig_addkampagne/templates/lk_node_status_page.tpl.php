<?php

if($node -> status == 0 AND $node -> lkstatus == 'deleted'){
?>
 <div class="well">
      Die Kampagne wurde als gelöscht markiert und verworfen.
    </div>
<?php  
}


if($node -> status == 0 AND $node -> lkstatus == 'proof'){
 ?> 
  <div class="well">Ihre Kampagne wird gerade administrativ geprüft.</div>  
 <?php
}
elseif($node -> lkstatus == 'canceled'){
 ?> 
  <p class="alert alert-danger">Ihre Kampagne wurde administrativ abgelehnt. Sie haben die Möglichkeit die Kampagne auf Antrag wieder für die Editierung freischalten zu lassen.</p>  
 <?php
 
 print drupal_render(node_view(node_load(12)));
 
 return ;
}
elseif($node -> status == 1){
 ?> 
  <p class="alert alert-success">Diese Kampagne ist Online.</p> 
 <?php  
}

return ;
?>

<h3>Vorschau Übersicht</h3>

<hr />

<?php 

$node_copy = clone $node;
$node_copy -> vmode = 'teaser';

print drupal_render(node_view($node_copy, "teaser")); ?>

