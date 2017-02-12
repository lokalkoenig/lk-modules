<?php

/**
 * REST Callback VKU 2.0
 *
 * @path /vku/$vku_id/callback
 * @param type $vku_id
 */
function _vku2_callback($vku_id){
    
  $obj = $_POST["save"];
  $vku = new VKUCreator($vku_id);
    
  // Preview
  if(isset($_GET["preview"]) AND $_GET["preview"] == 1){
    $pagemanager = new \LK\VKU\Export\Manager();
    $pagemanager ->generatePDF($vku, 0, true);
  }

  $manager = new \LK\VKU\VKU2($vku, $_POST['save']);
  $manager ->performClient();
}
