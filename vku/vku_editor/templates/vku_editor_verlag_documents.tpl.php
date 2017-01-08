
<?php //kpr($documents); ?>

<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th>Dokument</th>
      <th>Erstellt</th>
      <th>Zuletzt bearbeitet</th>
      <th>Aktionen</th>
    </tr>
 </thead>
  
  <?php while(list($key, $val) = each($documents)): ?>
   
    <tr>
      <td colspan="1"><strong><?php print $val['title']; ?></strong></td>
      <td colspan="3" class="text-right">
        
        <div class="link-group">
          <a class="btn btn-sm btn-default btn-block"><span class="glyphicon glyphicon-plus"></span> Dokument erstellen</a>
          
          <div class="items text-left">
            <?php while(list($preset, $info) = each($val['presets'])): ?>
            <a class="btn btn-sm btn-default btn-block btn-create-document" data-preset="<?= $preset ?>">
              <strong><?= $info['title']; ?></strong><br />
              <small><?= $info['desc']; ?></small>
            </a>  
            <?php endwhile; ?>
          </div>
          
        </div>
      </td>
    </tr>
    <?php 
      if(count($val['documents']) === 0):
        ?>
          <tr>
            <td colspan="4" class="small"><em>Keine Dokumente in dieser Kategorie.</em></td>
          </tr>
        <?php
      endif;
    ?>
  
  <?php endwhile; ?>
</table>