<?php

namespace LK\Admin\Data;

/**
 * Description of PLZChooser
 * 
 * OpenGeo DB
 * http://www.lichtblau-it.de/downloads
 *
 * @author Maikito
 */
class PLZChooser {

  public static function get($verlag_id = 0){

    $bundeslaender = [];
    $landkreise = [];

    // if Verlag
    if($verlag_id){
      $verlag = \LK\get_user($verlag_id);
      $comma = $verlag -> getPlzFormatted();
      $explode = explode(", ", $comma);

      $dbq = db_query("SELECT c.state_id, c.county_id
           FROM
             opengeodb_zipcode z,
             opengeodb_city c
           WHERE
             c.id= z.city_id AND
           z.zipcode IN ('". implode("','", $explode) . "')");
        foreach($dbq as $all){
           $bundeslaender[$all -> state_id] = $all -> state_id;
           $landkreise[$all -> county_id] = $all -> county_id;
        }
    }

    $states = array('<ul style="max-height: 400px; overflow: auto;" id="plzselect">');
    if($bundeslaender){
      $dbq = db_query("SELECT * FROM opengeodb_state WHERE id IN (". implode(",", $bundeslaender) . ") ORDER BY name");
    }
    else {
      $dbq = db_query("SELECT * FROM opengeodb_state ORDER BY name");
    }

    foreach($dbq as $state){
      $states[] = '<li><span class="state">' .  $state -> name . '</span>';
      $states[] = '<ul>';

      if($landkreise){
        $dbq2 = db_query("SELECT * FROM opengeodb_county WHERE state_id='". $state -> id . "' AND id IN (". implode(",", $landkreise) .") ORDER BY name");
      }
      else {
        $dbq2 = db_query("SELECT * FROM opengeodb_county WHERE state_id='". $state -> id . "' ORDER BY name");
      }

      foreach($dbq2 as $county){
        $states[] = '<li><span class="county">' .  $county -> name . '</span>';
        $states[] = '<ul>';
        $dbq3 = db_query("SELECT c.name, z.zipcode FROM opengeodb_zipcode z, opengeodb_city c
              WHERE c.id=z.city_id AND c.county_id='". $county -> id . "' ORDER BY z.zipcode ASC");

        foreach($dbq3 as $plz){
           $states[] = '<li><span class="plz" plz="'. $plz -> zipcode .'">' .  $plz -> name . ' ('. $plz -> zipcode .')</span></li>';
        }

        $states[] = '</ul>';
        $states[] = '</li>';
      }

      $states[] = '</ul>';
      $states[] = '</li>';
    }

    $states[] = '</ul>';
    $out = implode("", $states);

    print $out;
    drupal_exit();
  }
}
