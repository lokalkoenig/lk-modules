<?php
namespace LK\VKU;

/**
 * Description of PageManager
 *
 * @author Maikito
 */
class PageManager extends Data\VKUDataManipulator {

  use \LK\Log\LogTrait;

  var $default_options = [
   'has_children' => false, 
   'delete' => false, 
   'active' => 1,   
   'preview' => false,
   'container' => false, 
   'children_sortable' => false,
   'children' => array(),
   'empty_shown' => false,
   'deactivate' => false,
   'single_toggle' => false,
   'id' => 0,
   'edit-handler' => '',
   'orig-id' => '',
   'pages' => 1,
   'kampagne' => false,
   'collapsed' => false,
   'class' => array(),
   'drop-zone' => false   
  ];
  
  var $modules = [
    'default' => '\\LK\\VKU\\Pages\\PageDefault',
    'node' => '\\LK\\VKU\\Pages\\PageKampagne',
  ];
  
  var $save_dir = "sites/default/private/vku";
  var $vku = null;

  /**
   * Calls a Hook to Modules to get Invokes
   */
  function __construct(\VKUCreator $vku) {

    $this->vku = $vku;

    foreach (module_implements('vku2_add_module') as $module) {
      $function = $module . '_vku2_add_module';
      $addition = $function();
      
      if($addition){
        $this->modules += $addition;
      }
    }
  }

  /**
   * Gets the VKU
   *
   * @return \VKUCreator
   */
  function getVKU(){
    return $this->vku;
  }

  /**
   * Gets the Authors-UID
   *
   * @return int UID
   */
  function getAuthor(){
    return $this->getVKU()->getAuthor();
  }

  /**
   * Gets the Authors Object
   *
   * @return \LK\User
   */
  function getAuthorObject(){
    return \LK\get_user($this->getAuthor());
  }

  /**
   * Gets back the items for the Categories
   * 
   * @param string $category
   * @param \LK\User $account
   * @return arrar Items
   */
  function getPossibilePages($category){

    $account = $this->getAuthorObject();
    
    $items = [];
    $callables = $this->modules;
    
    while(list($module, $val) = each($callables)){
      $obj = $this->getModule($module);
      
      if(!$obj){
        continue;
      }

      $items += $obj ->getPossibilePages($category, $account);
    }
    
    return $items;  
  }
  
  /**
   * Gets the default options
   * 
   * @return array Options
   */
  function getDefaultOptions(){
    return $this->default_options;
  }
  
  /**
   * Gets the default Category Configuration
   * 
   * @return array Categories
   */
  public function getDefaultCategories(){
    
    $categories = array();
    $categories["title"] = array(
      'title' => 'Titelseite',
      'children' => false,
      'children_sortable' => false,
      'delete' => false,
      'deactivate' => true,
      'empty_shown' => true,
      'delta' => 1,
    );
    
    $categories["print"] = array(
      'title' => 'Print',
      'children' => true,
      'delete' => true,
      'children_sortable' => true,
      'empty_shown' => true,
      'deactivate' => true,
      'delta' => 2
    );
    
    $categories["online"] = $categories["print"];
    $categories["online"]["title"] = 'Online';
    $categories["online"]["delta"] = 3;
    
    $categories["kampagne"] = array(
      'title' => 'Kampagne',
      'children' => true,
      'children_sortable' => false,
      'delete' => true,
      'deactivate' => false,
      'delta' => 4,
      'empty_shown' => false,
    );
    
    $categories["other"] = array(
      'title' => 'Sonstiges',
      'children' => false,
      'children_sortable' => false,
      'delete' => true,
      'deactivate' => true,
      'empty_shown' => false,
    );
    
   return $categories;
  }

  private function getCategoryChildren($cid){
    $default = $this->getDefaultOptions();
    
    $children = [];
    $dbq2 = db_query("SELECT id FROM lk_vku_data WHERE data_category='". $cid ."' ORDER BY data_delta ASC");
    foreach($dbq2 as $child){
      $page = $this->_getPageData($child -> id);
                
      $item = $default;
      $item["cid"] = $cid;
      $item["id"] = $child -> id;
      $item["title"] = $page -> data_class;
      
      $return = $this-> getModuleConfiguration($child -> id, $item);
      if(!$return){
        // we have a Broken Page
        $this->removeBrokenID($child -> id);
        continue;
      }  
      
      $children[] = $return;
    }
  
    return $children;
  }

  /**
   * Gets a Module Configuration from a Callback
   * 
   * @param type $cid
   * @param \VKUCreator $vku
   * @param type $items
   * @return boolean
   */
  function getModuleConfiguration($id, $items){
    $seite = $this->_getPageData($id);

    if(!$seite){
      return FALSE;
    }

    $document = $this->getModule($seite -> data_module);
    if(!$document){
      return FALSE;
    }
    
    return $document ->getImplementation($items, (array)$seite);
  }
  
  
  /**
   * Gets a Module
   * 
   * @param string $module
   * @return boolean|\LK\VKU\Pages\Interfaces\PageInterface
   */
  protected function getModule($module){

    if($module === 'kampagne'){
      $module = 'node';
    }

    // Module must be propagated
    if(!isset($this->modules[$module])){
      return FALSE;
    }
    
    $class_name = $this->modules[$module];
    lokalkoenig_Autoload($class_name);
    
    if(!class_exists($class_name)){
      return false;
    }
    
    $obj = new $class_name($this);
    
  return $obj;   
  }
  
  
  /**
   * Removes a Broken Item from the VKU-Creator-Instance
   * 
   * @param type $id
   */
  private function removeBrokenID($id){


    $data = $this->_getPageData($id);
    if($data){
      // Remove the Page
      $this->logError('Remove broken page ' . $data -> id . "/". $data -> data_category ." (". $data -> data_module ."/". $data -> data_class .")", ['vku' => $this->getVKU()]);
      $this->_removePage($data -> id);
    }
  }

  /**
   * Gets the current PageConfiguration
   * 
   * @return array
   */
  function generatePageConfiguration(){
    // Assuming there is nothing

    $vku = $this->getVKU();
    $default = $this->getDefaultOptions();
    $vku_id = $vku->getId();
    
    $structure = array();
    $dbq = db_query("SELECT * FROM lk_vku_data_categories WHERE vku_id='". $vku_id ."' ORDER BY sort_delta ASC");
    while($all = $dbq -> fetchObject()){
      
      $cid = $all -> id;
      $structure[$cid] = $default;
      $structure[$cid]["type"] = $all -> category;
      $structure[$all -> id]["cid"] = $all -> id;
      $structure[$all -> id]["id"] = 0;
      $structure[$all -> id]["active"] = true;
        
      // Print & Online
      if(in_array($all -> category, array('print', 'online'))){
        $structure[$all -> id]["has_children"] = true;
        $structure[$all -> id]["title"] = '<strong>Argumentation ' . ucfirst($all -> category) . '</strong>';
        $structure[$all -> id]["delete"] = true;
        $structure[$all -> id]["container"] = true;
        $structure[$all -> id]["children_sortable"] = true;
        $structure[$all -> id]["empty_shown"] = true;
        $structure[$all -> id]["collapsed"] = true;
        $structure[$all -> id]["class"][] = 'dropable-items-' . $all -> category;
        $structure[$all -> id]["drop-zone"] = 'drop-zone-' . $all -> category;
        $structure[$all -> id]["children"] = $this -> getCategoryChildren($all -> id);
        
        $pages_count = 0;
        while(list($key, $val) = each($structure[$all -> id]["children"])){
          if($val["active"]){
            $pages_count += $val["pages"];
          }
        }
        
        reset($structure[$all -> id]["children"]);
        $structure[$all -> id]["pages"] = $pages_count;
      }
      elseif(in_array($all -> category, ['other', 'title', 'kampagne'])){
        $dbq2 = db_query('SELECT id FROM lk_vku_data WHERE data_category=:cat', [':cat' => $cid]);
        $all2 = $dbq2 -> fetchObject();
        $return = $this-> getModuleConfiguration($all2 -> id, $structure[$cid]);

        // We have a broken Configuration
        if(!$return):
          $this->removeBrokenID($all2 -> id);
          unset($structure[$cid]);
          continue;
        endif;
        
        $structure[$all -> id] = $return;
      } 
    }

    return $structure;
  }
}
