
<?php //kpr($documents); ?>

<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th>Dokument</th>
      <th>Erstellt</th>
      <th>Zuletzt bearbeitet</th>
      <th width="160">Aktionen</th>
    </tr>
 </thead>
  
  <?php while(list($key, $val) = each($documents)): ?>
   
    <tr>
      <td colspan="2"><strong><?php print $val['title']; ?></strong></td>
      <td colspan="2" class="text-right">
        
        <div class="link-group">
          <a class="btn btn-sm btn-default btn-block"><span class="glyphicon glyphicon-plus"></span> Dokument erstellen</a>
          
          <div class="items text-left">
            <?php while(list($preset, $info) = each($val['presets'])): ?>
            <a class="btn btn-sm btn-default btn-block btn-document-create" data-preset="<?= $preset ?>">
              <strong><?= $info['title']; ?></strong><br />
              <small><?= $info['desc']; ?></small>
            </a>  
            <?php endwhile; ?>
          </div>
          
        </div>
      </td>
    </tr>
    <?php 
      if(count($val['documents']) === 0 && count($val['documents_unpublished']) == 0):
        ?>
          <tr>
            <td colspan="4" class="small"><em>Keine Dokumente in dieser Kategorie.</em></td>
          </tr>
        <?php
      endif;
      
      foreach ($val['documents'] as $document):
          ?>
          <tr>
            <td><?= $document['document_title']; ?><br />
              <small><?= $document['preset_title']; ?></small>
            </td>
            <td><?= date('d.m. Y', $document['document_created']); ?></td>
            <td><?= date('d.m. Y', $document['document_changed']); ?></td>
            <td><a href="#" class="btn-document-edit" data-edit-id="<?= $document['id']; ?>">Editieren</a></td>  
          </tr>  
          <?php
      endforeach;
      
      if(count($val['documents_unpublished']) > 0):
        ?>
         <tr>
           <td colspan="4">Unveröffentlichte Dokumente</td>
         </tr> 
         <?php
          foreach ($val['documents_unpublished'] as $document):
          ?>
            <tr class="greyed-out">
              <td><?= $document['document_title']; ?><br />
                <small><?= $document['preset_title']; ?></small>
              </td>
              <td><?= date('d.m. Y', $document['document_created']); ?></td>
              <td><?= date('d.m. Y', $document['document_changed']); ?></td>
              <td><a href="#" class="btn-document-edit" data-edit-id="<?= $document['id']; ?>">Editieren</a></td>  
            </tr>  
          <?php
          endforeach;
         ?>
        <?php
      endif;
      
      
    ?>
  
  <?php endwhile; ?>
</table>

<style>
 table {
   font-size: 0.9em;
 }
  
  table tr.greyed-out td {
    color: darkgray;
  }
  
</style>
  