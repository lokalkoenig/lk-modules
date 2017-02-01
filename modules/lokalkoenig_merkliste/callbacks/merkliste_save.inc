<?php


/**
 * Removes a Merkliste
 *
 * @path /merkliste/%/remove, /merkliste/%/save
 * @param int $tid
 */
function lokalkoenig_merkliste_ajax_callback(){

  $nid = 0;
  if(isset($_POST["nid"]) && is_numeric($_POST["nid"])){
    $nid = (int)$_POST["nid"];
  }

  $action = isset($_POST["action"]) ? $_POST["action"] : '';
  $new_name = isset($_POST["new"]) ? $_POST["new"] : '';

  $terms = [];
  if(isset($_POST["terms"]) && is_array($_POST["terms"])){
    $terms = $_POST["terms"];
  }

  $manager = new \LK\Merkliste\UserMerkliste();

  $response = [
    'error' => 0,
    'message' => '',
  ];

  $node = node_load($nid);
  if(!$node){
    $response = [
      'error' => 1,
      'message' => 'Keine Kampagne ausgewählt.',
    ];

    drupal_json_output($response);
    exit;
  }

  // save new one
  if($action === 'save'){
    $saved_terms = $manager -> getTermsFromKampagne($nid);
    foreach($saved_terms as $term){
      if(!in_array($term, $terms)){
        $manager ->removeKampagne($term, $nid);
      }
    }

    if($new_name){
      $manager -> addNewTerm($new_name, $nid);
    }

    foreach($terms as $term){
      $manager ->addKampagne($term, $nid);
    }

    $response['message'] = "Die Kampagne wurde in Ihren Merklisten gespeichert.";
  }
  elseif($action === 'load'){
    $response['load_terms'] = [];

    $load_terms = $manager ->getTermsFromKampagne($nid);;
    foreach($load_terms as $term){
      $merkliste = $manager ->loadMerkliste($term);
      $response['load_terms'][$term] = $merkliste->getName();
    }

    $response['message'] = 'Loaded terms';
  }
  elseif($action === 'remove'){
    $terms = $manager ->getTermsFromKampagne($nid);
    
    foreach ($terms as $term){
      $manager->removeKampagne($term, $nid);
    }

    $response['message'] = 'Die Kampagne wurde von Ihren Merklisten entfernt.';
  }

  if($response['message'] === ''){
    $response['error'] = 1;
  }

  $response['total'] = $manager ->getCount();
  $response['terms'] = $manager ->getTerms();

  drupal_json_output($response);
  exit;
}
