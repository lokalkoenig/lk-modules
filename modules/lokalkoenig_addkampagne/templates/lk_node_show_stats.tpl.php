<div class="panel panel-default panel-info">
  <div class="panel-body">
    <div class="well">Die Statistiken beziehen sich auf die einzelne Kampagne.</div>
    <table class="table">
      <?php
        while(list($key, $val) = each($stats)){
           print '<tr><td>'.  $key . '</td><td>' . $val .'</td></tr>';
        }
      ?>
  
    </table>
  </div>
</div>  
  
      
      
      