<?php

function vku_cronapi($op, $job = NULL) {

  $items['vku_generate_cron'] = array(
    'description' => 'VKUs generieren',
    'rule' => '*/5 * * * *', // Every 5 minutes
    // i must call: example_news_fetch('all')
    'callback' => 'vku_generate_cron',
    'arguments' => array('all'),
  );
  
  $items['vku_delete_unusedfiles'] = array(
    'description' => 'Unbenötigte Lizenzen löschen',
    //'rule' => '*/5 * * * *', // Every 5 minutes
    // i must call: example_news_fetch('all')
    'callback' => 'vku_delete_unusedfiles',
    'arguments' => array('all'),
  );
  
  $items['vku_delete_old_vkus'] = array(
    'description' => 'Unbenötigte VKUs löschen (VKU-Status=deleted)',
    'callback' => 'vku_delete_old_vkus',
    'arguments' => array('all'),
  );
  
  return $items;
}




function vku_delete_old_vkus(){
    
  $time = time() - (60*60*24*31); // vor einem Monat  
    
  $dbq = db_query("SELECT vku_id, vku_changed "
          . "FROM lk_vku "
          . "WHERE vku_status='deleted' AND ((vku_created < '". $time ."' AND vku_changed < '". $time ."') OR vku_changed IS NULL) ORDER BY vku_id DESC");
  foreach($dbq as $all){
    $vku = new VKUCreator($all -> vku_id);
    if($vku -> is()){
        $vku ->logEvent('remove-cron', "Cron-Delete VKU-ID " . $all -> vku_id . ", Zuletzt geändert am: " . format_date($all -> vku_changed, 'short'));
        $vku ->remove();
    }
  }
  
  $time = time() - (60*60*24);
  $dbq = db_query("SELECT vku_id, vku_changed "
          . "FROM lk_vku "
          . "WHERE vku_status='new' AND vku_changed < '". $time ."' ORDER BY vku_id DESC");
  foreach($dbq as $all){
    $vku = new VKUCreator($all -> vku_id);
    if($vku -> is()){
        $vku ->logEvent('remove-cron', "Cron-Delete nicht benutze VKU " . $all -> vku_id . ", Zuletzt geändert am: " . format_date($all -> vku_changed, 'short'));
        $vku ->remove();
    }
  }
  
  
}

// Update Old VKUs to display them different in the view
function vku_cron(){
  $date = time()  - 60*60*24*variable_get('lk_vku_max_download_time', 30);
  
  $dbq = db_query("SELECT vku_id FROM lk_vku WHERE vku_status='purchased' AND vku_purchased_date <='". $date  ."'");
  foreach($dbq as $item){
    $dbq2 = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE vku_id='". $all -> vku_id  ."' AND lizenz_until <='". time() ."'");
    $res = $dbq2 -> fetchObject();
    
    if($res -> count == 0){
      db_query("UPDATE lk_vku SET vku_status='purchased_done' WHERE vku_id='". $item -> vku_id ."'");
    }
  }
}


function vku_generate_cron(){
// Delete unused preview files
  $dirname = 'sites/default/vkutest';
  
  $time = time() - (60*10); 
  $dir = opendir($dirname);
  while($date = readdir($dir)){
    if($date == '.' OR $date == '..') continue;
    
    $filetime = filemtime($dirname . "/" . $date);
    
    // Löschen wenn älter als 10 Minuten
    if($filetime < $time){
      unlink($dirname . "/" . $date);
    } 
  }
}


function vku_delete_unusedfiles(){

  $time = variable_get('lk_vku_max_download_time', 30) + 5;
  
  $deletebefore = time() - (60*60*24*$time);
  
  // Lizenzen checken
  $dir = opendir("sites/default/private/downloads");
  while($date = readdir($dir)){
      if($date == '.' OR $date == '..') continue;
      
      $file = "sites/default/private/downloads/" . $date;
      $check = filemtime($file);
      
      if($check < $deletebefore){
        unlink($file);
      }
  }
  
  closedir($dir);
  
  
  // Alte Lizenzen löschen
  $time = 60;
  $deletebefore = time() - (60*60*24*$time);
  
  $dbq = db_query("SELECT * FROM lk_vku WHERE vku_ready_time IS NOT NULL AND vku_ready_filename !='' AND vku_ready_time > '". $deletebefore ."'");
  foreach($dbq as $all){
      // DELETE FILE - Mark as Deleted
        
      if(in_array($all -> vku_status, array("deleted", 'purchased', 'purchased_done'))){
          if(file_exists('sites/default/private/vku/' . $all -> vku_id . '.pdf')){
              unlink('sites/default/private/vku/' . $all -> vku_id . '.pdf');               
          }              
        }
  }
}


?>