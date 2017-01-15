<hr />

<div class="well clearfix">
  <h3>
    <span class="pull-right">~<?= $total_items; ?> Ergebnisse</span>
    Das könnte Sie auch interessieren
  </h3>
  <div class="current-search-item-active current-search-item-releated no-delete clearfix">
    <?php print theme('item_list', array("items" => $tags_display)); ?>
  </div>
</div>
<hr />

<?= $viewsout; ?>

<h4 class="center" style="text-align: center; text-decoration: underline;">
  <a href="<?= $url; ?>">Alle verwandten Artikel anzeigen</a>
</h4>

