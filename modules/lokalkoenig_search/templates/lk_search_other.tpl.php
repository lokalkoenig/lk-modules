<hr />

<div class="well clearfix">
<h3><div class="pull-right"><?php print $total_items; ?> Ergebnisse</div>Das könnte Sie auch interessieren</h3>
<div class="current-search-item-active no-delete clearfix">
  <?php print theme('item_list', array("items" => $tags_display)); ?>
</div>
</div>
<hr />

<?php
  print $viewsout;
?>


<h4 class="center" style="text-align: center; text-decoration: underline;">
<?php
  if(isset($url["q"])) unset($url["q"]);
  print l('Alle verwandten Artikel anzeigen', 'suche', array("query" => $url));
?>
</h4>

