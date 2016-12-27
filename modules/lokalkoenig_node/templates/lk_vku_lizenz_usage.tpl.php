
   <div class="row">
       <div class="col-xs-4 text-right"><strong>Die Kampagne wurde bereits für folgende <br />Posteleitzahlenbereiche lizensiert:</strong></div>
       <div class="col-xs-8">
        <?php
          if($in){
            foreach($in as $item){
              print '<span class="label label-primary">'. $item .'</span> &nbsp;';
            }
          }
          
          if($in AND $out){
            print '<hr />';
          }
          
          if($out){
            print implode(", ", $out);
          }
        ?>
       </div>
   </div>