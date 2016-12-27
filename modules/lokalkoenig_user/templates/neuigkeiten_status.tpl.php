<hr />
<div class="well well-white">
   <strong>Ihre Neuigkeit</strong><br />
      <div class="pull-right"><a href="<?php print $entity -> edit_url; ?>" class="btn btn-sm btn-primary">Neuigkeit editieren</a> <a href="<?php print $entity -> delete_url; ?>" optintitle="Neuigkeit löschen" optin="Soll die Aktion wirklich durchgeführt werden?" class="optindelete btn btn-sm btn-danger">Löschen</a></div>
 
    <ul>
      <li>Empfänger: <?php print count($entity->field_recievers['und']); ?></li>
      <li>Status: <?php print ucfirst($entity->field_message_status['und'][0]['value']); ?></li>
      <li>Gelesen: <?php print ($entity->read_count); ?> mal [<a href="<?php print $entity -> read_count_details; ?>">Mehr Informationen</a>]</li>
    </ul>    
</div>