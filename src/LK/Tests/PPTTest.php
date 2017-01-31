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
    global $user;

      $this ->printLine('', 'Suche letzte aktive VKU / [Eine X-beliebige kann per GET Parameter erzeugt werden.]');

      $vku_id = \LK\VKU\VKUManager::getActiveVku($user -> uid);
      if(!$vku_id){
         drupal_set_message('Sie haben keine Aktive VKU');
         return ;
      }

      $vku = new \VKUCreator($vku_id);
      $this ->printLine('VKU', $vku ->getTitle());
      $this ->printLine('Author', \LK\u($vku ->getAuthor()));
      
      $manager = new \LK\VKU\PageManager();
      $file_name = 'test-vku';
      file_prepare_directory($mydir, FILE_CREATE_DIRECTORY);
      
      $mydir = 'public://test';
      $dir = drupal_realpath($mydir);
      $pptx = $manager ->generatePPTX($vku);
      $fn = \LK\PPT\PPTX_Loader::save($pptx, $dir, $file_name);
      $size = filesize($dir . '/' . $fn);
      $this ->printLine('PPTX', l($fn, 'sites/default/files/test/' . $fn));
      $this ->printLine('Size', format_size($size));
    }
}
