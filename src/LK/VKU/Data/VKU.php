<?php
namespace LK\VKU\Data;

/**
 * Description of VKU
 *
 * @author Maikito
 */
class VKU {
  use \LK\Stats\Action;
  use \LK\Log\LogTrait;

  var $vku_data = [];
  var $LOG_CATEGORY = 'VKU';
  var $id = NULL;
 
  /**
   * Constructs a VKU
   *
   * @param \stdClass $data
   */
  function __construct(array $data) {
    $this->vku_data = $data;
    $this->id = $data['vku_id'];
  }

  /**
   * Current User has access
   * 
   * @global \stdClass $user
   * @return boolean
   */
  function hasAccess(){
    global $user;

    // is Moderator
    if(lk_is_moderator()) {

      return TRUE;
    }

    // Only the Author has Access
    $author = $this -> getAuthor();
    if($author == $user -> uid) {

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Is state
   *
   * @param string $status
   * @return boolean
   */
  function is($status = NULL) {

    if(is_null($status)) {
      return $this->getId();
    }

    if($this->getValue('vku_status') === $status) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Gets a Data from the VKU
   *
   * @param key $value
   * @param boolean $plain
   * @return string
   */
  function get($value, $plain = true){
    if($plain === true){
      return check_plain($this -> getValue($value));
    }

    return $this ->getValue($value);
  }

  /**
   * Updates the current VKU
   *
   * @return int
   */
  function update(){
    $this -> set("vku_changed", time());
    return $this -> get("vku_changed");
  }
  
  /**
   * Gets the ID of the VKU
   * 
   * @return int
   */
  function getId() {
    return $this->id;
  }

  /**
   * Sets a DB-Value
   *
   * @param string $key
   * @param string $value
   * @return string
   */
  function set($key, $value = NULL){
    return $this -> setValue($key, $value);
  }

 /**
  * Gets back an length trimmed title
  *
  * @param Integer $length
  * @param Boolan $html
  * @return string
  */
  function getTitleTrimmed($length = 20, $html = TRUE){

    $title = $this -> get("vku_title");
    if(strlen($title) > $length){
      $substriged = substr($title, 0, $length);

      if(!$html):
        $title = $substriged;
      else:
        $title = '<span title="'. $title .'">' . $substriged . "...</span>";
      endif;
    }
    
    if(!$title){
      $title = '<em>Neue Verkaufsunterlage</em>';
    }

    return $title;
  }

  /**
   * Gets the Access to the Data-Maintenance
   *
   * @return \LK\VKU\Data\VKUUserManager
   */
  function getDataAdmin() {
    return new \LK\VKU\Data\VKUUserManager($this);
  }
  
  /**
   * Sets a Short PLZ-Sperre
   */
  function setShortPlzSperre() {
    $this->getDataAdmin()->setShortPlzSperre();
  }
  
  /**
   * Removes PLZ-Sperre/n
   */
  function removePLZSperren() {
    $this->getDataAdmin()->removePLZSperren();
  }

  /**
   * Has PLZ-Sperre
   */
  function hasPlzSperre() {
    return $this->getDataAdmin()->hasPlzSperre();
  }

  /**
   * Sets the new Status
   *
   * @param type $status
   */
  function setStatus($status){

    // VKU is completed
    if($status === 'ready'){
      $this->setAction('vku-completed', $this ->getId());
      \LK\Stats::countGeneratedVKU($this);
    }

    if($status === 'deleted'){
      $this -> removePLZSperren();
    }

    $this->set('vku_status', $status);
  }

  /**
   * Gets a Setting
   *
   * @param string $key
   * @return mixed
   */
  function getValue($key){
     return $this->vku_data[$key];
  }

  /**
   * Sets a Value
   *
   * @param type $key
   * @param type $value
   */
  function setValue($key, $value){
    db_update('lk_vku')
    ->fields([$key => $value])
    ->condition('vku_id', $this->id)
    ->execute();

    $this->vku_data[$key] = $value;

    return $value;
  }

  /**
   * Logging the VKU as created
   */
  function isCreated(){
    \LK\Stats::countVKU($this);
  }
  
  /**
   * Weather the VKU is active or template
   *
   * @return boolean
   */
  function isActiveStatus(){

    $status = $this ->getStatus();

    if(in_array($status, ['active', 'template'])){
      return TRUE;
    }

    return FALSE;
  }
  
  /**
   * Gets the Kampagnen-Count
   * 
   * @return int
   */
  function countKampagnen(){
    $kampagen = $this -> getKampagnen();

    return count($kampagen);
  }

  /**
   * Checks if VKU has the given Kampagne
   *
   * @param int $nid
   */
  function hasKampagne($nid) {

    $dbq = db_query("SELECT count(*) as count FROM lk_vku_data WHERE vku_id=:vku_id AND data_class='kampagne' AND data_entity_id=:nid", [
      ':nid' => $nid,
      ':vku_id' => $this->id,
    ]);
    $result = $dbq -> fetchObject();
    
    if($result-> count == 0) {

      return FALSE;
    }

    return TRUE;
  }

  /**
   * Adds a new Kampagne
   *
   * @param int $nid
   * @return boolean|string
   */
  function addKampagne($nid){
    
    if($this->hasKampagne($nid)) {

      return FALSE;
    }

    $manager = $this->getDataAdmin();
    $this->setAction('vku-add-node', $nid);
    $this->logVerlagEvent("Kampagne wurde hinzuguefügt", ['nid' => $nid]);

    $this -> update();
    return $manager->addKampagne($nid);
  }

  /**
   * Gets the raw Data of all the Kampagnen
   *
   * @todo Check if this is in use
   * @return array
   */
  function getKampagnen() {

    $results = [];
    $dbq = db_query("SELECT data_entity_id FROM lk_vku_data WHERE data_class='kampagne' AND vku_id=:vku_id ORDER BY data_delta ASC", [':vku_id' => $this->id]);
    while($all = $dbq -> fetchObject()) {
      $results[] = $all -> data_entity_id;
    }

    return $results;
  }

  /**
   * VKU can be Deleted
   *
   * @return boolean
   */
  function isDeleteAble() {

    $status = $this->getStatus();

    if(in_array($status, ["purchased", "purchased_done"])) {

      return FALSE;
    }

    return TRUE;
  }

  /**
   * Gets the current state
   *
   * @return string
   */
  function getStatus(){
    return $this -> get("vku_status");
 }

 /**
  * Gets the Author-ID
  *
  * @return int
  */
 function getAuthor(){
  return $this -> get("uid");
 }

 /**
  * Gets the Object of the Author
  *
  * @return \LK\User
  */
 function getAuthorObject(){
   return \LK\get_user($this->getAuthor());
 }

 /**
   * Logs an Event inside an VKU
   *
   * @param String $type
   * @param String $message
   *
   * @return String Message

   */
  function logEvent($type, $message){
    $this->logNotice($message, ['category' => $type, 'vku' => $this]);
    return $message;
  }

  /**
   * Logs a Verlag-Event
   *
   * @param type $message
   * @return type
   */
  function logVerlagEvent($message){
    $this->logVerlag($message, ['vku' => $this]);

    return $message;
  }

  /**
   * Gets the URL to the VKU
   *
   * @return string
   */
  function url(){
    if($this -> is('active') OR $this -> is('new')){

      return $this -> vku_url();
    }

    return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/details";
  }

  /**
   * Gets the Link to the Users VKU-Page
   *
   * @return string
   */
  function userUrl(){
    return 'user/' . $this -> getAuthor() . "/vku";
  }

  /**
   * Gets the Link to renew the VKU
   *
   * @return string
   */
  function renewUrl(){
    return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/renew";
  }

  /**
   * Link to Remove the VKU
   *
   * @return string
   */
  function removeUrl(){
    return $this -> vku_url() . "/delete";
  }

  /**
   * Link to Download the PPT
   *
   * @return string
   */
  function downloadUrlPPT(){
    return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/download/ppt";
  }

  /**
   * Link to Download the PDF
   *
   * @return string
   */
  function downloadUrl(){
    return 'user/' . $this -> getAuthor() . "/vku/" . $this -> getId() . "/download";
  }

  /**
   * Link to VKU-Editor
   *
   * @return type
   */
  function vku_url(){
      return 'vku/' . $this -> getId();
  }

  /**
   * Gets the Short-Description of the VKU
   *
   * @return string
   */
  function getTitle(){

    $title = array();
    $title[] = '<strong>' . $this -> get("vku_title") . '</strong>';

    $company = $this -> get("vku_company");
    if($company) {
      $title[] = ' /  ' . $company;
    }

    $changed = $this -> get("vku_changed");
    if($changed){
      $title[] = ' (zuletzt geändert am: ' . format_date($changed, 'custom', 'd.m.Y') .')';
    }

    $count = $this -> countKampagnen();
    if($count){
      $title[] = ' <br />Kampagnen: '. $count;
    }

    return implode("", $title);
  }

  /**
   * Checks if Kampagne has Lizenzen
   *
   * @return boolean
   */
  function hasLizenzen(){
    $id = $this -> getId();

    $test = db_query("SELECT count(*) as count FROM lk_vku_lizenzen WHERE vku_id=:id", [':id' => $id]);
    $result = $test -> fetchObject();
    
    if($result -> count == 0){
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Gets the Lizenzen for the VKU
   *
   * @return array
   */
  function getLizenzen(){

    $manager = new \LK\Kampagne\LizenzManager();

    $lizenzen = array();
    $dbq = db_query("SELECT id FROM lk_vku_lizenzen WHERE vku_id='". $this -> getId() ."'");
    foreach($dbq as $all){
      $lizenz = $manager ->loadLizenz($all -> id);
      $lizenzen[] = $lizenz -> getTemplateData();
    }

    return $lizenzen;
  }

  /**
   * Removes the VKU-Data
   *
   * @return boolean
   */
  function remove(){

    // We don't DELETE the VKU, when there is a Lizenz
    if($this -> hasLizenzen()){
      $this ->logEvent('remove-cron-cancel', "Can not be deleted through there are Licences");

      return FALSE;
    }

    // Check for files
    $id = $this->getId();
    $dir = \LK\VKU\Export\Manager::save_dir;
    
    if(file_exists($dir . '/' . $id . '.pdf')) {
      unlink($dir . '/' . $id . '.pdf');
    }

    if(file_exists($dir . '/' . $id . '.pptx')) {
      unlink($dir . '/' . $id . '.pptx');
    }

    $this -> removePLZSperren();
    $this ->logEvent('vku_remove', "VKU-Daten entferenen");

    db_query("DELETE FROM lk_vku WHERE vku_id=:id", [':id' => $id]);
    db_query("DELETE FROM lk_vku_data WHERE vku_id=:id", [':id' => $id]);
    db_query("DELETE FROM lk_vku_data_categories WHERE vku_id=:id", [':id' => $id]);
 
    return TRUE;
  }

  /**
   * Sets a data_serialized field
   *
   * @param type $id
   * @param type array
   */
  function setPageSerializedSetting($id, $settings){

    db_update('lk_vku_data')
      ->fields(array('data_serialized' => serialize($settings)))
      ->condition('vku_id', $this ->getId())
      ->condition('id', $id)
      ->execute();
  }

  /**
   * Active PPTX Generation for the User
   *
   * @return boolean
   */
  function canGeneratePPTX() {

    $account = $this->getAuthorObject();
    if($account ->isModerator()) {
      return TRUE;
    }

    $verlag = $account->getVerlagObject();
    if(!$verlag) {
      return FALSE;
    }

    $value = $verlag->getVerlagSetting('vku_2_pptx', FALSE);
    if($value) {

      return TRUE;
    }

    return FALSE;
  }
}
