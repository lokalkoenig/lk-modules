<?php
namespace LK;

use LK\Log\LogTrait;

class Lizenz {
  
  use LogTrait;
  var $id = null;
  var $data;
  var $vku_id;
  var $ausgaben = array();

  function __construct($id) {
    $dbq2 = db_query("SELECT * FROM lk_vku_lizenzen WHERE id='". $id ."'");
    $lizenz = $dbq2 -> fetchObject();

    if(!$lizenz){
        return ;
    }

    $this -> data = $lizenz;
    $this -> id = $id;
    $this -> vku_id = $this -> data -> vku_id;

    $dbq = db_query("SELECT ausgabe_id FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $id ."'");
    foreach($dbq as $all){
         $this -> ausgaben[] = $all -> ausgabe_id;
    }
  }
   
   /**
    * Gets an Object for the Template
    * 
    * @return \stdClass
    */
   function getTemplateData(){
     
     $data = $this->data;
     $data -> download_link_direct = $this->getDownloadLink(true);
     $data -> download_link_external = $this->getDownloadLink();
     $data -> node = \node_load($this->getNid());
     $data -> download_max = variable_get('lk_vku_max_download');
     
     // gets the Information
     $test =  $this->canDownload();
     $data -> can_download = $test["access"];
     $data -> can_download_info = $test;
     
   return $data;  
   }
   
   function getId(){
       return $this -> id;
   }
   
   function is(){
       return $this -> id;
   }
   
   function getShortSummary(){
     return "Erworben am: " . format_date($this-> data -> lizenz_date) . " / " . $this -> data -> lizenz_downloads . " &times heruntergeladen";
   }
   
   /**
    * Generates the ZIP for the Licence
    */
   function generateZIP(){
     
     $manager = new LizenzDownload($this);
     return $manager -> createZip();
   }
   
   /**
    * Returns a public Lizenz Download link
    * 
    * @return String absolute URL
    */
   function getDownloadLink($direct = false){
     
     $lizenz = $this->data;
     
     $infos = array();
     $infos[] = $lizenz -> lizenz_date;
     $infos[] = $lizenz -> nid;
     $infos[] = $lizenz -> lizenz_uid;
     $infos[] = $lizenz -> lizenz_paket;
     
     $arguments = array("absolute" => true);
     
     if($direct){
       $arguments['query'] = [
           'download' => time()
       ];
     }
     
     return url("download/" . implode("-", $infos), $arguments);  
   }
   
   /**
    * Lizenz is Downloadable now
    * 
    * @return boolean
    */
   function isDownloadable(){

     $test = $this->canDownload();
     return $test['access'];
   }

   /**
    * Checks if the Lizenz can be downloaded or not
    * 
    * @return Array Descriptions
    */
   function canDownload(){
     
     $lizenz = $this -> data;
     
     $date = $lizenz -> lizenz_until;
     if(time() > $date){
        return array('access' => false, 'reason' => "Die Downloads sind zeitlich abgelaufen.");
     }
     // Zuviele Downloads
    if($lizenz -> lizenz_downloads >= variable_get('lk_vku_max_download')){
      return array('access' => false, 'reason' => "Die Maximalanzahl der Downloads wurde erreicht.");
    }

    return array('access' => true);
   }
   
   
   function getNid(){
     return $this -> data -> nid;
   }
   
   /**
    * Sets the Values
    * 
    * @param type $key
    * @param type $val
    */
   function set($key, $val){
     $this->data->$key = $val;
     $id = $this->getId();
     
     db_query("UPDATE lk_vku_lizenzen SET ". $key ."='". $val ."' WHERE id='". $id  ."'"); 
   }
    
   /**
    * Gets the Paket
    * 
    * @return Int
    */
   function getPaket(){
       return $this -> data -> lizenz_paket;
   }
   
   function getDate(){
       return $this -> data -> lizenz_date;
   }
   
   function getAuthor(){
       return $this -> data -> lizenz_uid;
   }
   
   function getDownloads(){
       
       $downloads = array();
       $db2 = db_query("SELECT * FROM lk_vku_lizenzen_downloads WHERE lizenz_id='". $this -> id ."'");
       foreach($db2 as $all2){
          $downloads[] = u($all2 -> uid) . " <small>(". date('d.m.Y', $all2 -> download_date) .")</small>";
       } 
       
   return $downloads;    
   }
   
   function getAusgaben(){
      return $this -> ausgaben; 
   }
   
   function setAusgaben($ausgaben){
       
       $id = $this -> getId();
       // delete all them
       db_query("DELETE FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $id ."'");
       
       $plz_collection = array();
       $this -> ausgaben = array();
       foreach($ausgaben as $ausgabe){
           $object = new Ausgabe($ausgabe);
           $plz = $object ->getPlz();
           $this -> ausgaben[] = $ausgabe;
           db_query("INSERT INTO lk_vku_lizenzen_ausgabe SET lizenz_id='". $id ."', ausgabe_id='".  $ausgabe."'");
           
           // generate an PLZ-Area
           foreach($plz as $item){
               if(!in_array($item, $plz_collection)){
                   $plz_collection[] = $item;
               }
           }
       }
       
       if($this -> data -> plz_sperre_id){
           $sperre = new PlzSperre($this -> data -> plz_sperre_id);
           return $sperre ->setPlzTids($plz_collection);
       }
       
   return false;    
   }
   
   function createSperre($ausgaben, $until_date){
      
      $this->setAusgaben($ausgaben);
     
      $nid = $this->getNid();
      $uid = $this->getAuthor();
      
      $manager = new \LK\Kampagne\SperrenManager();
      $result = $manager ->createSperre($nid, $uid, $ausgaben, $until_date);

      // only if we get a Sperre
      if($result){
        $plz_id = $result ->getId();
        db_query("UPDATE lk_vku_lizenzen SET plz_sperre_id='". $plz_id ."' WHERE id='". $this->getId() ."'");
        $this -> data -> plz_sperre_id = $plz_id;
      }

   return $result;   
   }
   
   function extend($until_timestamp){
      $vku = $this -> getVku();
      $vku ->setStatus('purchased');
      $id = $this ->getId();
      
      $this -> data -> lizenz_until = $until_timestamp;
      db_query("UPDATE lk_vku_lizenzen SET lizenz_download_serverfilename='', lizenz_until='". $until_timestamp ."' WHERE id='". $id ."'");
      return \lk_note('lizenz-admin', "Erweitere Lizenz " . $id . " bis zum " . format_date($until_timestamp));
   }
   
   function getEditUrl(){
       return 'backoffice/logbuch/editlizenz/' . $this -> id;
   }
   
   /**
    * Gibt die Lizenz zurück
    * 
    * @return \VKUCreator
    */
   function getVku(){
        return new \VKUCreator($this -> vku_id);
   }
           
   function remove(){
      // Remove PLZ-Sperre
      $id = $this -> id;
      $plz_sperre_id = $this -> data -> plz_sperre_id;
      
      db_query("DELETE FROM lk_vku_lizenzen WHERE id='". $id ."'");
      db_query("DELETE FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $id ."'");
      
      // Lösche PLZ -Sperre
      if($plz_sperre_id){
          $manager = new \LK\Kampagne\SperrenManager();
          $manager ->removeSperre($plz_sperre_id);
       }
       
      // checken if VKU has a Lizenz
      $vku = $this -> getVku();
      $test = $vku -> getLizenzen();
      if(!$test){
         $vku -> setStatus('deleted'); 
         $vku -> logEvent('remove', 'Status geändert auf Deleted, da Lizenz gelöscht wurde.'); 
      }
      
  return $this->logNotice("Lizenz gelöscht.", array('lizenz' => $this));
  }

  /**
   * Gets the PLZ of the Lizenz
   *
   * @return boolean|\LK\Kampagne\Sperre
   */
  function getPLZSperre() {

    if(!$this -> data -> plz_sperre_id){

      return FALSE;
    }

    $manager = new \LK\Kampagne\SperrenManager();
    $sperre = $manager ->getSperre($this -> data -> plz_sperre_id);

    return $sperre;
  }

  /**
   * Gets a Summary of the Lizenz
   *
   * @return string
   */
  function getSummary(){

    $current = \LK\current();

    $vku = new \VKUCreator($this -> vku_id);
    $node = node_load($this -> data -> nid);
    $array = array();

    if($this -> data -> lizenz_uid !== $current->getUid()) {
      $array['Benutzer'] = \LK\u($this -> data -> lizenz_uid);
    }
    
    $array['Lizenziert am'] = format_date($this -> data -> lizenz_date);
    $array['Downloads gültig bis'] = format_date($this -> data -> lizenz_until);
    $array['Verkaufsunterlage'] = $vku ->getValue('vku_title') . ' ['. $vku->getId() .']';

    if($current->isModerator()) {
      $array['Verkaufsunterlage'] = l($array['Verkaufsunterlage'], $vku->url(), ['html' => TRUE]);
    }

    if($this -> data -> lizenz_verlag_uid && $current->isModerator()){
      $verlag = \LK\get_user($this -> data -> lizenz_verlag_uid);
      $array['Verlag'] = (string)$verlag;      
    }

    $sperre = $this->getPLZSperre();
    if($sperre){
      $date = strtotime($sperre->entity->field_plz_sperre_bis['und'][0]['value']);
      $array['Ablauf der PLZ-Sperre'] = date('d.m.Y', $date);
    }

    $ausgaben = $this ->getAusgaben();
    $ausgaben_formatted = array();
    foreach($ausgaben as $au){
      $a = \LK\get_ausgabe($au);
      $ausgaben_formatted[] = $a ->getTitleFormatted();
    }

    if($ausgaben){
      $array['Ausgaben'] = implode(" ", $ausgaben_formatted);
    }

    $array['Downloads'] = '';
    $downloads = $this ->getDownloads();

    if(!$downloads) {
      $array['Downloads'] = '<em>Keine Downloads bisher</em>';
    }
    else {
      $array['Downloads'] = count($downloads) . ' &times; heruntergeladen';

      if($current ->isModerator()) {
        $array['Downloads'] .= '<ul class="small"><li>'. implode("</li><li>", $downloads) .'</li></ul>';
      }
    }
 
    $data = '<div class="row clearfix">';
    $data .= '<div class="col-xs-12"><span class="label label-default pull-right" style="margin-left: 10px;">Lizenz #' . $this->getId() . '</span>';

    // add a check, otherwise Drupal will break
    if($current->isModerator() && arg(2) != "editlizenz"){
      $data .= l('<span class="glyphicon glyphicon-pencil"></span> Lizenz editieren', $this ->getEditUrl(), array('html' => true, "query" => drupal_get_destination(), 'attributes' => array('class' => 'btn btn-xs btn-default pull-right')));
    }

    $data .= '<h4 style="margin: 0; margin-bottom: 10px;">Kampagne: ' .l($node->title, 'node/'. $node->nid) . ' <small>'. $node -> sid .'</small></h4>';
    $data .= '</div>';

    $data .= '<div class="col-xs-8">'. \LK\UI\DataList::render($array) .'</div>'
           . '<div class="col-xs-4 text-right">';

    $data .= \LK\UI\Kampagne\Picture::get($node->nid, ['height' => 150, 'width' => 150]);
    $data .= '</div>';
    $data .= '</div>';

    if($this->isDownloadable() && $current->getUid() == $this->getAuthor()) {
      $widget = theme('node_page_lizenz_purchased_small', ["lizenz" => $this->getTemplateData()]);
      $data .= '<hr />' . $widget;
    }

    return $data;
  }
}

/**
 * Lizenz Download class
 * 
 * Creates the ZIP, Tracks the Downloads
 */
class LizenzDownload {
  
  use \LK\Log\LogTrait;
  
  const DOWNLOAD_DIR = 'sites/default/private/downloads';
  var $lizenz = null;
 
  function __construct(Lizenz $lizenz) {
    $this -> lizenz = $lizenz;
  }
  
  /**
   * Checks if the ZIP is already created
   * 
   * @return boolean
   */
  public function DownloadReady(){
    $lizenz = $this->lizenz;
    
  return empty($lizenz -> data -> lizenz_download_filename);  
  }
  
  /**
   * Downloads the ZIP
   */
  public function downloadZip(){
    
    $savedir = self::DOWNLOAD_DIR;
    $lizenz = $this->lizenz;
    
    if(!$this->DownloadReady()){
      $result = $this->createZip();
      
      if(!$result){
        $this ->logError("Probleme bei der Generierung von Lizenz #" . $lizenz ->getId());
        
        drupal_set_message('Es gab Probleme bei der Generierung der Lizenzdateien. Wir werden uns dazu bei Ihnen melden.');
        drupal_goto("<front>");
        drupal_exit();
      }
    }
    
    $this ->trackDownload();
    
    ob_clean();
    ob_end_flush();
    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=\"". $lizenz -> data -> lizenz_download_filename ."\"");
    readfile($savedir . '/' . $lizenz -> data -> lizenz_download_serverfilename);
    drupal_exit();   
  }
  
  /**
   * Tracks the Download
   * 
   * @global type $user
   */
  private function trackDownload(){
  global $user;  
    
    $lizenz = $this->lizenz;
    $downloads = $lizenz->data->lizenz_downloads;
    
    if($downloads === 0){
      $lizenz -> set("lizenz_download_date", time());
    }
    
    // Zähler erhöhen
    $lizenz -> set("lizenz_downloads", $downloads + 1);
    
    $array = array(
        'lizenz_id' => $lizenz ->getId(),
        'download_date' => time(),
        'uid' => $user -> uid,
    );
    
    db_insert('lk_vku_lizenzen_downloads')->fields($array)->execute();  
  }
  

  /**
   * Creates a ZIP-File
   * 
   * @return boolean
   */
  public function createZip(){
    
    $lizenz = $this->lizenz;
    
    $file_name = $lizenz ->getId() .'.zip';
    $savedir = self::DOWNLOAD_DIR;
    
    $zip = new \ZipArchive(); 
    $vku = $lizenz->getVku();
    $account = \LK\get_user($lizenz ->getAuthor());
    
   // make sure the Action gets a refresh
    if(file_exists($savedir . '/' . $file_name)){
      unlink($savedir . '/' . $file_name);
    }
    
    // uses the Transliterate Module to transform the Filename
    include_once drupal_get_path('module', "transliteration") . '/transliteration.inc';
    
    // Load Node
    $node = node_load($lizenz ->getNid());
    $zip->open($savedir . '/' . $file_name, \ZIPARCHIVE::CREATE);
   
    $text = variable_get('lk_vku_info_text_downloadfile', '');
    
    // Add an Info-Text
    $variables = array();
    $variables["[!node_title]"] = $node -> title;
    $variables["[!node_sid]"] = $node -> sid;
    $variables["[!node_link]"] = url("node/" . $node -> nid, array("absolute" => true));
   
    $term = taxonomy_term_load($lizenz -> getPaket());
    $variables["[!node_paket]"] = $term -> name;
    $variables["[!lizenz_start]"] = date("d.m.Y H:i:s", $lizenz ->getDate());
   
    // get the Days
    $days = \LK\Kampagne\LizenzManager::getLizenzTime($account); 
    
    $newEndingDate = strtotime(date("Y-m-d H:i:s", time()) . " + ". $days ." day");
    $variables["[!lizenz_end]"] = date("d.m.Y H:i:s", $newEndingDate);
   
    $parsed_text = strtr($text, $variables); 
     
    // Adding an Info-File
    $zip->addFromString('info.txt', $parsed_text);
   
    foreach($node -> medien as $med){
      $url = file_create_url($med->field_medium_source['und'][0]['uri']);
      $url = str_replace($GLOBALS['base_url'] . "/system/files/", "", $url);
      $url = 'sites/default/private/' . $url;
      
      if(file_exists($url)){
        ($zip->addFile($url, $med -> id . "-" . $med->field_medium_source['und'][0]['filename'])); 
      }
   }
   
   $result = $zip->close();
   
   // Zip-Archive konnte erstellt werden
   if($result){
     $filename_public = date("Y-m-d"). '-' . $lizenz ->getId();
     $company = $vku -> get('vku_company');
     
     if($company) {
       $filename_public .= '-' . transliteration_clean_filename($company);
     }
     
     $filename_public .= '.zip';
     $filesize = filesize($savedir . '/' . $file_name); 

     $this ->logNotice("ZIP wurde generiert für Lizenz #" . $lizenz ->getId());
     
     //$lizenz -> set()
     $lizenz->set("lizenz_download_filename", $filename_public);
     $lizenz->set("lizenz_download_filesize", $filesize);
     $lizenz->set("lizenz_download_serverfilename", $file_name);
     
   return true;  
   } 
    
  return false;  
  }
}


/**
 * Class PlzSperre
 */
class PlzSperre {
    
    var $id = null;
    var $entity = null;
    var $nid = null;
    
    function __construct($id) {
        $this -> id = $id;
        $entity = entity_load_single('plz', $id);
        
        if(!$entity){
           new \Exception('Can not find a PLZ-Id.');
        }
        $this -> entity = $entity;
        $this -> nid = $this -> entity->field_medium_node['und'][0]['nid'];
    }
    
    function getNid(){
        return $this -> nid;
    }
    
    function getPlzTids(){
       
       $array = array();
       foreach($this -> entity -> field_plz_sperre['und'] as $id){
           $array[] = $id["tid"];
       }
        
     return $array;   
    }
    
    function setPlzTids($tids){
        $this -> entity  -> field_plz_sperre["und"] = array();
        
        foreach($tids as $tid){
            $this -> entity -> field_plz_sperre["und"][]["tid"] = $tid;
        }
        
        $this -> entity -> save();
        $nid = $this ->getNid();
        
        $manager = new \LK\Kampagne\SperrenManager();
        $manager ->updateNodeAccess($nid);        
    }
    
    function remove(){
         \entity_delete("plz", $this -> id); 
    }  
}
