<?php

// Default Value
$search_value = '';
if(isset($_GET["search_api_views_fulltext"])) {
  $search_value = $_GET["search_api_views_fulltext"];
}

?>


<!--SUCHE -->
<div class="btn-group" style="margin-right: 10px;">
  <form method="GET" id="kampasearch" action="<?php print url('suche'); ?>" class="formhover">
    <input id="searchbegin" title="" title="Geben Sie hier Suchbegriffe ein, wie z.B. Branchenbezeichnungen Bäcker, Fleischer, Auto, ..." value="<?= $search_value; ?>" type="search" class="form-control form-text lk-autocomplete lk-autocomplete-search" placeholder="Kampagnensuche" name="search_api_views_fulltext" style="display:inline; margin-right: -10px;" />
    <a href="<?php print url('suche'); ?>" onclick="jQuery('#kampasearch').submit(); return false;" class="btn btn-primary" title="Kampagnen-Übersicht" style="border-top-left-radius: 0; border-bottom-left-radius: 0; height: 34px;"><span class="glyphicon glyphicon-search"></span></a>

    <div class="showtext tooltip-inner">
      <button type="button" class="close pull-right" data-dismiss="modal" onclick="closeSearchHelp()"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>

      <div class="search-results">
        <h4>Suchvorschläge <small>Sortiert nach Ergebnissen</small></h4>
        <ul class="search-results-items"></ul>
      </div>

       <div class="row clearfix search-info-text">
        <div class="col-xs-2 text-center"><span class="glyphicon glyphicon-info-sign"></span></div>
        <div class="col-xs-10">
          <p>Geben Sie hier Suchbegriffe ein, wie z.B. Branchenbezeichnungen Bäcker, Fleischer, Auto, ...</p>
          <p>Oder nutzen Sie die <strong>Stichwort Suche</strong> womit Sie gezielt in Branchen suchen können:</p>
          <ul class="list-inline">
            <li><a class="btn btn-primary" href="<?php print url("user/" . $user -> uid . "/searches"); ?>"><span class="glyphicon glyphicon-user"></span> Ihre persönliche Suchhistorie</a></li>
          </ul>
        </div>
     </div>
    </div>
  </form>
</div>