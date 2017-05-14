<?php

global $user;

$current = \LK\current();
$accessed = \LK\get_user($account -> uid);
$isuser = false;

if($current == $accessed){
  $isuser = true;
}

if($accessed -> isVerlag() OR $accessed ->isVerlagController()): 
  if(!$test = $accessed -> getVerlag()){
    print 'Fehlerhafter User';
    return ;
  }
endif;

if($account -> uid == 0){
  if($user -> uid){
    ?>
    <div class="panel panel-default">
       <?php
        print lklink('Zurück', 'user/' . $user -> uid . '/dashboard','chevron-right');
        ?>
     </div>
    <?php
  }
  
  return ;
}
  

// Show Userpicture
if($verlag = $accessed -> getVerlag()):
  $obj = \LK\get_user($verlag);
?>
<div class="well well-white text-center">
    <?php print $obj ->getPicture(); ?>
    <p class="block-title"><strong><?php print (string)$accessed; ?></strong></p> 
    
    <?php 
     if($current ->isModerator() AND $accessed != $current):
     ?>   
      <div class="help-block">Sie befinden sich in der Profil-Ansicht eines anderen Nutzers.</div>

      <?php if(!$accessed->isVerlag()): print $obj; endif; ?>

      <?php
    endif;
    
    ?>
    
</div>
<?php 



else :
    $support = \LK\get_user(11);
    ?>
    <div class="well well-white text-center">
        <?php print $support ->getPicture(); ?>
        <p class="block-title"><strong><?php print (string)$accessed; ?></strong></p> 
    </div>
<?php
endif;
?>

<div class="panel panel-default">
  <?php 

  $links = [];
  $links[] = ['title' => '&nbsp;Dashboard', 'icon' => 'home', 'url' => 'user/' . $account -> uid . '/dashboard'];
    if(!$accessed -> isAgentur()):

      // Neues Feature
      //if($accessed->isLKTestverlag() && $accessed ->isTeamleiter()) {
      //  $links[] = ['title' => 'VKU Vorlagen', 'icon' => 'file', 'url' => 'user/' . $account -> uid . '/vku_team_editor'];
      //}

      $vkuscount = \LK\VKU\VKUManager::getNotfinalCount($account -> uid);
      $links[] = ['title' => 'Verkaufsunterlagen', 'count' => $vkuscount, 'icon' => 'lock', 'url' => 'user/' . $account -> uid . '/vku'];

      $lizenz_count = \LK\VKU\VKUManager::getLizenzActiveCount($account->uid);
      $links[] = ['title' => 'Lizenzen', 'count' => $lizenz_count, 'icon' => 'cloud-download', 'url' => 'user/' . $account -> uid . '/lizenzen'];

      $links[] = ['title' => 'Suchhistorie', 'icon' => 'search', 'url' => 'user/' . $account -> uid . '/searches'];
      $links[] = ['title' => 'Statistiken', 'icon' => 'stats', 'url' => 'user/' . $account -> uid . '/stats'];
    else:
      $links[] = ['title' => 'Abrechnung', 'icon' => 'euro', 'url' => 'user/' . $account -> uid . '/agenturabrechnung'];
    endif;



    if($accessed ->isMitarbeiter()){
      $team = $accessed -> getTeam();
      $links[] = ['title' => 'Ihr Team', 'icon' => 'comment', 'url' => 'team/' . $team];
    }
    
    if($isuser):
     // Private Nachrichten
     $count = privatemsg_unread_count();
     $links[] = ['title' => 'Nachrichten', 'icon' => 'envelope','count' => $count, 'url' => 'messages'];
    endif;

    // go through the links
    foreach($links as $link) {

      if(isset($link['count']) && $link['count']) {
        print lklink('<span class="badge pull-right">'. $link['count'] .'</span>' . $link['title'], $link['url'], $link['icon']);
        continue;
      }

      print lklink($link['title'], $link['url'], $link['icon']);
   }

    
   ?> 
</div> 

<?php if($accessed -> isVerlag() OR $accessed ->isVerlagController()): ?>    

 <div class="btn-group" style="display: block;">
  <button type="button" class="btn btn-success dropdown-toggle" style="white-space: normal; float: none; width: 100%;" data-toggle="dropdown">
    <span class="glyphicon glyphicon-comment"></span> Teams <span class="caret"></span>
  </button>
    
  <ul class="dropdown-menu" role="menu">
      <?php 
        $verlag = $accessed -> getVerlag();
        $obj = \LK\get_user($verlag);

        $teams = $obj -> getTeams();
        foreach($teams as $team_obj){
            print '<li>'. l($team_obj -> getTitle(), $team_obj -> getUrl()) .'</li>';
        }
      ?>
  </ul>
 </div>
<hr />
<?php endif; ?>
 
<?php if($accessed -> isVerlag() OR $accessed ->isVerlagController()): ?>    
    
<div class="panel panel-default">
  <div class="panel-heading">Verlag</div>

  <?php
    $links = lokalkoenig_user_profile_links_verlag($accessed);
    foreach($links as $link){
       print lklink($link["title"], $link["link"], $link["icon"]);
    }
  ?>
</div>

<?php  endif;  ?>

<?php if(!$isuser): ?>
    <p><a href="<?php print url($current -> getUrl()); ?>" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-chevron-right"></span> Zurück zu Ihrem Profil</a> 
<?php  endif; ?>
