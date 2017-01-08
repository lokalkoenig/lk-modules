<?php

namespace LK\VKU\Editor;


class Manager extends \LK\PXEdit\DyanmicLayout {
  
  
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
   * Gets the available Presets per Account
   * 
   * @param \LK\User $account Account
   * @return array
   */
  function getPresetsAvailable(\LK\User $account){
    
    
    return [
       'OnlineArgumentation',
        
    ];
    
  }
  
}

