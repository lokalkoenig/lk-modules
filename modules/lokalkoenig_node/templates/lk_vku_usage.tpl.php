
<div class="row">
   <div class="col-xs-1 text-center">
       <span class="glyphicon glyphicon-big glyphicon-exclamation-sign"></span>
   </div>
   
   <div class="col-xs-11">
      <strong>Verwendung dieser Kampagne in Verkaufsunterlagen Ihres Verlages</strong>
      <ul>
      <?php foreach($entries as $e) :?>
         <li>
             Am <?php print format_date($e -> date_added, 'short'); ?> verwendet von 
             <?php
              if($account -> uid == $e -> uid){
                print 'Ihnen in <strong>' . l("Verkaufsunterlage: " . $e -> vku_title, "user/" . $account -> uid . "/vku/" . $e -> vku_id . "/details") . '</strong>';
              }
              else {
                print _format_user($e -> uid);

                if($e -> ausgaben){
                  print ' für folgende Ausgaben ' . implode(" ", $e -> ausgaben);
                }
                else {
                  print ' in folgenden Ausgaben ' . \LK\user_ausgaben_f($e -> uid);  //implode(" ", $e -> ausgaben); 
                }
              }
             ?>
         </li>
      <?php endforeach; ?>
      </ul>
   </div>
</div>
