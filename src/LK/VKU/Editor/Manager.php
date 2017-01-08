<?php

namespace LK\VKU\Editor;


class Manager extends \LK\PXEdit\DyanmicLayout {
  
  use \LK\Log\LogTrait;
  
  /**
   * Gets the Editor-Template
   * 
   * @param array $variables
   * @return string
   */
  function getEditorTemplate($variables = []){
  

    $variables['footer_logos'] = array(
      "/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/ft.png",
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/br.png?itok=cu0U17XP',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/ct.png?itok=F6FMOtnd',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/sa.png?itok=tQSG6389',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/kt.png?itok=aghT9tC9',
      '/sites/default/files/styles/verlags-logos-klein/public/verlagslogo/franken-aktuell.png?itok=Ts6hLCyY'
    );

    $variables['header_logo'] = "/sites/default/files/styles/verlags-logos-klein/public/mgo_logo_quer.png?itok=C0S5IL9q";
    $variables['callback'] = url('vku_editor');
    
    return parent::getEditorTemplate($variables); 
  }
  
  
  /**
   * Gets the Documents per Category and Verlag
   * 
   * @param \LK\Verlag $verlag Verlag-Account
   * @param string $category
   * @return array
   */
  function getDocumentsPerVerlag(\LK\Verlag $verlag, $category){
    
    return [];  
  }
  
  
  /**
   * Returns a Verlag from as Hash
   * 
   * @todo Access-Check
   * @return boolean|\LK\Verlag
   */
  function getVerlagFromHash(){
    
    if(!isset($_REQUEST['hash'])){
        return false;
    }
    
    $explode = explode('-', $_REQUEST['hash']);
    if(!isset($explode[1])){
      return false;
    }
    
    $uid = (int)$explode[1];
    
    //$current = \LK\current();
    $account = \LK\get_user($uid);
    
    if(!$account || !$account ->isVerlag()){
      return false;
    }
    
    return $account;
  }
  
  
  /**
   * Gets the available Presets per Account
   * 
   * @param \LK\Verlag $account Account
   * @return array
   */
  function getPresetsAvailable(\LK\Verlag $account){
    return [
       'OnlineArgumentation' => [
           'title' => 'Online Argumentation', 
           'category' => 'online',
           'desc' => 'Erstellen Sie ...', 
        ],
       
        'OnlineMedium' => [
           'title' => 'Online Medium', 
           'category' => 'online',
           'desc' => 'Erstellen Sie ...', 
        ],
        
       'RegionalArgumentation' => [
           'title' => 'Regional Argumentation', 
           'category' => 'print',
           'desc' => 'Erstellen Sie ...', 
        ],
        
       'Preisliste' => [
           'title' => 'Preisliste', 
           'category' => 'sonstiges',
           'desc' => 'Erstellen Sie ...',
        ],
        
        'OpenDokument' => [
           'title' => 'Freies Dokument', 
           'category' => 'sonstiges',
           'desc' => 'Erstellen Sie ...',
        ],
        
       'OnlineMediumCollection' => [
           'title' => 'Online Medien Kollektion', 
           'category' => 'online',
           'desc' => 'Erstellen Sie ...',
        ],
    ];
  }
  
  
  function getCategoriesAvailable(\LK\User $account){
  
    return [
      'print' => "Print",
      'online' => "Online",
      'sonstiges' => "Sonstiges",
    ];  
  }
    
}

