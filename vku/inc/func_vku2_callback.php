<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . "/func_vku2_generate.php";

/**
 * REST Callback VKU 2.0
 * 
 * @param type $vku_id
 */
function _vku2_callback($vku_id){
    
    $obj = $_POST["save"];
    $vku = new VKUCreator($vku_id);
    
    // Preview
    if(isset($_GET["preview"]) AND $_GET["preview"] == 1){
        _vku2_callback_preview($vku);
        exit;
    }
    
    $manager = new \LK\VKU\VKU2($vku, $_POST['save']);
    $manager ->checkSignature();
    
    // VKU-Title & Template-Title
    if(isset($obj["vku_template_title"]) AND $vku -> getStatus() == 'template'){
      $manager -> saveTemplateData();
    }
    
    // Save Title
    if($obj["type"] == 'title'){
      $manager->saveTitle();
    }
    
    if($obj["type"] == 'save'){
      $manager->saveVKUPages();
    }
   
    
    
    $obj["error"] = 0;
    $obj["msg"] = null;
    $obj["signature_error"] = false; 
    
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
    
    // Nothing happend
    $manager->sendJSON([]);
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