<div class="well">
  Sie können die Quell-Dateien nun herunterladen.
  <br /><br />
  <a class="btn btn-lg btn-blue-arrow pull-right" href="<?php print $link; ?>">
    <span class="glyphicon glyphicon-cloud-download"></span> Datei jetzt herunterladen
  </a>
 
  <ul>
    <li><b>Dateiname:</b> <?php print $lizenz -> lizenz_download_filename; ?></li>
    <li><b>Dateigröße:</b> <?php print format_size($lizenz -> lizenz_download_filesize); ?></li>
  </ul>  
</div>
