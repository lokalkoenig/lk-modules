<?php
   $accessed = \LK\get_user($account);
   $current = \LK\current();
   $verlag = $accessed -> getVerlagObject();
   $short = false;
   $team = 0;
   $ausgaben = false;

   if($verlag){
    $ausgaben = $accessed ->getAusgabenFormatted();
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
  <?php endif; ?>
   
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
 
  <?php if($accessed ->isTestAccount()): ?>
     <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"></div>
      <div class="col-xs-8">
          <b class="label label-warning">Testaccount</b> (Eingeschränkte Funktionalität)
      </div>
  </div>
  <?php endif; ?>
  
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

  <?php if(isset($accessed->profile['main']->field_profile_telefon['und'][0]['value'])): ?>
    <div class="row clearfix" style="padding-bottom: 10px;">
        <div class="col-xs-4 text-right"><span class="glyphicon glyphicon-phone-alt"></span></div>
        <div class="col-xs-8">
          <?php print $accessed->profile['main']->field_profile_telefon['und'][0]['value']; ?>
        </div>
    </div>
  <?php endif; ?>
 
  <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"><span class="glyphicon glyphicon-envelope"></span></div>
      <div class="col-xs-8">
        <a href="mailto:<?php print $account -> mail; ?>"><?php print $account -> mail; ?></a> <?php if($account -> uid != $user -> uid AND $account -> status == 1) { ?>/ <a href="<?php print url("messages/new/" . $account -> uid); ?>">Nachricht über das Portal senden</a><?php } ?>
      </div>
  </div>
  
  
  
  <?php if($accessed ->getVerlag() AND $verlag AND $current -> hasRight("edit profile") && $accessed != $current) :?>
  <a class="btn btn-sm btn-success" href="<?php print url("user/" . $verlag ->getUid() . "/struktur", array("query" => array("action" => "edit", "uid" => $account -> uid, "profile" => 1))); ?>"><span class="glyphicon glyphicon-pencil"></span> Account editieren</a>
  <?php endif; ?>
</div>