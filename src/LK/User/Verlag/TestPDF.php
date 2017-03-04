<?php

namespace LK\User\Verlag;

/**
 * Description of TestPDF
 * Test the Title-Page for Colors and positioning of the Logo
 *
 * @author Maikito
 */
class TestPDF extends \LK\PDF\LK_PDF {

  /**
   * Constructs the Test-PDF
   *
   * @param \LK\Verlag $verlag
   */
  function __construct(\LK\Verlag $verlag) {
    parent::__construct();

    $settings = \LK\VKU\VKUManager::getVKU_RenderSettings($verlag);
    parent::setUserSettings($settings);
  }

  /**
   * Renders the Title-Page
   */
  function render(){
    $title = new \LK\VKU\Pages\StaticPages\Title();
    $title ->renderTitlePage($this, ['title' => 'Ihr Angebot', 'company' => "Unternehmen", 'underline' => "Untertitel"]);
 
    $this->Output();
    exit();
  }
}
