<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * @path 
 */

function newDocs(){
    require_once __DIR__ . '/DynamicDocument.php';
    
    $obj = new DynamicDocument\Manager();
    
    $categories = $obj -> getLayoutsHTML();
    
    //$obj ->create('print', '1col2col');
    //$obj -> saveDocument(array("title"));
    
    //$get = $obj ->getDocument();
   
    
    
    
    
    
return theme("vku2doc_overview", array("layouts" => $categories));    
}





/** Hook 2 get the available sonstiges Dokumente */
function vkuconnection_vku2_add_sonstiges($items){
    $verlag_uid = \LK\current_verlag_uid();
    $obj = new DynamicDocument\Manager();
    $obj -> getTemplates('sonstiges', $verlag_uid);
}

