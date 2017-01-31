<?php

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


global $user;
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
    print lklink('&nbsp;Dashboard', 'user/' . $account -> uid . '/dashboard','home'); 
    
    if(!$accessed -> isAgentur()):
      $vkuscount = \LK\VKU\VKUManager::getNotfinalCount($account -> uid);
      
      if($vkuscount):
        print lklink('<span class="badge pull-right">'. $vkuscount .'</span>Verkaufsunterlagen', 'user/' . $account -> uid . '/vku','cloud-download');
      else:
        print lklink('Verkaufsunterlagen', 'user/' . $account -> uid . '/vku','cloud-download');
      endif;
        
      print lklink('Suchhistorie', 'user/' . $account -> uid . '/searches','search');
    else:
          print lklink('Abrechnung', 'user/' . $account -> uid . '/agenturabrechnung','euro');
    endif;
    
    if($accessed ->isMitarbeiter()){
        $team = $accessed -> getTeam();
        print lklink('Ihr Team', 'team/' . $team, 'comment');
    }
    
    
    if($isuser):
     // Private Nachrichten
     $count = privatemsg_unread_count();
     if($count == 0):
        print lklink('Nachrichten', 'messages','envelope'); 
     else:
        print lklink('<span class="badge pull-right">'. $count .'</span>Nachrichten', 'messages','envelope'); 
     endif;
    endif;
    
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

<style>
  .block-lokalkoenig-user .list-group-item {
    border-left: 0;
    border-right: 0;
  }
  
  .region-sidebar-first .panel-heading {
          background-color: rgba(66,139,202,0.5) !important;
          text-transform: uppercase;
          color: White;
   }
  </style>
  
  <?php return ; ?>

  <div class="panel panel-default">
  <!-- List group -->
  <div class="panel-heading">Ihr Account</div>
  <?php 
    
  if($isuser OR $current -> isModerator()):
      print lklink('Profil ansehen', 'user/' . $account -> uid,'user'); 
      print lklink('Profildaten bearbeiten', 'user/' . $account -> uid . '/edit/main','edit'); 
        
      if($isuser){
        print lklink('Passwort ändern', 'user/' . $account -> uid . '/edit','wrench'); 
      }  
      
   endif;     
    
  if($current -> isTelefonMitarbeiter() AND $accessed ->isTelefonMitarbeiter()):
         print lklink('Einsatzbereiche', 'user/' . $account -> uid . '/setplz','globe');
  endif;
  
  if(!$accessed ->isAgentur()):
    print lklink('Statistiken', 'user/' . $account -> uid . '/stats','stats'); 
  endif;
  ?>
  
  <?php if($user -> uid == $account -> uid) print lklink('Abmelden', 'user/logout','eject'); ?>
  <?php if($user -> uid != $account -> uid AND $account -> status == 1 AND $account -> uid != 11) print lklink('Nachricht senden', 'messages/new/' . $account -> uid,'envelope'); ?>
</div>