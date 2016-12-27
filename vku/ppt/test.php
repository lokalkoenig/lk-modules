<?php
use LK\PPT\LK_PPT_Creator;

function vku_ppt(VKUCreator $vku){
    
    $ppt = new LK_PPT_Creator($vku -> getId());
    $ppt -> process();
    
    $dir = 'sites/default/private/vku';
    
    $fn = $ppt -> write($dir, $vku -> getId());
    $file_path = 'sites/default/private/vku/' . $fn;
    
    $vku -> set('vku_ppt_filename', $fn);
    $file_size = filesize($file_path);
    $vku -> set('vku_ppt_filesize', $file_size);
    
    return true;
}

function vku_test_ppt($node, $filepath, $fn){
    
   // create a tempory VKU 
   $vku = new VKUCreator(0);
   $ppt = new LK_PPT_Creator($vku ->getId());
   $ppt -> testNode($node);
   $ppt -> write($filepath, $fn);     
   $vku ->remove();
}




