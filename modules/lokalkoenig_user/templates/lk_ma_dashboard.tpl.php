
<div class="well">
<?php if(isset($links)) : ?>
    <div class="row clearfix">

<div class="col-xs-12">

<h4 style="margin-top: 0;">Quick-Links</h4>
</div>
<div class="col-xs-12">
   <ul class="list-inline" style="margin-bottom:0;">
     <li><?php print implode("</li><li>", $links); ?></li>      
   </ul>
</div>
</div>
   
   
   
   <?php else: ?>
   <div class="row clearfix">

<div class="col-xs-4">

<h4>Ihr persönliches Dashboard</h4>
</div>
<div class="col-xs-8">
Hallo <em><?php print $account -> name?></em>, <br />Herzlich willkommen zurück auf dem Lokalkönig. In ihrem persönlichen Dashboard haben Sie alles in der Übersicht.

</div>
</div>
   
<?php endif; ?>

</div>

<ul id="myTab" class="nav nav-tabs" role="tablist">

<?php $x = 0; while(list($key, $val) = each($list)){ 
  
  if(isset($val["link"])){
    ?>
    <li <?php if(isset($val["class"])) print 'class="'. $val["class"] .'"'; ?>><a href="<?php print url($val["link"]); ?>"><?php print $val["title"]; ?></a></li>
    <?php
    continue;
  }
  
  if(!isset($val["class"])) $val["class"] = '';
  if($x==0) $val["class"] .= ' active';


?>
      <li class="<?php print $val["class"];  ?>"><a href="#<?php print $key; ?>" role="tab" data-toggle="tab"><?php print $val["title"]; ?></a></li>

<?php $x++; } ?>
     
    </ul>
<div id="myTabContent" class="tab-content">    
<?php $x = 0; reset($list); while(list($key, $val) = each($list)){
  if(!$val["content"])  continue;

 ?>

<div class="tab-pane fade in <?php if($x == 0) print 'active'; ?>" id="<?php print $key; ?>">
    
    <?php print $val["content"]; ?>


</div>

<?php $x++; } ?>  
</div>

<script>
  // Javascript to enable link to tab
var hash = document.location.hash;
var prefix = "tab_";
if (hash) {
    jQuery('.nav-tabs a[href='+hash +']').tab('show');
} 


</script>