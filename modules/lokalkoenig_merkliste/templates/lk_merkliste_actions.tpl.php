<?php
  if(!$merkliste){
    ?>
      <?php print variable_get('lk_merkliste_info', 'Info'); ?>
      <h3>Ihre zuletzt hinzugefügten Kampagnen</h3>
      <hr />
    <?php
      
      if(!$nodes){
        ?>
          <p class="text-center">Sie haben bisher keine Merklisten erstellt.</p>
        <?php
      }
      print $nodes;
    return ;
  }
?>

<ul class="nav nav-tabs" role="tablist" id="merkliste-tabs">
  <li role="presentation" class="active">
    <a href="#mlvku" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-shopping-cart" data-toggle="tooltip" data-original-title="Merkliste in Verkaufsunterlage umwandeln"></span></a>
  </li>
  <li role="presentation">
    <a href="#mledit" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-pencil" data-toggle="tooltip" data-original-title="Merkliste bearbeiten"></span></a>
  </li>
  
  <?php if(!lk_is_moderator()):  ?>
    <li role="presentation">
      <a href="#mlsendform" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-envelope" data-toggle="tooltip" data-original-title="Merkliste versenden"></span></a>
    </li>
  <?php else: ?>
        <li role="presentation"><a href="<?php print url('messages/new', array('query' => array('ml' => $merkliste_id))); ?>" role="tab"><span class="glyphicon glyphicon-envelope" data-toggle="tooltip" data-original-title="Merkliste versenden (Admin-Modus)"></span></a></li> 
  <?php endif; ?>
   
        <li role="presentation" class="pull-right">
          <a optintitle="Merkliste entfernen" optin="Wollen Sie die aktuelle Merkliste wirklich löschen?" class="optindelete" href="/merkliste/delete/<?= $merkliste_id; ?>">
            <span class="glyphicon glyphicon-trash" data-toggle="tooltip" data-original-title="Merkliste löschen"></span></a>
        </li>
</ul>

<div class="tab-content">
  <div role="tabpanel" class="tab-pane well" id="mledit"> 
    <form method="POST" action="/merkliste/rename/<?= $merkliste_id; ?>">
          <p><strong>Bearbeiten:</strong><br />
            <input name="name" type="text" value="<?= $name; ?>"  class="form-control form-text" />
          </p>
          <button class="btn btn-primary" tid="<?= $merkliste_id; ?>">Umbenennen</button>  
          
         </form>
  </div>
  <div role="tabpanel" class="tab-pane active" id="mlvku">
        <form class="well" method="post" action="/merkliste/convert/<?= $merkliste_id; ?>">
             <p data-toggle="tooltip" data-original-title="Maximallänge: 75 Zeichen"><b>Zu Verkaufsunterlage umwandeln</b>
              <input maxlength="75" name="title" type="text" value="Ihr Angebot"  class="form-control form-text" />
             </p>
            <button class="btn btn-success">Verkaufsunterlage erstellen</button>
          </form>
  </div>
  
  <div role="tabpanel" class="tab-pane" id="mlsendform">
        <form class="well" method="post" action="/merkliste/send/<?= $merkliste_id; ?>">
             <div class="select-container"></div>
            
             <p><b>Betreff:</b></p>
             <p>
              <input maxlength="25" name="subject" type="text" value="Merkliste: <?= $name; ?>"  class="form-control form-text" />
             </p>
             
             <p><b>Nachricht (optional):</b></p>
             <p><textarea class="form-control form-textarea" name="message" cols="60" rows="6"></textarea></p>
             <p>
               <button class="btn btn-success">Senden</button>
            </p>  
          </form>
  </div>  
</div>

<script>
   jQuery(document).ready(function(){
      
      jQuery('.select-container').html(sendto_kampas);    

      var hash = window.location.hash;
      if(hash){
        jQuery('ul.nav a[href="' + hash + '"]').tab('show');
      }
   });

</script>


<style>
   .select-container .bootstrap-select {
    width: 100% !important;
    margin-top: 5px;
   }
</style>

<div class="well hidden">
   <div class="row">
       <div class="col-xs-2">
            <strong>Versenden</strong><br />
           <a href="/messages/send/<?= $id; ?>" class="btn btn-primary" ><span class="glyphicon glyphicon-envelope"></span> Versenden</a>
       </div>
   </div>
</div>


<?= $nodes; ?>
