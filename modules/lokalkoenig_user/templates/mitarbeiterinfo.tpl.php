<?php
    
   if(!isset($short)){
       $short = false;
   }

   $accessed = \LK\get_user($account);
   $current = \LK\current();
   $verlag = $accessed -> getVerlagObject();
   
   $team = 0;
   $ausgaben = false;
 
   // Bereichinfo
   $vid = lk_get_verlag_from_user($account);
   $account = _lk_user($account);
   
   if($verlag){
     $verlag = _lk_user($vid);
     
     if($test = $accessed ->getCurrentAusgaben()){
       $ausgaben = $accessed ->getAusgabenFormatted();
     }
    
    $team = $accessed -> getTeamObject();
   }
   
   
   
   $picture = $accessed -> getPicture();
 ?>

<div class="well well-white clearfix">
    <?php if($picture): ?>
    <div class="pull-right"><?php print $picture; ?></div>
   <?php endif; ?>
   
    <?php if($team): ?>
    <h4 style="margin-top: 0;"><?php print l($team ->getTitle(), $team ->getUrl()); ?></h4>

  <p><strong>Verkaufsleiter:</strong></p>
  <p>
  <?php
    
    $vkl = $team -> getLeiter();
    if($vkl){
        $load = \LK\get_user($vkl);
        if($load){
            print (string)$load;
        }
    }    
  
   ?>
  </p>
   

 
 <?php if($short == false): ?>
   <p>&nbsp;</p>
  <hr class="clearfix" style="clear:both;" /> 
  
    
  
  <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right">Benutzer:</div>
      <div class="col-xs-8">
         <?php
         print l($account -> name, "user/" . $account -> uid);
        ?>
      </div>
  </div>
  <?php endif; ?>
  <?php 
  elseif($short == false):
    ?>  
   <h4 style="margin-top: 0;"><?php print $accessed-> name; ?></h4>
   <p><em><?php print ucfirst($accessed ->getRoleLong()); ?></em></p>
    
 <p>&nbsp;</p>
  <hr class="clearfix" style="clear:both;" /> 
  
  <?php   
  endif; 
  ?>
  
    <?php if($short == false): ?>
 
  <?php if($accessed ->isTestAccount()): ?>
     <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"></div>
      <div class="col-xs-8">
          <b class="label label-warning">Testaccount</b> (Eingeschränkte Funktionalität)
      </div>
  </div>
  <?php
    endif;
  ?>
 
  <?php if($ausgaben): ?>
  <div class="row clearfix" style="padding-bottom: 10px; clear:both;">
      <div class="col-xs-4 text-right">Ausgaben:</div>
      <div class="col-xs-8">
        <?php  
          print $ausgaben;
        ?>
      </div>
  </div>
  <?php endif; ?>
 
  <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"><span class="glyphicon glyphicon-phone-alt"></span></div>
      <div class="col-xs-8">
        <?php print $account->profile['main']->field_profile_telefon['und'][0]['value']; ?>
      </div>
  </div>
   
 
  <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"><span class="glyphicon glyphicon-envelope"></span></div>
      <div class="col-xs-8">
        <a href="mailto:<?php print $account -> mail; ?>"><?php print $account -> mail; ?></a> <?php if($account -> uid != $user -> uid AND $account -> status == 1) { ?>/ <a href="<?php print url("messages/new/" . $account -> uid); ?>">Nachricht über das Portal senden</a><?php } ?>
      </div>
  </div>
  
  
  
  <?php if(!$accessed -> isVerlag() AND $accessed ->getVerlag() AND $verlag AND $current -> hasRight("edit profile")) :?>
    <a class="btn btn-sm btn-success" href="<?php print url("user/" . $vid . "/struktur", array("query" => array("action" => "edit", "uid" => $account -> uid, "profile" => 1))); ?>"><span class="glyphicon glyphicon-pencil"></span> Account editieren</a> 
  <?php endif; ?>
  <?php endif; ?>
</div>