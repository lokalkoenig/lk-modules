
<div class="form-group">
  <div class="input-group">
    <div class="input-group-addon">Download-Link:</div>
    <input type="text" class="form-control form-select-all" value="<?php print $lizenz -> download_link_external; ?>">
    <a href="<?= $lizenz -> download_link_direct; ?>" class="input-group-addon btn btn-sm btn-primary"><span class="glyphicon glyphicon-hdd"></span>&nbsp;&nbsp;Direkt Download (<?= format_size($lizenz -> lizenz_download_filesize); ?>)</a>
  </div>
</div>

