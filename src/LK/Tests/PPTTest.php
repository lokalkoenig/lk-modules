<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LK\Tests;

use LK\PPT\LK_PPT_Creator;

/**
 * Description of PPTTest
 *
 * @author Maikito
 */
class PPTTest extends TestCase {
    //put your code here
    
    function build() {
        
        $this ->printLine('', 'Suche letzte aktive VKU / [Eine X-beliebige kann per GET Parameter erzeugt werden.]'); 
        
        $id = vku_get_active_id();
        if(!$id){
           drupal_set_message('Sie haben keine Aktive VKU');
           return ;
        }
       
        $vku = new \VKUCreator($id);
        
        // get the last VKU-ID
        $this ->printLine('VKU', $vku ->getTitle());
        $this ->printLine('Author', \LK\u($vku ->getAuthor()));
        
        $ppt = new LK_PPT_Creator($vku -> getId());
        $ppt -> process();
        
        
        $mydir = 'public://test'; 
        file_prepare_directory($mydir, FILE_CREATE_DIRECTORY);
        $dir = drupal_realpath($mydir);
        $fn = $ppt -> write($dir, $vku -> getId());
        
        $size = filesize($mydir . '/' . $fn);
        $this ->printLine('PPTX', l($fn, 'sites/default/files/test/' . $fn));
        $this ->printLine('Size', format_size($size));
    }
}
