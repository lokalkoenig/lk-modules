<div class="panel panel-default panel-info">
  <div class="panel-body">
    <div class="well well-white">
        <div class="row clearfix">
            <div class="col-xs-1 text-center"><span class="glyphicon glyphicon-big glyphicon-info-sign"></span></div>
            <div class="col-xs-11">Alerts sind Benachrichtigungen über neue Kampagnen im Lokalkönig. Sie werden automatisch per E-Mail benachrichtigt, falls für eine Suche eine neue Kampagne erstellt worden ist. Sie können die Alerts jederzeit löschen.</span>
          </div>
        </div>  
    </div>
    
    <hr />
    
    <?php if($count == 0): ?>
      <div class="alert alert-success">Sie haben keine Alerts gespeichert. <?php print l("Zur Kampagnensuche", 'suche'); ?></div>
   <?php else: print '<div class="well well-white">' . $table . '</div>'; endif; ?>     
  </div>
</div>
   
  