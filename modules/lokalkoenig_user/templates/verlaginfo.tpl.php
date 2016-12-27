<?php
 // $vid - Verlagsuser-id
   $account = _lk_user($vid);
   global $user;
   
   if(isset($account->profile['verlag']->field_verlag_logo['und'][0]['uri'])){
      $logo = $account->profile['verlag']->field_verlag_logo['und'][0]['uri'];
      $logo = theme('image_style', array('style_name' => "verlags-logos-klein", "path" => $logo));
   }
   
   $leiter = $account;
?>

<div class="well clearfix">
  <?php
      if(isset($logo)) print '<div class="thumbnail pull-right" style="padding: 20px; border: 1px Silver solid;">' .$logo . '</div>';
  ?>

  <h4><?php print $account->profile['verlag']->field_verlag_name['und'][0]['value']; ?></h4>
  
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
  
  
   
 <?php if(isset($account->profile['main']->field_profile_telefon['und'][0]['value'])): ?>
  <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"><span class="glyphicon glyphicon-phone-alt"></span></div>
      <div class="col-xs-8">
        <?php print $account->profile['main']->field_profile_telefon['und'][0]['value']; ?>
      </div>
  </div>
  <?php endif; ?>
   
 
  <div class="row clearfix" style="padding-bottom: 10px;">
      <div class="col-xs-4 text-right"><span class="glyphicon glyphicon-envelope"></span></div>
      <div class="col-xs-8">
        <a href="mailto:<?php print $account -> mail; ?>"><?php print $account -> mail; ?></a> <?php if($account -> uid != $user -> uid AND $account -> status == 1) { ?>/ <a href="<?php print url("messages/new/" . $account -> uid); ?>">Nachricht über das Portal senden</a><?php } ?>
      </div>
  </div>
</div>