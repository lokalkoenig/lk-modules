<?php

require_once __DIR__ .'/functions.php';

if(!defined('LK_TEST_VERLAG_UID')){
    define("LK_TEST_VERLAG_UID", 352);
}


class VKUCreator {
    
   use \LK\Log\LogTrait; 
    
  /**
   * VKUData Class
   * 
   * @var VkuData 
   */  
  var $data = NULL;
  
  /**
   * ID of the VKU
   * 
   * @var Integer 
   */
  var $id = NULL;
  
  /**
   * Saved Lizenzen 
   * 
   * @var Array 
   */
  var $lizenzen = NULL; 

  function hasAccess(){
   global $user;
    

    if(!$this -> is()) return false;
    if(lk_is_moderator()) return true;

    $author = $this -> getAuthor();

    if($author == $user -> uid) return true;
    else {
      return false;
    } 
  }

  function update(){
    $this -> set("vku_changed", time());
    return $this -> get("vku_changed");
  }

  function is($status = NULL){
    $id = $this -> getId();
    
    if($id) {
       if($status){
          $current_status = $this -> getStatus();
          if($current_status != $status){
              return false;
          }
       }

      return true;
    }
    else {
       return false; 
    }
  }
  
 /**
  * Gets back an length trimmed title
  * 
  * @param Integer $length
  * @param Boolan $html
  * @return string
  */
  function getTitleTrimmed($length = 20, $html = true){
      $title = $this -> get("vku_title");
     
      
      if(strlen($title) > $length){
          $substriged = substr($title, 0, $length);
          
          if(!$html):
              $title = $substriged;
          else:
              $title = '<span title="'. $title .'">' . $substriged . "...</span>"; 
          endif;
      }
      
      if(!$title){
          $title = '<em>Neue Verkaufsunterlage</em>';
      }
      
  return $title;    
  }
  
  function get($value, $plain = true){
      if($plain == true){
          return check_plain($this -> getValue($value));
      } 
      else {
          return $this ->getValue($value);
      }    
  }

  function set($key, $value = NULL){
  	return $this -> setValue($key, $value);
  }

  function setStatus($status){
      if($this -> data){
          
          if($status == 'deleted'){
              $this -> removePLZSperren();
          }
          
          return $this -> data -> setSetting('vku_status', $status);
      }
  }

  function getValue($value){

      if($this -> data){
          return $this -> data -> getSetting($value);
      }
      else {
          return false;
      }

  }


  function setValue($key, $value){

      if($this -> data){
          return $this -> data -> setSetting($key, $value);
      }
      else {
          return false;
      }
  }
                        
 function getStatus(){
       return $this -> get("vku_status");
 }
/**
 * Return if active status
 * 
 * @return boolean
 */
 function isActiveStatus(){
     $status = $this ->getStatus();
     
     if(in_array($status, array('active', 'template'))){
         return true;
     }
     else {
         return false;       
     }
 }
 
  function getAuthor(){
      return $this -> get("uid");
  }

  function getKampagnen(){
    if($this -> data){
        $results = $this -> data -> searchAll('node', 'kampagne');

        $nodes = array();
        foreach($results as $data){ 
          	$nodes[] = $data["data_entity_id"]; 
        }
        return $nodes;
     } 
     else {
      	return array();
     } 
  }

  function getPages(){
  	 if($this -> data){
  	 	return $this -> data -> object_data; 
  	 }

  	 return array();
  }

  function saveItemOrder($id, $order){
  	$this -> data -> setMediaOrder($id, $order);
  }

  function getPage($id){
  	
  	if($this -> data){
  	 	if(isset($this -> data -> object_data[$id])){
  	 		return $this -> data -> object_data[$id];
  	 	}
  	 }

  return array();	 
  }
  
  /**
   * Logs an Event inside an VKU
   * 
   * @param String $type
   * @param String $message
   * 
   * @return String Message

   */
  function logEvent($type, $message){ 
     $log = new \LK\Log\Debug("[". $type ."] " . $message);
     $log ->setVku($this)->save();
  
  return $message;   
  }
  
  function logVerlagEvent($message, $context = []){
    $log = new \LK\Log\Verlag($message);
    $log ->setVku($this)->save();
  }
  
  /**
   * Returns the Category-Information by ID
   * @since 2016-07-18
   * 
   * @param type $cid
   * @return Object ategory Item
   */
  function getCategory($cid){
      
      $dbq = db_query("SELECT * FROM lk_vku_data_categories WHERE id='". $cid ."'");
      $data = $dbq -> fetchObject();
  
  return $data;    
  }
  
  /**
   * Gets back a Category by name
   * 
   * @param String $name
   * @return Array
   */
  function getCategoryByName($name){
     $values = array(); 
     $id = $this ->getId();
     
     $dbq = db_query("SELECT id FROM lk_vku_data_categories WHERE category='". $name ."' AND vku_id='". $id ."' ORDER BY sort_delta ASC"); 
     foreach($dbq as $all){
        $values[] = $all -> id;  
     }    
  
  return $values;   
  }    
  
  /**
   * Removes a Category 
   * 
   * @param type $cid
   */
  function removeCategory($cid){
      $dbq = db_query("DELETE FROM lk_vku_data_categories WHERE id='". $cid ."'");
  }
  
  /**
   * Sets the Sort Delta of a Category
   * 
   * @param type $id
   * @param type $delta
   */
  function setCategoryDelta($id, $delta){
      db_query("UPDATE lk_vku_data_categories SET sort_delta='". $delta ."' WHERE id='". $cid ."'");
  }    
  
  function removePage($id){
      
      $page = $this ->getPage($id);
      // check for Category
      
      
      if(vku_is_update_user()){
        $cid = $page["data_category"];
        // lk_vku_data_categories
        $data = $this -> getCategory($cid);
      
        if(!in_array($data -> category, array('print', "online"))){
            $this -> removeCategory($cid);
        }
      } 
      
      
      
      return $this -> data -> removeItem($id);
  }  

  function toggleItemStatus($id){
  		$pages = $this -> getPages();
  		
  		if(!isset($pages[$id])){
  			
  			return false;	
  		}
  		else {
  			$status = $pages[$id]["data_active"];

  			$active = 'active';

  			if($status) { $new_status = 0; $active = 'disabled'; }
  			else $new_status = 1;
  		
  			if($this -> data -> changeMediaActive($id, $new_status)){
  				return $active;	
  			}	
  			else {
				
  			}	
  		}

  return false;			
  }

  function countKampagnen(){
  	$kampagen = $this -> getKampagnen();
  	return count($kampagen);
  }

  /** 
   * VKU 2 Categories check
   */
  function vku2Check(){
     
    $dbq = db_query("SELECT count(*) as count FROM lk_vku_data_categories WHERE vku_id='". $this -> getId() ."'");
    $test = $dbq -> fetchObject();
    
    if($test -> count == 0){
        $this -> vku2Covert();
        $this -> data = new VkuData($this -> getId()); 	
        $this ->logEvent('convert', 'Verkaufsunterlage wurde auf das neue Format konvertiert.');
    }
  }    
  
  /**
   * VKU 2 Convert
   */
  function vku2Covert(){
    
    // Add Standard Categories
    // title + print + online
    
    $vku_id = $this -> getId();
    
    // Add the Basic onces
    $print_category = $this ->setDefaultCategory('print', 1); //db_insert('lk_vku_data_categories')->fields(array('vku_id' => $vku_id, 'category' => 'print', 'sort_delta' => 1))->execute();
    $online_categroy = $this ->setDefaultCategory('online', 2); //db_insert('lk_vku_data_categories')->fields(array('vku_id' => $vku_id, 'category' => 'online', 'sort_delta' => 2))->execute();
    
    // get Pages
     $pages = $this -> getPages();
     
     // Check if there is a title page
     $title = false;
     foreach($pages as $page){
        if($page["data_class"] == 'title'){
            $title = true;
            break;
        }
     }
     
     // Add Title page
     if($title == false){
         $category = $this -> setDefaultCategory('title', -1);
         $this -> data -> add('default', 'title', -10, 1, 0, null, $category);
     }
    
     $pages = $this -> getPages();
     $delta = 0;
     
     foreach($pages as $page){
         $cid = 0;
         
         if($page["data_class"] == 'kampagne'){
            $cid = $this ->setDefaultCategory('kampagne', $delta);
         } 
        
         if(in_array($page["data_class"], array('title'))){
             $cid = $this ->setDefaultCategory('title', $delta);
         }
        
         if(in_array($page["data_class"], array('kontakt', 'kplanung'))){
            $cid = $this ->setDefaultCategory('other', $delta);
         }
        
         if(in_array($page["data_class"], array('wochen', 'tageszeitung'))){
           $cid = $print_category;
         }
        
         if(in_array($page["data_class"], array('onlinewerbung'))){
             $cid = $online_categroy;
         }
        
        if($cid){
            $this -> setPageCategory($page["id"], $cid);
        }    
        
        $delta++;
    }
  }
  
  function VKUCreator($id = null, $data = array()){
  global $user;

        $test_int = (int)$id;
        
        // wenn null, dann neue erzeugen oder aktive laden
      
        // Takeover from ID
        if($id == 'new'){
            $test_id = 0;
        }
        elseif($id AND $test_int){
          // Replace
          $test_id = $test_int;
        }
        elseif($id != NULL){
        	// not valid 
        	return ;
		    }
        // Get the Active ID
        else {
            $test_id = vku_get_active_id();
        }
  
       	// Wenn ID, dann weiterverarbeiten
       if($test_id){
         
          $this -> data = new VkuData($test_id); 
          
          if(!$this -> data ->id) {
            return ;
          }
          
       
          if($this -> data -> id){
            $this -> id = $this -> data ->id;
          }
          
          // Check for Categories in the new Format
          if(vku_is_update_user()){
            $this -> vku2Check();
          }
      }
      else {
          $current = \LK\current();
      	  $verlag_uid = $current -> getVerlag();		
          
          $data["vku_changed"] = time();
          
          if(!isset($data["vku_created"])) $data["vku_created"] = time();
          if(!isset($data["uid"])) $data["uid"] = $account -> uid;
          if(!isset($data["verlag_uid"])) $data["verlag_uid"] = $verlag_uid;
          if(!isset($data["vku_status"])) $data["vku_status"] = 'active';
          if(!isset($data["vku_title"])) {  
              $data["vku_title"] = 'Ihr Angebot'; 
          
              if(vku_is_update_user()){
                  $data["vku_title"] = '';
              }  
          }
        
      	  $this -> data = new VkuData(0, $data); 	
      	  $this -> id = $this -> data -> id;	
          
          if(vku_is_update_user()){
              
              // If VKU-Status is not set it's a new One
              if(!isset($data["vku_status"])){
                  $this ->setStatus('new');
              }
              
              // set Defaults	
              
              $category = $this -> setDefaultCategory('title', 1);
              $this -> data -> add('default', 'title', 1, 1,0, NULL, $category);
              
              $this -> setDefaultCategory('print', 2);
              $this -> setDefaultCategory('online', 3);
              
          }
          else {
                $this -> data -> add('default', 'title', 0);
                $this -> data -> add('default', 'tageszeitung', 10);
                $this -> data -> add('default', 'wochen', 11);
                $this -> data -> add('default', 'onlinewerbung', 12);
                $this -> data -> add('default', 'kplanung', 13);
                $this -> data -> add('default', 'kontakt', 100);  
          }
      }  
  }
  
  
/**
 * Adds an Container-Category
 * 
 * @since 2016-07-17
 * 
 * @param String $type
 * @param Integer $delta
 * @return Integer
 */  
function setDefaultCategory($type, $delta){
    
    $vku_id = $this ->getId();
    
    $category = db_insert('lk_vku_data_categories')
            ->fields(array('vku_id' => $vku_id, 'category' => $type, 'sort_delta' => $delta))->execute();
    
    return $category;
}

/** 
 * Update the sort Order of the Category
 * 
 * @since 2016-07-18
 * 
 * @param Int $cid
 * @param Int $delta
 */
function setDefaultCategoryOrder($cid, $delta){
    db_query("UPDATE lk_vku_data_categories SET sort_delta='". $delta."' WHERE id='". $cid ."'");
}


function getDefaultCategory($cid){
    $dbq = db_query("SELECT * FROM lk_vku_data_categories WHERE id='". $cid ."'");
    $all = $dbq -> fetchObject();
    
return $all;    
}


/**
 * Sets the Page Category id
 * @since 2016-07-24
 * 
 * @param Int $page_id
 * @param Int $cid
 */
function setPageCategory($page_id, $cid){
    db_query("UPDATE lk_vku_data SET data_category='". $cid ."' WHERE id='". $page_id ."'");
}    


/**
 * Updates the Page Delta
 * 
 * @since 2016-07-18
 * 
 * @param type $pid
 * @param type $delta
 */
function setPageOrder($pid, $delta){
    db_query("UPDATE lk_vku_data SET data_delta='". $delta ."' WHERE id='". $pid ."'");      
}


/**
 * Sets the Page as Active / Inactive
 * 
 * @param Integer $pid
 * @param Boolean $status
 */
function setPageStatus($pid, $status = true){
    $this -> data -> changeMediaActive($pid, $status);   
}


 function getLizenzen(){

     $lizenzen = array();		
 	 $dbq = db_query("SELECT * FROM lk_vku_lizenzen WHERE vku_id='". $this -> getId() ."'");
     foreach($dbq as $all){
     	$lizenzen[] = $all; 
     }	

     return $lizenzen;
 }	
  
  function hasLizenzen(){
    $id = $this -> getId();
    
    $test = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE vku_id='". $id ."'");
    $result = $test -> fetchObject();
    
    if($result -> count == 0){
      return false;
    } 
    
    return true;
  }

  function getId(){
    return $this -> id;
  }
  
  function getTitle(){

    $title = array();
    $title[] = '<strong>' . $this -> get("vku_title") . '</strong>';

    $company = $this -> get("vku_company");
    if($company) $title[] = ' /  ' . $company;

    $changed = $this -> get("vku_changed");
    if($changed){
       $title[] = ' (zuletzt geändert am: ' . format_date($changed, 'custom', 'd.m.Y') .')';
    }

    $count = $this -> countKampagnen();
    if($count){
        $title[] = ' <br />Kampagnen: '. $count;
    }

    return implode("", $title);
  }
  
  function hasKampagne($nid){
  	 $res = $this -> data -> search("node", 'kampagne', $nid);
     	
  	 if($res) return true;
  	 else return false;
  }

  function getPageId($class){
       return $this -> data -> search("default", $class);
  }
  
 
  function removeKampagne($nid){   
      if($this -> data){
          $this -> data -> remove('node', 'kampagne', $nid);  
      }
  }
  
  function addKampagne($nid){
      if($this -> data){
         $category = $this -> setDefaultCategory('kampagne', 5);
         $this -> update();
         
         $log = new \LK\Log\Verlag("Kampagne wurde hinzuguefügt");
         $log ->setNid($nid);
         $log ->save();
         
         $id = $this -> data -> add('node', 'kampagne', 5, 1, $nid, NULL, $category);
         return $category . "-" . $id;
      }  
      
  return false;    
  }
  
  
  
  function hasPlzSperre(){
  global $user;
  
      $dbq = db_query("SELECT * FROM lk_vku_plz_sperre WHERE vku_id='". $this -> getId() ."'");
      $all = $dbq -> fetchObject();
      
      if(!$all){
          return false;
      }
      else {
          // Get Ausgaben from PLZ-Sperre
          $plz_sperre_id = $all -> plz_sperre_id; 
          $until = $all -> plz_sperre_until;
          
          $ausgaben = array();
          $ausgaben_ids = array();
          // Get Ausgaben form special Table
          $dbq2 = db_query("SELECT DISTINCT plz_ausgabe_id FROM lk_vku_plz_sperre_ausgaben WHERE plz_sperre_id='". $plz_sperre_id ."'");
          foreach($dbq2 as $all){
              
             $ausgabe = \LK\get_ausgabe($all -> plz_ausgabe_id);
             if($ausgabe){
                $ausgaben_ids[] = $ausgabe -> getId(); 
                $ausgaben[] = $ausgabe ->getTitleFormatted(); 
             }
          }
          
          
      return array(
          'until' => $until,
          'url' => $this -> url(),
          'ausgaben' => $ausgaben,
          'ausgaben_ids' => $ausgaben_ids,
          'message' => '<div class="row clearfix"><div class="col-xs-1 text-center"><span class="glyphicon glyphicon-exclamation-sign"></span></div><div class="col-xs-10">Die Kampagnen sind bis zum ' . date("d.m.Y", $until) . " für <br />die Ausgaben ". implode(" ", $ausgaben) ." für Sie vorgemerkt.</div></div>"
        );    
      }
  }
  
  function removePLZSperren(){
      $dbq = db_query("SELECT plz_sperre_id FROM lk_vku_plz_sperre WHERE vku_id='". $this -> getId() ."'");
      foreach($dbq as $all){
          lokalkoenig_nodeaccess_delete_rule($all -> plz_sperre_id);
      }
  }
  
  
  function setPageSerializedSetting($id, $settings){
      return $this -> data -> setPageSerializedSetting($id, $settings);
  }

  function remove(){
      
      // We don't DELETE the VKU, when there is a Lizenz   
      if($this -> hasLizenzen()){
         $this ->logEvent('remove-cron-cancel', "Can not be deleted through there are Licences");
         return false;
      }
      
      if($this -> data) {
          $this -> removePLZSperren();
          $this ->logEvent('vku_remove', "VKU-Daten entferenen");
          $this -> data -> removeData();  
      }

  return true;
  }
  
  /**
   * Copy a existing VKU to a new One
   * 
   * @global type $user
   * @return type
   */
  function cloneVku(){
  global $user;
    
      $verlag_uid = (int)lk_get_verlag_from_user($user);
      
      $array = array();
      $array["uid"] = $user -> uid;
      $array["vku_created"] = time();
      $array["vku_status"] = 'active';
      $array["verlag_uid"] = $verlag_uid; 
      $array["vku_oldformat"] = 0; 
      $array["vku_title"] = $this -> get('vku_title');
      $array["vku_company"] = $this -> get('vku_company');
      $array["vku_untertitel"] = $this -> get('vku_untertitel');
   
      $vku_id = db_insert('lk_vku')->fields($array)->execute();
      $new_data = new VkuData($vku_id);
      $new_data -> takeOver($this -> data);
      return $vku_id; 
  }
  
  
  function isCreated(){
       \LK\Stats::countVKU($this);
  }
  
  /**
   * Sets a PLZ based on the Ausgaben
   * Only call on the Checkout 
   */
  function setShortPlzSperre(){
      
      \LK\Stats::countGeneratedVKU($this);

      $author = $this ->getAuthor();
      $account = \LK\get_user((int)$author);
      
      // Only for Verlag/Mitarbeiter
      if(!$account -> isVerlag() AND !$account ->isMitarbeiter() AND !$account ->isVerlagController()){
          return ;
      }
      
      $verlag = $account ->getVerlag();
      if(!$verlag){
          return ;
      }
      
      $verlag_user = \LK\get_user($verlag);
      $days = $verlag_user -> getVerlagSetting('sperrung_vku_pdf', 0);
      
      // If not Days or Days == 0 (deaktiviert)
      if(!$days){
          return ;
      }
      
      $ausgaben =  $account -> getCurrentAusgaben();
      if(!$ausgaben){
          return ;
      }
      
      $dateplz = date('Y-m-d',strtotime(date("Y-m-d", time()) . " + ". $days ." day"));
       
        $nodes = $this -> getKampagnen();
        foreach($nodes as $nid){
             $node = node_load($nid);
             $plz_id = na_create_node_rule($node -> nid, $account -> uid, $dateplz);
             
             $array = array(
                 'vku_id' => $this -> getId(),
                 'uid' => $account -> uid,
                 'plz_sperre_id' => $plz_id,
                 'verlag_uid' => $verlag_user -> getUid(),
                 'nid' => $node -> nid,
                 'plz_sperre_until' => strtotime($dateplz)
             );
             
             db_insert('lk_vku_plz_sperre')->fields($array)->execute();
             
             // Log also the Ausgaben, based on the Users Ausgaben
             foreach($ausgaben as $ausgabe){
                 $array2 = array(
                     'plz_ausgabe_id' => $ausgabe,
                     'vku_id' => $this -> getId(),
                     'plz_sperre_id' => $plz_id
                 );
                 
               db_insert('lk_vku_plz_sperre_ausgaben')->fields($array2)->execute();
             }
             
             lk_note("kurzsperre", 'Kampagne ' . $node -> title ." [". $node -> nid ."] wurde für " . $account -> getTitle() . " bis zum " . date("d.m.Y H:i:s"). ' gesperrt');   
          }
  } 
  
  function url(){
      if($this -> is('active') OR $this -> is('new')){
          return $this -> vku_url();
      }
      
      return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/details";
  }

  function userUrl(){
  	return 'user/' . $this -> getAuthor() . "/vku";
  }

  function renewUrl(){
       return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/renew";
  }
  
  function removeUrl(){
     return $this -> vku_url() . "/delete";  
  }    
  
  function downloadUrlPPT(){
      return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/download/ppt";
  }   
  
   function downloadUrl(){
      return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/download";
  }

  function vku_url(){
      return 'vku/' . $this -> getId();
  }
}




/**
 * Class VkuData, saves all the VKU-Relevant Data 
 */
class VkuData {

  var $id;
  var $data;
  var $object_data = array();
  var $categories = array();
  
  function getSetting($id){
     
      if(!isset($this -> data[$id])){
         return ''; 
      }
      
    return $this -> data[$id];
  }

  function VkuData($id = NULL, $defaults = array()){

      if($id){
      	 
      	 $dbq = db_query("SELECT * FROM lk_vku WHERE vku_id='". $id ."'");   
      	 $vku = $dbq -> fetchObject();
         
          if($vku){
                $this -> data = (array)$vku;

                if(!$this -> data["vku_changed"]) $this -> data["vku_changed"] = $this -> data["vku_created"];
                $this -> id = $vku -> vku_id;  
                // load the data
                $dbq = db_query("SELECT * FROM lk_vku_data WHERE vku_id='". $id ."' ORDER BY data_active DESC, data_delta ASC");  
                foreach($dbq as $all){
                    $this -> object_data[$all -> id] = (array)$all;
                }
          } 
      }
      else {
        $vku_id = db_insert('lk_vku')->fields($defaults)->execute(); 
        $this -> id = $vku_id;
      } 

  }

  function takeOver($other){
      foreach($other -> object_data as $item){
          if($item["data_active"] == null) {
              $item["data_active"] = 1;
          }
            
          $func = 'copy_vku_item_' . $item["data_module"];
          if(function_exists($func)){
              $func($this, $item);
          }

          $this -> add($item["data_module"], $item["data_class"], $item["data_delta"], $item["data_active"], $item["data_entity_id"], $item["data_serialized"]);
      }

  }
  
  
  function setPageSerializedSetting($seiten_id, $values){
     
     
     if(!$values){
        $serialized = null; 
     }
     else {
       $serialized = serialize($values);   
     }
     
     $count = db_update('lk_vku_data')
             ->fields(array('data_serialized' => $serialized))
             ->condition('vku_id', $this -> id)
             ->condition('id', $seiten_id)
             ->execute(); 
     
  return $count;    
  }
  
  function setSetting($setting, $value){
      $this -> data[$setting] = $value;
      
      db_update('lk_vku')
             ->fields(array($setting => $value))
             ->condition('vku_id', $this -> id)
             ->execute(); 
    }

  function search($module, $class, $entity_id = 0){
      foreach($this -> object_data as $item){
          if($item["data_module"] == $module AND $item["data_class"] == $class){
             if(!$entity_id) {
                  return $item["id"];
              }      
              
              if($entity_id == $item["data_entity_id"]){
                  return $item["id"];
              }
          }
      }

   return false; 
  }

  function searchAll($module, $class){
      $result = array();  

      foreach($this -> object_data as $item){
          if($item["data_module"] == $module AND $item["data_class"] == $class){
              $result[] = $item;
          }
      }

   return $result; 
  }

  function saveOrder($class, $module, $delta = 0, $entity = 0){
      $id = $this -> search($class, $module, $entity);
      
      if($id){
           $data = $this -> object_data[$id];

           if($data["data_delta"] != $delta){
              $this -> object_data[$id]["data_delta"] = $delta;
              db_query("UPDATE lk_vku_data SET data_delta='". $delta ."' WHERE id='". $id ."'");  
            }
      }
  }

  function remove($class, $module, $entity = 0){
    $id = $this -> search($class, $module, $entity);

    if($id) {
        $this -> removeItem($id);
    }
  }


  function removeItem($id){
    $remove = $this -> object_data[$id];

    //$remove["data_module"];
    $func = "remove_vku_addon_" . $remove["data_module"];
    if(function_exists($func)){
        $func($remove["data_class"], $remove);
    }
    
    db_query("DELETE FROM lk_vku_data WHERE id='". $id ."'");  
    unset($this -> object_data[$id]);
    return true;
  }

  function removeData(){
       // Wen keine Lizenzen      
      $id = $this -> id;
      
      while(list($key, $val) = each($this -> object_data)){
          $this -> removeItem($key);
      }
      
      db_query("DELETE FROM lk_vku WHERE vku_id='".  $id ."'");
      db_query("DELETE FROM lk_vku_data_categories WHERE vku_id='".  $id ."'");
  }

  function changeMediaActive($id, $new_satus){
  	$new_satus = (int)$new_satus;

  	if(isset($this -> object_data[$id])){
  		$this -> object_data[$id]["data_active"] = $new_satus;
  		db_query("UPDATE lk_vku_data SET data_active='". (int)$new_satus ."' WHERE id='". $id ."'");		
  		return true;
  	}
  	else {
  		return false;
  	}
  }
  
  function setMediaOrder($id, $oder){
  	$oder = (int)$oder;

  	if(isset($this -> object_data[$id])){
  		$this -> object_data[$id]["data_delta"] = $oder;
  		db_query("UPDATE lk_vku_data SET data_delta='". $oder ."' WHERE id='". $id ."'");		
  		return true;
  	}
  	else {
  		return false;
  	}
  }


  function add($module, $class, $delta = 0, $active = 1, $entity_id = 0, $data_serialized = '', $data_category = 0){
     
     // Serialize 
     if(is_array($data_serialized)){
        $data_serialized = serialize($data_serialized); 
     }; 
      
     $id = $this -> id;
     $data = array('vku_id' =>  $id, 
                   'data_active' => (int)$active, 
                   'data_module' => $module, 
                    'data_class' => $class, 
                    'data_entity_id' => $entity_id, 
                    'data_created' => time(), 
                    'data_delta' => $delta,
                    'data_serialized' => $data_serialized,
                    'data_category' => $data_category
         ); 
     
     
     db_insert('lk_vku_data')->fields($data)->execute();
     $lastId = Database::getConnection()->lastInsertId();  
      
     $this -> object_data = array();
     $dbq = db_query("SELECT * FROM lk_vku_data WHERE vku_id='". $id ."' ORDER BY data_delta ASC");  
     foreach($dbq as $all){
       $this -> object_data[$all -> id] = (array)$all;
     }
     
     
   return $lastId;  
   }

}

?>