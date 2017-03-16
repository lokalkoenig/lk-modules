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

    $fields = [];
    $rendered = '';
    if(isset($_GET['case']) AND isset($array['cases'][0][$_GET['case']])):
      $case = $_GET['case'];
      $test = new $case($_GET);
      $GLOBALS['LK_LOG'] = FALSE;

      $rendered .= $test -> run();
      
      if(method_exists($test, 'getForm')){
        $fields = $test -> getForm();
      }
      
      lk_set_subtitle($array['cases'][0][$_GET['case']]);

      if(isset($GLOBALS['LK_LOG_RUN'])){
        drupal_set_message('<strong>Logbuch</strong>', 'warning');

        foreach($GLOBALS['LK_LOG_RUN'] as $message){
          drupal_set_message($message, 'warning');
        }

        //dpm($GLOBALS['LK_LOG_RUN']);
      }
    endif;

    
    $form = drupal_get_form('\\LK\\Tests\\Controller\\lokalkoenig_admin_show_tests_case_form', array("0" => '- Select a case -') + $array['cases'][0], $fields);
    $rendered = drupal_render($form) . $rendered;
    
    return $rendered;
  }
}


function lokalkoenig_admin_show_tests_case_form($form, $form_state, $cases, $fields){
  
  $form['#method'] = 'get';
   
  $form['case'] = array(
    '#type' => 'select',
    '#title' => t('Test-Case'),
    '#options' => $cases,
    '#description' => ('Ein Test-Case testet die Funktionalitaet des Portals'),
  );

  while(list($key, $val) = each($fields)){
    $form[$key] = $val;
  }

  if(isset($_GET['case']) AND isset($cases[$_GET['case']])){
    $form['case']['#default_value'] = $_GET['case'];
  }

  $form['submit'] = array('#type' => 'submit', '#value' => t('Go'));

  return $form;
}

function lokalkoenig_admin_show_tests_case_form_submit($form, $form_state){
  form_state_values_clean($form_state);
  drupal_goto($_GET['q'], array('query' => $form_state['values']));
}