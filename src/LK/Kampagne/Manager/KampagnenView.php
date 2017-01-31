<?php

namespace LK\Kampagne\Manager;

class KampagnenView {

  use \LK\Stats\Action;
  
  var $kampagne = NULL;
  var $access = NULL;
  var $account = NULL;
  var $node = NULL;
  
  function __construct(\LK\Kampagne\Kampagne $kampagne) {
    $this -> kampagne = $kampagne;
    $this -> account = \LK\current();
    $this -> access = new \LK\Kampagne\Manager\Access($kampagne, $this->account);
    $this -> node = $this ->getKampagne()->getNode();
  }

  /**
   * Gets the Node
   *
   * @return \stdClass
   */
  function getNode(){
    return $this->node;
  }

   /**
   * Gets the Kampagne
   *
   * @return \LK\Kampagne\Kampagne
   */
  function getKampagne(){
    return $this->kampagne;
  }

  /**
   * Gets the Access-Manager
   *
   * @return \LK\Kampagne\Manager\Access
   */
  function getAccessManager(){
    return $this->access;
  }

  /**
   * Sets a View-Mode
   *
   * @param string $view_mode (full, teaser, grid)
   */
  function setMode($view_mode){
    $node = $this->getNode();
    $this -> setDefaults();
    $kampagne = $this->getKampagne();
    $account = $this->getAccount();

    if(!$node -> status || $account -> isAgentur()){
      $node -> merkliste_can = $node -> vku_can = false;
      return ;
    }
    elseif(!$kampagne->canPurchase()){
      $node -> vku_can = false;
    }

    $this -> addRecomendLinks();
    $this -> getFormatInformation();
    $this -> getVKULinks();
    $this -> getMerklisteLinks();
    $this -> getAlerts();

    $this->getAccessInfo();

    if($view_mode === 'full'){
      $this ->getFullViewAccessInformation();

      if($node -> status){
        $this->addMoreLikeThis();
        $this->trackNodeView();
      }
    }
  }

  /**
   * Gets the current user account
   */
  function getAccount(){
    return $this->account;
  }
  
  /**
   * Sets the Defaults for the 
   * Kampagnen View
   */
  function setDefaults(){

    $node = $this -> getNode();
    $node -> merkliste_can = $node -> vku_can = true;
    $node -> in_vku = $node -> vku_url = false;
    $node -> sperre_hinweis = '';
    $node -> basic_links = array();
    $node -> verlags_sperre = false;
    $node -> purchase_button = '';

    // Only Moderator
    if($this->getAccount()->isModerator()){
      if($this->getKampagne()->getLizenzenCount()){
        $node -> basic_links["lizenz"] = array();
        $node -> basic_links["lizenz"]["title"] = '<span class="glyphicon glyphicon-euro" data-toggle="tooltip" title="Kampagne hat Lizenzen"></span>';
        $node -> basic_links['lizenz']["href"] = "node/" . $node -> nid . "/lizenzen";
        $node -> basic_links["lizenz"]["attributes"]["class"] = array("new-style-icon");
      }
    }

    $node -> merkliste_link = [
        'href' => 'node/' . $node -> nid,
        'title' => "Merkliste",
        'attributes' => [
            'class' => ['merkliste'],
            'data-toggle' => 'tooltip',
            'onclick' => 'return false;',
            'title' => 'Diese Funktion ist für Sie nicht verfügbar',
        ]
    ];

    $node -> vku_active = 0;
    $node -> vku_link = [
        'href' => 'node/' . $node -> nid,
        'title' => 'Verkaufsunterlage',
        'attributes' => [
            'class' => ['addvkujs-no'],
            'data-toggle' => 'tooltip',
            'onclick' => 'return false;',
            'title' => 'Diese Funktion ist für Sie nicht verfügbar',
        ]
    ];
  }

  /**
   * Adds some info if there is a Lizenz
   *
   * @param int $lizenz
   */
  private function addLizenzView($lizenz_id){
    $manager = new \LK\Kampagne\LizenzManager();
    $lizenz = $manager ->loadLizenz($lizenz_id);
    
    if(!$lizenz){
      return ;
    }  
    
    $test = $lizenz ->canDownload();
    if($test["access"]):
      $this->setSperreInfo(
        theme('node_page_lizenz_purchased', 
        [
          "lizenz" => $lizenz ->getTemplateData()
        ]));    
    endif;
  }
  
  /**
   * Sets the Information about the Sperre
   *
   * @param string $html
   */
  private function setSperreInfo($html){
    $node = $this ->getNode();
    $node -> sperre_hinweis = $html;
  }

  /**
   * Gets all the Information for the Full view
   */
  private function getFullViewAccessInformation(){

    $node = $this ->getNode();
    $account = $this->getAccount();
    $manager = $this->getAccessManager();
    $node -> alerts = $manager -> getVKUUsageCount();

    if(isset($node -> basic_links["alerts"])){
      unset($node -> basic_links["alerts"]);
    }

    if(isset($node -> basic_links["lizenz"])){
      unset($node -> basic_links["lizenz"]);
    }
   
    $access = $node -> plzaccess;
    $current_lizenz = NULL;

    // User has a Lizenz
    if(!$access){
      $current_lizenz = $manager -> getCurrentUserLizenz();
      if($current_lizenz){
        $this->addLizenzView($current_lizenz);
      }
    }
    //
    else {
      if($account->isTestAccount()){
        $node ->purchase_button = \theme('node_page_lizenz_purchas_can_not');
      }
      else {
        $array = ['nid' => $node -> nid];
        
        // When is Telefon-MA
        if($account ->isTelefonmitarbeiter()):
          $array['link'] = 'user/' . $account ->getUid() . '/setplz';
          $array['ausgaben'] = [];
          
          $ausgaben = $account ->getCurrentAusgaben();
          foreach($ausgaben as $a):
            $obj = \LK\get_ausgabe($a);
            $array['ausgaben'][] = $obj ->getTitleFormatted();
          endforeach;
        endif;

        $node ->purchase_button = \theme('node_page_lizenz_to_purchase', $array);
      }
    }

    
    // Alerts
    if($node -> alerts){
      $node -> sperre_hinweis = '<h4 style="margin-top: 0">Kampagnenverwendung</h4>' .
        theme("lk_vku_usage", [
            'class' => 'clearfix',
            'account' => $account,
            'entries' => $manager->getVerlagUsageDetails()
          ]
        );
      return ;
    }


    // Sperre innerhalb des Verlags
    if($node -> verlags_sperre && isset($node -> verlags_sperre["info"])){
      $node -> sperre_hinweis = theme("lk_node_vku_info", ["info" => $node -> verlags_sperre]);
      return ;
    }

    
    // When there are Licences in the Area
    if(!$access && !$node -> verlags_sperre && !$current_lizenz){
        $test = $manager->getUsageAusgabePLZ();
        
        if($test){
           $test["class"] = 'well clearfix';
           $node -> sperre_hinweis .= theme("lk_vku_lizenz_usage", $test, true);
           return ;
        }
    }
  }

  /**
   * Tracks the Node view for the current user
   *
   * @global type $user
   */
  private function trackNodeView(){
    $nid = $this->getKampagne()->getNid();
    $uid = $this->getAccount()->getUid();

    $this->setAction('view-kampagne', $nid);
    db_query("DELETE FROM lk_lastviewed WHERE uid='". $uid ."' AND nid='". $nid ."'");
    db_query("INSERT INTO lk_lastviewed SET uid='". $uid ."', nid='". $nid ."', lastviewed_time='". time() ."'");
  }

  /**
   * Adds a recomend link for Users
   */
  function addRecomendLinks(){

    $node = $this ->getNode();
    $node -> basic_links["recomend"] = array();
    $node -> basic_links["recomend"]["title"] = '<span class="glyphicon glyphicon-envelope" data-toggle="tooltip" title="Versenden Sie diese Kampagne"></span>';
    $node -> basic_links['recomend']["href"] = "node/" . $node -> nid;
    $node -> basic_links["recomend"]["attributes"]["class"] = array("recomendnode new-style-icon");
    $node -> basic_links["recomend"]["attributes"]["nid"] = $node -> nid;

    if($this->getAccount()->isModerator()){
      $node -> basic_links["recomend"]["attributes"]["class"] = array("new-style-icon");
      $node -> basic_links["recomend"]["href"] = url('messages/new', ['absolute' => TRUE, 'query' => ['nid' => $node -> nid]]);
    }
  }

  /**
   * Gets primary Access-information
   */
  function getAccessInfo(){

    $manager = $this->getAccessManager();
    $account = $this->getAccount();
    $node = $this->getNode();

    if($account ->isAgentur()){
      return ;
    }

    // Verlags-Sperre
    $node -> verlags_sperre = $manager ->getVerlagPLZSperreInfo();
    if($node -> verlags_sperre AND $node -> verlags_sperre["uid"] == $account ->getUid()){
      $node -> alerts = false;
    }

    // Dont go further, wenn we can purchase the Kampagne
    if($manager -> hasPurchaseAccess()){
      return ;
    }

    $result = $manager ->getPurchaseAccessInformation();
    $node -> plzinfo = $result;

    if($node -> plzinfo && $node -> plzinfo["access"] == false){
      $node -> sperre_hinweis = '<p class="text-center"><b>' . $node -> plzinfo["reason"] . '</b></p>';
    }

    // A Sperre exists within the verlag
    if($node -> verlags_sperre){
      if($account -> getUid() == $node -> verlags_sperre["uid"]){
        $vku = new \VKUCreator($node -> verlags_sperre["vku_id"]);
        $info = $vku -> hasPlzSperre();
        $url = $vku -> url();
        $node -> vku_url = $vku -> url();
        $node -> sperre_hinweis = '<p>Die Kampagne wurde bis zum '. date('d.m.Y', $info["until"]) .' für folgende Ausgaben für Sie reserviert: ' . implode(" ", $info["ausgaben"]) . ' / '. l("Zu Ihrer Verkaufsunterlage", $url) .'</p>';
      }
      else {
        $node -> sperre_hinweis = '<p>Die Kampagne wird innerhalb Ihres Verlages verwendet.</p>';
      }
    }
  }


  /**
   * Gets the Format-Information
   */
  function getFormatInformation(){

    $node = $this->getNode();

    // Online
    $online_f = explode(",", $node->field_format_kamp_online['und'][0]['value']);
    $online_formate = $online_f[0];
    $online_formate_count = count($online_f);
    $overview_online =  '<span class="multiple-formate" data-toggle="tooltip" title="Diese Kampagne enthält mehrere Formate: '. implode(", ", $online_f) .'"><span class="label label-primary label-lk"><sup>' . $online_formate_count .'</sup><strong>@</strong></span><span class="k-desc">u.a. '.  $online_formate . '</span></span>';

    if($online_formate_count == 1){
      $overview_online = '<img src="/sites/all/themes/bootstrap_lk/design/icon-webanzeige.png" width="20" height="20"/><span class="k-desc">'. $online_formate .'</span>';
    }

    // Print
    $orig = $node->field_format_kamp_print['und'][0]['value'];
    $print_f = explode(",", $orig);

    $print_formate = $print_f[0];
    $print_formate_count = count($print_f);
    $overview_print =  '<span class="multiple-formate" data-toggle="tooltip" title="Diese Kampagne enthält mehrere Formate: '. implode(", ", $print_f) .'"><span class="label label-primary label-lk label-lk-print"><sup>' . $print_formate_count .'</sup><strong>P</strong></span><span class="k-desc">u.a. '.  $print_formate . '</span></span>';

    if($print_formate_count == 1){
      $overview_print = '<img src="/sites/all/themes/bootstrap_lk/design/icon-printanzeige.png" width="20" height="20" /><span class="k-desc">'. $print_formate .'</span>';
    }

    $node -> formate_print = $overview_print;
    $node -> formate_online = $overview_online;
  }
  
  /**
   * Get ML Links
   */
  function getMerklisteLinks(){

    $node = $this->getNode();
    $node -> merkliste_link["attributes"] = [
        'class' => ['merklistejs merkliste'],
        'data-nid' => $node -> nid,
    ];

    if($node -> merkliste) {
       $node -> merkliste_link["attributes"]["class"][] = 'on';
    }
  }

  /**
   * Gets the Alterts for this Kampagne if available
   */
  function getAlerts(){

    $node = $this->getNode();
    $manager = $this->getAccessManager();
    $node-> alters = $manager->getVKUUsageCount();
    
    // When no Alters
    if(!$node-> alters){
      return ;
    }

    $node -> basic_links["alerts"] = [];
    $node -> basic_links["alerts"]["title"] = '<span class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" title="Verwendung anzeigen"></span>';
    $node -> basic_links["alerts"]["title"] .= '<sup><small>' . $node-> alters . '</small></sup>';
    $node -> basic_links['alerts']["href"] = url("nodeaccess/" . $node -> nid, array("absolute" => true));
    $node -> basic_links["alerts"]["attributes"] = array('class' => array("new-style-icon alert-icon"));
  }

  /**
   * Adds VKU-Links to the current Node
   */
  function getVKULinks(){

    $node = $this->getNode();

    // Verkaufsunterlage
    $vku_link = $node -> vku_link;
    $vku_link["title"] = 'Verkaufsunterlage';
    $vku_link["href"] = 'node/' . $node -> nid;


    if($node -> vku_can){
      unset($vku_link["attributes"]['data-toggle']);
      unset($vku_link["attributes"]['title']);
     
      if(vku_is_update_user()){
        $vku_link["href"] = 'vku/add_active/' . $node -> nid;
        $vku_link["attributes"]["class"] = array('addvku2js vku-added');
        $vku_link["attributes"]["data-nid"] = $node -> nid;
      }
      else {
        $vku_link["href"] = 'vku/add/' . $node -> nid;
        $vku_link["attributes"]["class"] = array('addvkujs');
      }

      if($node -> vku_url){
        $vku_link["href"] = $node -> vku_url;
        $vku_link["attributes"]["class"] = array('addvkujs-active');
      }
    }
    
    $node -> vku_link = $vku_link;
  }
  
  /**
   * Gets More like this for the current
   * Kampagne
   */
  private function addMoreLikeThis(){

    $node = $this ->getNode();
    $other = [];
    $search = new \LK\Solr\Search();
    $nodes = $search ->moreLikeThis($node -> nid, 4);

    foreach ($nodes as $nid){
      $other[] = \LK\UI\Kampagne\Teaser::get($nid);
    }

    $node -> mlt = implode("", $other);
  }
}
