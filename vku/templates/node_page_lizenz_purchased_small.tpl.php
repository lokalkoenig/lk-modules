

<p><input type="text" class="form-control form-select-all" value="<?php print $lizenz -> download_link_external; ?>"></p>
<p>
  <a class="btn btn-blue-arrow" href="<?= $lizenz -> download_link_direct; ?>"><span class="glyphicon glyphicon-cloud-download"></span> Direkt-Download (<?= format_size($lizenz -> lizenz_download_filesize); ?>)</a>
</p>
