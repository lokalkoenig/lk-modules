<?php
  global $user;
  $count = _lk_get_recomend_count($user);
  $count_history = _lk_get_history_count($user);
  $countvku = vku_get_active_id();
?>

<div class="tabstyle">
  <?php if($count) { ?>
     <span class="pull-right"><a <?php if(isset($_GET["sticky"])) print 'class="active" '; ?>data-toggle="tooltip" data-placement="top" title="Kampagnen die von der Redaktion als empfehlenswert eingestuft sind" href="<?php print url('suche', array("query" =>array("sticky" => 1))); ?>">Redakt. Empfehlungen (<?php print $count; ?>)</a></span>   
  <?php } ?>
  
  <span id="merklistenav"><a <?php if(arg(0) == 'merkliste') print 'class="active" '; ?> data-toggle="tooltip" data-placement="top" title="Verwalten Sie hier Ihre Kampagnen, die sich auf Ihrer Merkliste befinden." href="<?php print url(MERKLISTE_URI); ?>">Merklisten (<span id="mlcount"><?php print $count_ml; ?></span>)</a></span>
  
  <?php if($count_history) { ?>
    <span><a <?php if(arg(0) == 'history') print 'class="active" '; ?> data-toggle="tooltip" data-placement="top" title="Sie können hier die zuletzt angeschauten Kampagnen noch einmal finden." href="<?php print url('history'); ?>">Zuletzt angesehen (<?php print $count_history; ?>)</a></span>
  <?php } ?>
  
  <?php if(arg(0) != 'vku' AND !vku_is_update_user()) { ?>
    <span style="background: #357ebd; <?php if($countvku == 0) print ' display: none;'?>" id="vku_cart"><a style="color: White;" onclick="return generateCurrentVKU();" data-toggle="tooltip" data-placement="top" title="Ihre Verkaufsunterlagen" href="<?php print url('vku'); ?>" class="active"><small class="glyphicon glyphicon-shopping-cart"></small> Verkaufsunterlagen </a></span>
  <?php } ?>
</div>

<div class="hidden" id="merklistecontent">
  <?php print theme('lk_merkliste_terms', array("select" => $GLOBALS["merkliste_entries"])); ?>
</div>