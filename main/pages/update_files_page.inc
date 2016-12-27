<?php

$update_files = 0;


function lokalkoenig_admin_update_files_page(){
    $form = drupal_get_form("lokalkoenig_admin_files_update_form");
return render($form);    
}


function lokalkoenig_admin_files_update_form($form, &$form_state){
global $update_files;
    
   
    
    
    $form["nid"] = array(
        '#type' => 'textfield',
        '#title' => "Node-Nid (>)",
        '#required' => true
    );
    
    $form["submit"] = array(
        '#type' => 'submit',
        '#value' => "Go!" 
    );
    
return $form;    
}

function lokalkoenig_admin_files_update_form_submit($form, $form_state){
global $update_files;
    
    $x = 0;
    $nid = (int)$form_state["values"]["nid"];
    $dbq = db_query("SELECT nid FROM node WHERE type='kampagne' AND nid >='". $nid ."' ORDER BY nid ASC");
    foreach($dbq as $all){
        $node = node_load($all -> nid);
        
        if($x == 100){
            drupal_set_message("Please run again. 100 Nodes pass. [LAST-NID: ". $all -> nid ."]");
            break;
        }
        
        
        if($update_files >= 200){
            drupal_set_message("Please run again [LAST-NID: ". $all -> nid ."]");
            break;
        }
        
        $x++;
        
        _lokalkoenig_admin_update_node_files($node);
    }   
}



function _lokalkoenig_admin_update_node_files($node){
    foreach($node -> medien as $media):
        foreach($media->field_medium_varianten['und'] as $file){
            $file_object = file_load($file["fid"]);
            _lokalkoenig_move_file($file_object);
        }
    endforeach;
}


function _lokalkoenig_move_file($file){
global $update_files;  
 $destination = str_replace('private:', 'public:', $file->uri);
 $dirname = drupal_dirname($destination);
 
 if($file->uri == $destination){
     return ;
 }
 
 if (file_prepare_directory($dirname, FILE_CREATE_DIRECTORY|FILE_MODIFY_PERMISSIONS)) {
     if(file_move($file, $dirname, FILE_EXISTS_RENAME)){
          $update_files++;
     }
     //drupal_set_message("FID " . $file -> fid . " was moved");
 }  
}


