<?php
namespace LK\VKU;

/**
 * Description of PageManager
 *
 * @author Maikito
 */
class PageManager {
  
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
  
  /**
   * Calls a Hook to Modules to get Invokes
   */
  function __construct() {
    foreach (module_implements('vku2_add_module') as $module) {
      $function = $module . '_vku2_add_module';
      $addition = $function();
      
      if($addition){
        $this->modules += $addition;
      }
    }
  }
  
  /**
   * Removes the current Page
   * 
   * @param \VKUCreator $vku
   * @param type $module
   * @param type $id
   * @param type $item
   * @return boolean
   */
  function removePage(\VKUCreator $vku, $id, $item){
    
    $data = $vku->getPage($id);
    
    $obj = $this->getModule($data['data_module']);
    if(!$obj){
      dpm($id);
      return false;
    }
    
    $obj ->removeItem($vku, $id);
    $vku->removePage($id);
  }
  
  
  function updatePage(\VKUCreator $vku, $id, $item){
    
    $data = $vku->getPage($id);
    $obj = $this->getModule($data['data_module']);
    if(!$obj){
      return false;
    }
    
    return $obj ->updateItem($vku, $id, $item);
  }
  
  
  function addNewPage(\VKUCreator $vku, $cid, $module, $id, $children = []){
    
    $insert = [];
    $insert['vku_id'] = $vku ->getId();
    $insert['data_module'] = $module;
    $insert['data_class'] = $id;
    $insert['data_created'] = time();
    $insert['data_entity_id'] = 0;
    $insert['data_serialized'] = null;
    $insert['data_delta'] = 0;
    $insert['data_active'] = 1;
    $insert['data_category'] = $cid;

    $obj = $this->getModule($module);
    
    if(!$obj){
      return false;
    }
    
    return $obj ->saveNewItem($insert);
  }
  
  /**
   * Generates a Sample Kampagne
   */
  function generateSampleKampagne($pdf, $node){
    $obj = $this->getModule('node');
    $obj ->getOutputPDF(['node' => $node, 'data_serialized' => ''], $pdf);
  }
  
  
  /**
   * Gets back a generated PDF from the VKU
   * @param \VKUCreator $vku
   * @param $line_item optional-page-id
   * @param boolean $output direct
   */
  function generatePDF(\VKUCreator $vku, $line_item = 0, $output = false){
    
    $pdf = \LK\PDF\PDF_Loader::getPDF($vku ->getAuthor()); 
    $pages = $vku -> getPages();
    
    while(list($key, $page) = each($pages)){
      if(!$page["data_active"]) {
          continue;
      }
      
      if($line_item && $line_item != $key){
        continue;
      }
      
      $mod = $this->getModule($page["data_module"]);
      if($mod){
        $mod->getOutputPDF($page, $pdf);
      }
    }
    
    if($output){
      \LK\PDF\PDF_Loader::output($pdf);
    }
    
    return $pdf;  
  }

  /**
   * Gets back a generated PDF from the VKU
   * @param \VKUCreator $vku
   * @param $line_item optional-page-id
   * @param boolean $output direct
   */
  function generatePPTX(\VKUCreator $vku, $line_item = 0){

    $ppt = \LK\PPT\PPTX_Loader::load();
    $ppt ->setVKU($vku);
    $pages = $vku -> getPages();

    while(list($key, $page) = each($pages)){
      if(!$page["data_active"]) {
          continue;
      }

      if($line_item && $line_item != $key){
        continue;
      }

      $mod = $this->getModule($page["data_module"]);
      if($mod){
        $mod->getOutputPPT($page, $ppt);
      }
    }

    return $ppt;
  }


  function finalizeVKU(\VKUCreator $vku){
    $pdf = $this->generatePDF($vku);
    $fn = $vku -> getId() . ".pdf";
    $file_path = $this->save_dir .'/'. $fn;
    $pdf->Output($file_path, 'F');

    // PPTX
    if(\vku_is_update_user_ppt()):
      $pptx = $this->generatePPTX($vku);
      $file_pptx = \LK\PPT\PPTX_Loader::save($pptx, $this->save_dir, $vku ->getId());
      $vku -> set('vku_ppt_filename', $file_pptx);
      $file_size = filesize($this->save_dir . '/' . $file_pptx);
      $vku -> set('vku_ppt_filesize', $file_size);
    endif;

    $vku -> set("vku_ready_filename", $fn);
    $vku -> set("vku_ready_time", time()); 
    $vku -> set("vku_ready_filesize", filesize($file_path)); 
    
    return true;
  }
  
  
  /**
   * Gets back the items for the Categories
   * 
   * @param string $category
   * @param \LK\User $account
   * @return arrar Items
   */
  function getPossibilePages($category, \LK\User $account){
    
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
  
  
  private function getCategoryChildren($cid, \VKUCreator $vku){
    $default = $this->getDefaultOptions();
    
    $children = [];
    $dbq2 = db_query("SELECT id FROM lk_vku_data WHERE data_category='". $cid ."' ORDER BY data_delta ASC");
    foreach($dbq2 as $child){
      $page = $vku ->getPage($child -> id);
                
      $item = $default;
      $item["cid"] = $cid;
      $item["id"] = $child -> id;
      $item["title"] = $page["data_class"];
      
      $return = $this-> getModuleConfiguration($child -> id, $vku, $item);
      if(!$return){
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
  function getModuleConfiguration($id, $vku, $items){
    $seite = $vku->getPage($id);
 
    $document = $this->getModule($seite["data_module"]);
    if(!$document){
      return false;
    }
    
    return $document ->getImplementation($vku, $items, $seite);
  }
  
  
  /**
   * Gets a Module
   * 
   * @param type $module
   * @return boolean|\LK\VKU\PageInterface
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
   * Gets the current PageConfiguration
   * 
   * @param \VKUCreator $vku
   * @return array
   */
  function generatePageConfiguration(\VKUCreator $vku){
    // Assuming there is nothing
   
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
        $structure[$all -> id]["children"] = $this -> getCategoryChildren($all -> id, $vku);
        
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
        $return = $this-> getModuleConfiguration($all2 -> id, $vku, $structure[$cid]);
        
        if(!$return){
          unset($structure[$cid]);
        }
        
        $structure[$all -> id] = $return;
      } 
    }
    
    return $structure;
  }
}
