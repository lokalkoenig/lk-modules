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
    db_merge('lk_user_settings')
      ->key(array('uid' => $this->verlag_id, 'user_type' => 'verlag', 'settings_key' => $key))
      ->fields(array('settings_value' => $val))
      ->execute();
  }

  /**
   * Gets the Values of the Verlag
   *
   * @return array
   */
  function getVars(){

    $values = [];
    $dbq = db_query("SELECT settings_value, settings_key FROM lk_user_settings WHERE uid=:uid AND user_type='verlag'", [':uid' => $this->verlag_id]);
    foreach ($dbq as $all) {
      $values[$all -> settings_key] = $all -> settings_value;
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
