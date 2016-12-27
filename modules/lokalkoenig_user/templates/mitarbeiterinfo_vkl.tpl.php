<?php
global $user;

//$account = \LK\get_user($account);
$team = $account ->getTeamObject();

if(!$team):
    return '';
endif;

$verlag = $account -> getVerlagObject();
$picture = $account -> getPicture();

?>

<div class="well well-white clearfix">
    <?php if($picture): ?>
    <div class="pull-right"><?php print $picture; ?></div>
   <?php endif; ?>
   
    <?php if($team): ?>
    <h4 style="margin-top: 0;"><?php print l($team ->getTitle(), $team ->getUrl()); ?></h4>
    <?php endif; ?>
    
  <p><strong>Verkaufsleiter:</strong></p>
  <p>
  <?php
      print (string)$account;
   ?>
  </p>
  
  <p>
      <span class="glyphicon glyphicon-phone-alt"></span>&nbsp;&nbsp; <?php print $account->profile['main']->field_profile_telefon['und'][0]['value']; ?>
  </p>
  <p>
      <span class="glyphicon glyphicon-envelope"></span>&nbsp;&nbsp;      <a href="mailto:<?php print $account -> mail; ?>"><?php print $account -> mail; ?></a> 
            <?php if($account ->getUid() != $user -> uid AND $account ->getStatus()): ?>/ <a href="<?php print url("messages/new/" . $account ->getUid()); ?>">Nachricht über das Portal senden</a><?php endif; ?>
  </p>
  
</div>