
<div class="clearfix well well-white">
    <p><strong>Vorschau</strong></p>
    <p>Ihre Neuigkeit wurde gespeichert. Überprüfen Sie nun die Darstellung und veröffentlichen Sie diese.</p>
    
  <p>An: <?php print $entity -> send_info["title"]; ?> (<?php print $entity -> send_info["count"]; ?> Benutzer)</p>

  <a class="btn btn-success" href="<?php print $entity -> publish_url; ?>"><span class="glyphicon glyphicon-check"></span> Veröffentlichen</a>
  
  <div class="pull-right">
      <a class="btn btn-primary btn-sm"  href="<?php print $entity -> edit_url; ?>"><span class="glyphicon glyphicon-pencil"></span> Bearbeiten</a>
      <a class="btn btn-danger btn-sm optindelete"  optintitle="Neuigkeit löschen" optin="Soll die Aktion wirklich durchgeführt werden?" href="<?php print $entity -> delete_url; ?>"><span class="glyphicon glyphicon-trash"></span> Löschen</a>
   </div>
</div>

<hr />

<?php
  print render($message);
?>

