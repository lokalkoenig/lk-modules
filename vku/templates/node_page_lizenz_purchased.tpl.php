<div class="direct-purchase">
  <a class="btn btn-lg btn-blue-arrow pull-left" href="#" id="purchase-show-link" onclick="return false;">Direktdownload <span class="glyphicon glyphicon-chevron-right"></span></a>
  <div style="display: block; background: #eeeeee; height: 45px;" id="purchaselink">
  <a class="btn btn-blue-arrow pull-right" href="<?php print $url; ?>?download=1" style="margin-top: 5px; margin-right: 5px;" id="purchase-button-down"><span class="glyphicon glyphicon-cloud-download"></span> Direkt-Download <?php if($lizenz -> lizenz_download_filesize) { ?>(<?php print format_size($lizenz -> lizenz_download_filesize); ?>)<?php } ?></a>
  <h4 style="padding: 12px 4px; padding-left: 200px;"><?php print $url; ?></h4></div>
</div>