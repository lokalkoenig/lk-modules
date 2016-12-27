<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . "/func_vku2_generate.php";


function _vku2_callback($vku_id){
    
    $obj = $_POST["save"];
    $vku = new VKUCreator($vku_id);
    $obj["msg"] = null;
    $obj["signature_error"] = false; 
    
    // Preview
    if(isset($_GET["preview"]) AND $_GET["preview"] == 1){
        _vku2_callback_preview($vku);
        exit;
    }
    
    if($obj["signature"] != $vku -> get("vku_changed")){
       $obj["signature_error"] = true; 
       drupal_json_output($obj);
       drupal_exit();  
    }    
    
    
    // VKU-Title & Template-Title
    if(isset($obj["vku_template_title"]) AND $vku -> getStatus() == 'template'){
            $vku ->set('vku_title', $obj["vku_template_title"]);
            $vku ->set('vku_template_title', $obj["vku_template_title"]);
            
            $obj["vku_title"] = $vku -> get('vku_title');
            
            if(empty($obj["vku_title"])){
                $obj["vku_title"] = 'Ohne Titel';
            }
    }
    
    // save Title
    if($obj["type"] == 'title'){
        $vku->set('vku_title', $obj["vku_title"]);
        $vku->set('vku_company', $obj["vku_company"]);
        $vku->set('vku_untertitel', $obj["vku_untertitel"]);
        
        $status = $vku ->getStatus();
        if($status == 'new'){
            $vku ->setStatus('active');
            $vku ->isCreated();
        }
        
        if(!empty($obj["template"])){
             $new_vku = vku2_generate_takeover_vorlage($vku, $obj["template"]);
             $pages = vku2_generate_category_pages($new_vku);
             $generated = theme("vku2_items", array("items" => $pages, 'vku' => $new_vku));
             $obj["renew_items"] = $generated;
        }
    }
    
    $line = array();
    $new = null;
    $replace_sid = null;
    
    
    
    
    // Save data
    if($obj["type"] == 'save'){
        
        
        
        foreach($obj["data"] as $item){
            $sid = $item["sid"];
            $items = explode("-", $item["sid"]);
          
            // new
            if($item["status"] == 3){
                $replace_sid =  $sid;
                
                if($items[0] == 'default'){
                    $cid = $vku ->setDefaultCategory('other', 0);
                    $id = $vku -> data -> add('default', $items[1], 1, 1, 0, NULL, $cid);
                    $sid = $cid . "-" . $id; 
                    $new = $sid;
                }
                
                if($items[0] == 'kampagne'){
                   $id = $vku -> addKampagne($items[1]);
                   $sid = $id;
                   $new = $sid;
                   // Save Sub-Items
                }
            }
            
            $cid = $items[0];
            $pid = $items[1];
            
            // disable
            if($item["status"] == 0){
                $vku ->setPageStatus($pid, false);
            }
            else {
                $vku ->setPageStatus($pid, true);
            }
            
            // delete
            if($item["status"] == 2){
                $vku ->removePage($pid);
                continue;
            }
            
            
            
            
            
            $line[$sid] = array();
            
            // Load Container
            $category = $vku -> getDefaultCategory($cid);
            
            if($category && in_array($category -> category, array('print', 'online'))){
                if(!isset($item["children"])):
                    $item["children"] = array();
                 endif;   
                    

                // Check for Children
                foreach($item["children"] as $child){
                    $items2 = explode("-", $child["sid"]);
                    $pid2 = $items2[1];
                    
                    // delete
                    if($child["status"] == 2){
                        $vku ->removePage($pid2);
                        $obj["item-remove"] = $pid2;
                        
                        continue;
                    } 
                    
                    // new item
                    if($child["status"] == 3){
                        $id = $vku -> data -> add('default', $pid2, 1, 1, 0, NULL, $cid);
                        $replace_sid =  $child["sid"];
                        $new = $cid . "-" . $id;
                        $pid2 = $id;
                    }
                    
                    $sid2 = $cid . '-' . $pid2; 
                    $line[$sid][] = $sid2;
                }
            }
            
            
            // Save Child-Info of Kampagne when not new
            if($category && in_array($category -> category, array('kampagne')) AND $item["status"] != 3){
                _vku2_callback_save_kampagne($vku, $pid, $item["children"]);
            }
            
        }
        
        
        $cid_order = 0;
        $page_order = 0;
        
        $obj['bla'] = $line;
        
        // save order
        while(list($key, $val) = each($line)){

            $explode = explode("-", $key);
            $cid = $explode[0];
            $pid = $explode[1];

            $vku -> setDefaultCategoryOrder($cid, $cid_order);
            $cid_order++;
            
            if($pid){
                 $vku -> setPageOrder($pid, $page_order);
                 $page_order++;
            }
            
            // means, there are Sub-Items
            if($val){
                foreach($val as $item){
                     $explode = explode("-", $item);   
                     $pid2 = $explode[1];
                     $vku -> setPageOrder($pid2, $page_order);
                     $page_order++;
                }
            }
        }
    }
    
    
    
    
    $vku_updated = new VKUCreator($vku ->getId());
    $pages = vku2_generate_category_pages($vku_updated);
    
    $obj["replace"] = null;
    
    // When there is a new Item we go through all the Items to find the item we need to replace
    if($new){
      $explode = explode("-", $new);
      $cid = $explode[0];
      $pid = $explode[1];
       
      while(list($key, $val) = each($pages)){
        if($cid == $key){
            
            $obj["test"] = $pid;
            
            // PID can be 0 on print or online
            if($pid == $val["id"]){
                $obj["replace_sid"] = $replace_sid;
                $obj["replace"] = theme("vku2_item", array("item" => $val, 'vku' => $vku_updated));
            }   
            else {
                while(list($key2, $val2) = each($pages[$key]["children"])){
                    if($pid == $val2["id"]){
                        $obj["replace_sid"] = $replace_sid;
                        $obj["replace"] = theme("vku2_item", array("item" => $val2, 'vku' => $vku_updated));    
                    }
                }
            }
        }
      }
    }
    
    
    $obj["error"] = 0;
    
    // Final check
    if($obj["type"] == 'savelast' OR $obj["type"] == "save" OR $obj["type"] == "finalize"):
      // Node count
      $nodes = $vku_updated ->getKampagnen();
      if(count($nodes) > 3){
            $obj["msg"] = 'Sie haben zu viele Kampagnen in Ihrer Verkaufsunterlage. Bitte reduzieren Sie die Anzahl auf maximal 3 Kampagnen.';
            $obj["error"] = 1;
      }  
      else {
          // check for Kampagnen die nicht lizenziert werden können
          foreach($nodes as $nid){
            if(!vku2_node_can_add($nid, $vku -> getAuthor())){
                $obj["error"] = 1;
                $node = node_load($nid);
                $obj["msg"] = 'Die Kampagne <strong>' . $node -> title . "</strong> kann im Moment nicht lizenziert werden. Bitte löschen Sie diese aus Ihrer Verkaufsunterlage."; 
                break;
            } 
          }
      }
      
      
      // Check for 0 Pages
      $count = 0;
      $pages = $vku_updated -> getPages();
      foreach($pages as $page){
          if($page["data_active"]){
              $count++;
          }
      }
      
      if($count == 0):
          $obj["error"] = 1;
          $obj["msg"] = 'Sie haben im Moment keine aktivierten Seiten in Ihrer Verkaufsunterlage.';
      endif;
    endif;
    
    
    if($obj["type"] == "finalize" AND $obj["error"] == 0){
        
        // Create PDF
        require_once(__DIR__ ."/func_generate_vku.php");
        $pdf = _vku_generate_final_vku_v2($vku_updated);
        
        $obj["pdf"] = $pdf;
        
        $ppt = true;
        if(vku_is_update_user_ppt()):
            // Create PPT
            require_once(__DIR__ ."/../ppt/test.php");
            $ppt = vku_ppt($vku_updated);
        
            $obj["ppt"] = $ppt;
        endif;
        
        if(!$pdf OR !$ppt){
          $obj["error"] = 1;
          $obj["msg"] = 'Die Verkaufsunterlage konnte nicht generiert werden.';   
        }   
        else {
            $vku_updated -> setStatus('ready');

            // Sets the PLZ-Sperre for Short time 
            $vku_updated -> setShortPlzSperre();
            
             // Generate PDF
            $obj["pdf_download_link"] = url($vku_updated ->downloadUrl());
            $obj["pdf_download_size"] = format_size($vku_updated -> get("vku_ready_filesize"));

            $obj["vku_link"] = url($vku ->url());
            $obj["ppt_download_link"] = null;
            $obj["ppt_download_size"] = 0;
                
            
            if(vku_is_update_user_ppt()):
                 // Generate PPT
                $obj["ppt_download_link"] = url($vku_updated ->downloadUrlPPT());;
                $obj["ppt_download_size"] = format_size($vku_updated -> get("vku_ppt_filesize"));
            endif;
       }    
    }    
    
    $time = $vku_updated ->update();
    $obj["signature"] = $time;
    $obj["changed"] = format_date($time, 'short');
    drupal_json_output($obj);
    drupal_exit();  
}


function _vku2_callback_title($vku, $obj){
    
    
}

function _vku2_callback_save_kampagne($vku, $pid, $children){
   
   $save = array(); 
    
   foreach($children as $child){
      $explode = explode("-", $child["sid"]);
      $child_id = $explode[1]; 
       
      if($child["status"] == 0){
          $save[$child_id] = 1;
      }              
      else {
          $save[$child_id] = 0;
      }              
   }
   
   $vku -> data -> setPageSerializedSetting($pid, $save);
}


function _vku2_callback_preview(VKUCreator $vku){
    
  $author = $vku -> getAuthor();
  $id = $vku -> getId();
  
  $pdf = vku_generate_get_pdf_object($vku); 
  $fn = $id . "_" . time() . ".pdf";
  $pages = $vku -> getPages();


  while(list($key, $page) = each($pages)){
      
      if(!$page["data_active"]) {
          continue;
      }
      
      $mod = $page["data_module"];  
      $func_name = 'vku_generate_pdf_' . $mod;

      if(function_exists($func_name)){
        $func_name($vku, $page, $pdf);
      }
  }
  
  
  $vku ->logEvent('pdf-preview', 'Vorschau PDF wurde generiert.');
  $pdf->Output();
  exit;
  //return $dir . "" .  $fn; 
}