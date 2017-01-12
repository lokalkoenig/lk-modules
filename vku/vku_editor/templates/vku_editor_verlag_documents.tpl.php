
<?php //kpr($documents); ?>

<table class="table table-condensed table-hover">
  <thead>
    <tr>
      <th>Dokument</th>
      <th>Erstellt</th>
      <th>Zuletzt bearbeitet</th>
      <th width="160">Aktionen</th>
    </tr>
 </thead>
  
  <?php while(list($key, $val) = each($documents)): ?>
   
 <tr class="tr-headline">
      <td colspan="2"><strong><?php print $val['title']; ?></strong></td>
      <td colspan="2" class="text-right">
        
        <?php if($val['presets']): ?>
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
        <?php endif; ?>
        
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
            <td class="small"><?= date('d.m. Y', $document['document_created']); ?></td>
            <td class="small"><?= date('d.m. Y', $document['document_changed']); ?></td>
            <td><a href="#" class="btn-document-edit" data-edit-id="<?= $document['id']; ?>">Editieren</a></td>  
          </tr>  
          <?php
      endforeach;
      
      if(count($val['documents_unpublished']) > 0):
        ?>
         <tr>
           <td colspan="4"><em>Unveröffentlichte Dokumente</em></td>
         </tr> 
         <?php
          foreach ($val['documents_unpublished'] as $document):
          ?>
            <tr class="greyed-out">
              <td><?= $document['document_title']; ?><br />
                <small><?= $document['preset_title']; ?></small>
              </td>
              <td class="small"><?= date('d.m. Y', $document['document_created']); ?></td>
              <td class="small"><?= date('d.m. Y', $document['document_changed']); ?></td>
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
  table tr.greyed-out td {
    color: darkgray;
  }
  
  tr.tr-headline td {
    background-color: White;
    color: #428bca;
    border-bottom-color: #428bca !important;
    border-bottom-width: 2px !important;
    border-bottom-style: solid !important;
  }
  
  tr.tr-headline:hover td {
    background-color: White !important; 
  }
  
</style>
  