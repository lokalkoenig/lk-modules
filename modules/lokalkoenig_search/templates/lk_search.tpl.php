<?php
global $user;

if((isset($_GET["f"]) OR (isset($_GET["search_api_views_fulltext"]) AND $_GET["search_api_views_fulltext"]))) :
  $search = array();
  if(isset($_GET["f"])){
     $search['f'] = $_GET["f"]; 
  }
  if(isset($_GET["search_api_views_fulltext"]) AND !empty($_GET["search_api_views_fulltext"])
    AND strlen($_GET["search_api_views_fulltext"]) > 3
  ){
      $search['search_api_views_fulltext'] = $_GET["search_api_views_fulltext"]; 
  }
  
  if($search AND isset($_GET["sort_by"]) AND !empty($_GET["sort_by"])){
      $search['sort_by'] = $_GET["sort_by"]; 
  }
  
if(!$search){
    // We need this!
    return ;
}
?>

<div class="action-bar well well-white">
  <div class="row clearfix">
    <div class="col-xs-3 text-center"><span class="label label-success">NEU</span></div>
    <div class="col-xs-3">
      <a href="<?php print url('user/'. $user -> uid .'/alerts', array("query" => $search + array('action' => 'add'))); ?>" data-toggle="tooltip" data-placement="top" title="Sie bekommen bei neuen Kampagnen zu dieser Suche eine Nachricht"><span class="glyphicon glyphicon-flag"></span> <strong>Alert erstellen</strong>
      </a>
    </div>
    <div class="col-xs-3"><a href="<?php print url('messages/new', array("query" => $search)); ?>" data-toggle="tooltip" data-placement="top" title="Versenden Sie diese Suchergebnisseite an Ihre Mitarbeiter"><span class="glyphicon glyphicon-envelope"></span> <strong>Ergebnisseite versenden</strong>
    </a></div>
    <div class="col-xs-3"><a href="<?php print url("suchanfrage", array("query" => drupal_get_destination())); ?>" data-toggle="tooltip" data-placement="top" title="Sie haben eine spezielle Suchanfrage an den Lokalkönig"><span class="glyphicon glyphicon-question-sign"></span> <strong>Suchanfrage</strong>
    </a></div>
  </div>  
</div>


<style>
  .action-bar {
      padding: 10px 0px;  
      text-align: center;
  }
  
  .action-bar a {
    display: block;
  }
  

</style>

<?php endif; ?>


<?php
 
  
 
  
 $base = arg(0); 
 $gets = $_GET;
 $gets2 = $gets;
 if(isset($gets["f"])){
   $cop = array();
   
   foreach($gets["f"] as $test){
      $explode = explode(":", $test);
      if($explode[0] != 'field_kamp_medientypen'){
         $cop[] = $test;
      }
   }
   
   $gets2["f"] = $cop;
 }
 
 unset($gets["q"]);
 if(isset($gets["sort_by"])){
  unset($gets["sort_by"]);
 }
 
 $stdsort = 'search_api_relevance';
 
 
  if(isset($_GET["sort_by"])){
    if(in_array($_GET["sort_by"], array("created", "field_kamp_beliebtheit"))){
      $stdsort = $_GET["sort_by"];  
    }
  }
 
 
 

?>

<div class="search_tabs">
<div class="label_auswahl" style="float: right; display: none;">
   <strong>Auswahl</strong>

</div>


<ul class="clearfix">
  <li<?php if($stdsort == 'search_api_relevance') print ' class="active"';  ?>><a href="<?php print url($base, array("query" => $gets + array("sort_by" => "search_api_relevance"))); ?>">Relevanz</a></li>
  <li<?php if($stdsort == 'created') print ' class="active"';  ?>><a href="<?php print url($base, array("query" => $gets + array("sort_by" => "created"))); ?>">Neueste</a></li>
  <li<?php if($stdsort == 'field_kamp_beliebtheit') print ' class="active"';  ?>><a href="<?php print url($base, array("query" => $gets + array("sort_by" => "field_kamp_beliebtheit"))); ?>">Beliebt</a></li>
  
  <?php
    if(isset($gets["page"])){
        unset($gets["page"]);
    }
  ?>
  
  
  <li class="pull-right<?php if(arg(0) == 'suche-grid') print ' active'; ?>"><a data-toggle="tooltip" title="Bilderansicht" href="<?php print url('suche-grid', array("query" => $gets)); ?>"><span class="glyphicon glyphicon-th"></span></a></li>
  <li class="pull-right<?php if(arg(0) == 'suche') print ' active'; ?>"><a data-toggle="tooltip" title="Listenansicht" href="<?php print url('suche', array("query" => $gets)); ?>"><span class="glyphicon glyphicon-list"></span></a></li>
  
 
  
</ul>
</div>



<style>
 .search_tabs {
    border-bottom: 2px #d7dcde solid;
    height: 44px;
 }

 .search_tabs ul {
    margin: 0;
    padding: 0;
    list-style: none;
 
 }
 
 .search_tabs ul li {
  float: left;
 }
 
 .search_tabs ul li a {
    display: block;
    font-weight: bold;
    padding: 10px 30px ;
 }

 .search_tabs ul li.active a {
   border: 2px #d7dcde solid;
   border-top-right-radius: 4px;
   border-top-left-radius: 4px;
   border-bottom-color: white;
   background: white;
   color: #f7a800;
  } 

.region-search-top .block-facetapi {
  float: right;
  position: relative;
  z-index: 5;
  height: 20px;
}


.region-search-top .block-facetapi ul li .badge {
  display: none;
}

.region-search-top .block-facetapi ul li {
  float: left;
}

.region-search-top .block-facetapi ul li a {
  background-image: url(/sites/all/themes/bootstrap_lk/design/icon-off.png);
  background-repeat: no-repeat;
  background-position: left center;
  padding-left: 20px;
  font-weight: bold;
  color: #526476;
  border: none;
}

.region-search-top .block-facetapi ul li a:hover,
.region-search-top .block-facetapi ul li a.facetapi-active {
  background-image: url(/sites/all/themes/bootstrap_lk/design/icon-on.png);
  box-shadow: none;
  border: none;
  background-color: White;
}



</style>
