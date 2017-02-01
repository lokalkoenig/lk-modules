




<?php while(list($key, $val) = each($documents)): ?>

  <?php
  if(count($val['documents']) === 0 && count($val['documents_unpublished']) === 0){
    continue;
  }
?>

  <h2 class="special"><?php print $val['title']; ?></h2>
  <div class="well well-white" style="padding: 10px;">

  <table class="table table-hover">
  <thead>
    <tr>
      <th class="headline">Aktive Dokumente</th>
      <th>Erstellt</th>
      <th>Letzte Bearbeitung</th>
      <th width="160">&nbsp;</th>
    </tr>
 </thead>

    <?php
      if(count($val['documents']) === 0):
        ?>
          <tr>
            <td colspan="4" class="small"><em>Keine veröffentlichten Dokumente in dieser Kategorie.</em></td>
          </tr>
        <?php
      endif;

      foreach ($val['documents'] as $document):
          ?>
          <tr>
            <td><?= $document['document_title']; ?><br />
              <small><?= $document['preset_title']; ?></small>
            </td>
            <td class="small"><?= date('d.m.Y', $document['document_created']); ?></td>
            <td class="small"><?= date('d.m.Y', $document['document_changed']); ?></td>
            <td class="small">
              <a href="#" class="btn-document-edit" data-edit-id="<?= $document['id']; ?>">Bearbeiten</a> /
              <a href="#" class="btn-document-remove" data-edit-id="<?= $document['id']; ?>">Löschen</a><br />
              <a href="#" class="btn-document-toggle" data-edit-id="<?= $document['id']; ?>">Deaktivieren</a>
            </td>
          </tr>
          <?php
      endforeach;

      if(count($val['documents_unpublished']) > 0):
        ?>
         <tr class="greyed-out">
           <td colspan="4"><strong>Inaktive Dokumente</strong></td>
         </tr>
         <?php
          foreach ($val['documents_unpublished'] as $document):
          ?>
            <tr class="greyed-out">
              <td><?= $document['document_title']; ?><br />
                <small><?= $document['preset_title']; ?></small>
              </td>
              <td class="small"><?= date('d.m.Y', $document['document_created']); ?></td>
              <td class="small"><?= date('d.m.Y', $document['document_changed']); ?></td>
              <td class="small">
                <a href="#" class="btn-document-edit" data-edit-id="<?= $document['id']; ?>">Bearbeiten</a> /
                <a href="#" class="btn-document-remove" data-edit-id="<?= $document['id']; ?>">Löschen</a> /
                <a href="#" class="btn-document-toggle" data-edit-id="<?= $document['id']; ?>">Aktivieren</a>
              </td>
            </tr>

          <?php
          endforeach;
         ?>

        <?php
      endif;
    ?>
    </table>
    </div>

  <?php endwhile; ?>


<style>
  h3.special {
    color:rgb(106, 168, 119);
    font-size: 14px;
    border-bottom: 0;
  }

   h2.special {
    font-size: 18px;
    border-bottom: 0;
  }

  table tr th.headline  {
    width: 40%;
  }

  table tr.greyed-out td {
    color: #777;
  }

</style>
