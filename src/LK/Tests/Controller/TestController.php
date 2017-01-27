<?php

/*
 * @path backoffice/test
 */

namespace LK\Tests\Controller;

/**
 * Description of TestController
 *
 * @author Maikito
 */
class TestController {

  public static function run(){
    $file = file_get_contents(__DIR__ . '/../../../../tests.json');
    $array = json_decode($file, TRUE);
    $form = drupal_get_form('\\LK\\Tests\\Controller\\lokalkoenig_admin_show_tests_case_form', array("0" => '- Select a case -') + $array['cases'][0]);
    $rendered = drupal_render($form);

    if(isset($_GET['case']) AND isset($array['cases'][0][$_GET['case']])):
      $case = $_GET['case'];
      $test = new $case();
      $rendered .= $test -> run();
      lk_set_subtitle($array['cases'][0][$_GET['case']]);
    endif;

    return $rendered;
  }
}


function lokalkoenig_admin_show_tests_case_form($form, $form_state, $cases){
  $form['case'] = array(
    '#type' => 'select',
    '#title' => t('Test-Case'),
    '#options' => $cases,
    '#description' => ('Ein Test-Case testet die Funktionalitaet des Portals'),
  );

  if(isset($_GET['case']) AND isset($cases[$_GET['case']])){
    $form['case']['#default_value'] = $_GET['case'];
  }

  $form['submit'] = array('#type' => 'submit', '#value' => t('Go'));

  return $form;
}

function lokalkoenig_admin_show_tests_case_form_submit($form, $form_state){
    drupal_goto($_GET['q'], array('query' => array('case' => $form_state['values']['case'])));
}