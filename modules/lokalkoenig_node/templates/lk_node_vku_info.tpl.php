 <div class="row">
  <div class="col-xs-4 text-right"><strong>Die Kampagne wurde bis zum <?php print date('d.m.Y', $info["info"]["until"]) ?> für folgende Ausgaben für Sie reserviert:</strong></div>
     <div class="col-xs-4">
        <?php print implode(' ', $info["info"]["ausgaben"]); ?>
        <p>&nbsp;</p>
    </div>
    <div class="col-xs-4 text-right">
         <a class="btn btn-primary" href="<?php print url($info["info"]["url"]); ?>"><span class="glyphicon glyphicon-chevron-right"></span> Zu Ihrer Verkaufsunterlage</a>  
    </div>    
</div>