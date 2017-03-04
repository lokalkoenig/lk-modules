<?php

/**
 * @file
 * Generates a Test-PDF
 *
 * @path user/%/testpdf
 */

function lokalkoenig_user_test_pdf($account){

  $obj = \LK\get_user($account -> uid);

  if(!$obj || !$obj ->isVerlag()){
    drupal_goto('user');
  }
  
  $overwrite = ['vku_hintergrundfarbe', 'vku_hintergrundfarbe_titel', 'vku_vordergrundfarbe_titel'];
  
  foreach($overwrite as $id) {
    $obj->settings[$id] = $_GET[$id];
  }
  
  $pdf = new \LK\User\Verlag\TestPDF($obj);
  $pdf->render();
}