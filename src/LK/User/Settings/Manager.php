<?php

namespace LK\User\Settings;


/**
 * Description of Manager
 *
 * @author Maikito
 */
class Manager {

  protected $verlag_id = null;

  function __construct(\LK\Verlag $verlag) {
    $this->verlag_id = $verlag ->getUid();
  }


  /**
   * Sets a setting
   *
   * @param string $key
   * @param string $val
   */
  function setVar($key, $val){

    $fields = array('settings_value' => $val);
    if(is_array($val)){
      $fields = array('settings_value' => serialize($val), 'settings_serialized' => 1);
    }

    db_merge('lk_user_settings')
        ->key(array('uid' => $this->verlag_id, 'user_type' => 'verlag', 'settings_key' => $key))
        ->fields($fields)
        ->execute();
   
  }

  /**
   * Gets the Values of the Verlag
   *
   * @return array
   */
  function getVars(){

    $values = [];
    $dbq = db_query("SELECT settings_value, settings_key, settings_serialized FROM lk_user_settings WHERE uid=:uid AND user_type='verlag'", [':uid' => $this->verlag_id]);
    foreach ($dbq as $all) {
      if($all -> settings_serialized){
        $values[$all -> settings_key] = unserialize($all -> settings_value);
      }
      else {
        $values[$all -> settings_key] = $all -> settings_value;
      }
    }

    return $values;
  }

  /**
   * Adds the Submit handler
   * 
   * @param array $form
   * @param array $form_state
   * @param \LK\Verlag $verlag
   * @return string
   */
  public static function toForm($form, &$form_state, \LK\Verlag $verlag){
     $form['#verlag'] = $verlag;
     $form['#submit'][] = 'lk_user_verlag_settings_form_submit';
     $form['submit'] = array('#type' => 'submit', '#value' => 'Speichern');
     return $form;
  }
}
