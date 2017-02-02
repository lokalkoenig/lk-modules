
<div class="btn-group">  

<?php
  $account_obj = \LK\get_user($account);
  $uid = $account_obj ->getUid();
   
  $search_value = '';
  if(isset($_GET["search_api_views_fulltext"])) {
      $search_value = $_GET["search_api_views_fulltext"];
  } 
  
  if($account_obj ->isAgentur()):
      ?>
        <a href="<?php print url("suche"); ?>" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Kampagnenindex</a>  
      <?php
  else:
?>
        
    
        
<!--SUCHE -->
<div class="btn-group" style="margin-right: 10px;">
<form method="GET" id="kampasearch" action="<?php print url('suche'); ?>" class="formhover">
  <input id="searchbegin" title="" title="Geben Sie hier Suchbegriffe ein, wie z.B. Branchenbezeichnungen Bäcker, Fleischer, Auto, ..." value="<?php print $search_value; ?>" type="search" class="form-control form-text lk-autocomplete lk-autocomplete-search" placeholder="Kampagnensuche" name="search_api_views_fulltext" style="display:inline; margin-right: -10px;" />
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

<?php endif; ?>

<!--VKU NEU -->
<?php if($vku_menu): ?>
    <?php print $vku_menu; ?>
<?php endif; ?>    


<?php
    

    // Show only to Agentur  
  if($account_obj -> isAgentur()): 
?>
<!-- Kampagnen -->
<div class="btn-group">  
   <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
    <span class="glyphicon glyphicon-lock"></span> Kampagnen <span class="caret"></span>
  </button>
  
   <ul class="dropdown-menu" role="menu">
     <li><a href="<?php print url("user/". $account -> uid ."/kampagnen"); ?>">Meine Kampagnen</a></li>  
     <li><a href="<?php print url("node/add/kampagne"); ?>">Neue Kampagne anlegen</a></li>
    
  </ul>
</div> 

<?php
  endif;
?>

<?php
  // Add Dashboard Links there
  $links = _lokalkoenig_user_dashboard_links($account);
?>

<div class="btn-group">
  <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
    <span class="glyphicon glyphicon-user"></span> Ihr Account <span class="caret"></span>
  </button>
  
  <ul class="dropdown-menu" role="menu">
    <?php foreach ($links as $link) :?>  
        <?php if(isset($link["divider"])) :?>
             <li class="divider"></li>
        <?php else: ?>
            <li><a href="<?php print url($link["link"]); ?>"><?php print $link["title"]; ?></a></li>
       <?php endif; ?>
    <?php endforeach; ?>  
  </ul>
</div>

<?php if($account -> count_msg_new AND arg(0) != 'messages'): ?>
      <a href="/messages" class="btn btn-warning" data-toggle="tooltip" title="" data-original-title="Neue Nachrichten (<?php print $account -> count_msg_new; ?>)" data-placement="bottom"><span class="glyphicon glyphicon-envelope"></span></a>
<?php endif; ?> 
 
      
<?php if($account_obj ->isTelefonmitarbeiter() AND arg(2) != 'setplz'): 
    
    $titles = array();
    $ausgaben = $account_obj ->getCurrentAusgaben();
    if(count($ausgaben) == 0):
        $titles[] = '<em>Keine</em>';
        $ausgaben_title = 'Keine';
    else:
        $titles = array();
        foreach($ausgaben as $aus){
            $obj = \LK\get_ausgabe($aus);
            $titles[] = $obj ->getShortTitle();
        }
        
        if(count($ausgaben) == 1):
            $ausgaben_title = '1 Ausgabe';
        else:
            $ausgaben_title = count($ausgaben) .' Ausgaben'; 
        endif;
        
        
        
    endif;
    
    ?>
   
      <a href="<?php print url('user/'. $uid .'/setplz'); ?>" class="btn btn-warning" data-toggle="tooltip" title="" data-html="true" data-original-title="Wählen Sie Ihre Ausgaben aus. <br />Momentane Auswahl: <?php print implode(", ", $titles); ?>" data-placement="bottom"><span class="glyphicon glyphicon-globe"></span> <small style="font-size: 0.8em"><?php print $ausgaben_title; ?></small></a>

    <?php
    
endif; ?>      

   <a href="/user/logout" class="btn btn-warning" title="Abmelden"><span class="glyphicon glyphicon-eject"></span></a>
</div>


<div class="feedback-form" style="display:none">
   <?php print render($form); ?>
</div>
<?php if(lk_vku_access()) : ?>
  <div onclick="lk_add_js_modal_optin('Feedback', jQuery('.feedback-form').html(), '', '')" class="feedback-trigger" style="position: fixed; cursor: pointer; padding: 5px 10px; bottom: 0; right: 0; width: auto; background: #34495e; color: White; z-index: 200; "><span class="glyphicon glyphicon-envelope"></span> Feedback senden</div>
<?php endif; ?>