<?php
namespace LK\PDF;

define('K_PATH_FONTS', $_SERVER['DOCUMENT_ROOT'] . '/sites/all/fonts/');
define('VKU_FONT', "DejaVu");
require_once $_SERVER['DOCUMENT_ROOT'] .'/sites/all/vendor/tecnick.com/tcpdf/tcpdf.php';


/**
 * Description of LK_PDF
 *
 * @author Maikito
 */
class LK_PDF extends \TCPDF {

  protected $_settings;
  protected $_module_asset_dir = 'sites/all/modules/lokalkoenig/vku/pages';
  protected $font_usage = 'lato';


  function __construct() {
    parent::__construct("L");
    
    $this->SetMargins(0, 0);
    $this->SetTopMargin(30);
    $this->SetAutoPageBreak(FALSE);

    $this->setFontSubsetting(true);
    $this->SetCompression();
  }

  /**
   * Gets the LK Asset DIR
   *
   * @return string Path to Module Asset Dir
   */
  public function getAssetDir(){
    return $this->_module_asset_dir;
  }

  

  /**
   * Adds a new Page
   *
   * @param string $orientation
   * @param string $format
   * @param boolean $keepmargins
   * @param boolean $tocpage
   */
  public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
    parent::AddPage($orientation, $format, $keepmargins, $tocpage);

    // Reset Margins & Color
    $this->SetTextColor(69, 67, 71);
    $this->SetTopMargin(40);
    $this->SetLeftMargin(25);
    $this->SetRightMargin(25);
  }

  public function getMarginLeft(){
    return $this->lMargin;
  }


  /**
   *
   * @param string $key Font-Key
   */
  public function SetFontClass($key){

    if($key === 'h2'){
      $this-> SetFont('', '', 22);
    }

    if($key === 'h1'){
      $this-> SetFont('', 'B', 26);
    }

    if($key === 'big'){
      $this-> SetFont('', '', 14);
    }
  }

  /**
   * Adds a Headline at the TOP
   *
   * @param string $copy Text
   * @param string $align Align
   */
  public function addHeadline($copy, $align = "L"){
    $this->SetFontClass('h1');
    $this->MultiCell(0, 0, $copy, 0, $align, 0);
    $this->Ln(10);
  }

  /**
   * Sets the User-Setting
   *
   * @param type $key
   * @param type $value
   */
  public function setUserSetting($key, $value){
    $this->_settings[$key] = $value;
  }

  public function setUserSettings($settings){
    $this->_settings = $settings;
    $this -> font_usage = $this->getUserSettings('', 'lato');

    if($this -> font_usage === 'lato'){
      $this->AddFont('lato');
      $this->AddFont('lato', 'B');
      $this->AddFont('lato', 'I');
      $this->AddFont('lato', 'BI');
    }
    else {
      $this->AddFont('myarial', '', '');
      $this->AddFont('myarial', 'B', '', TRUE);
      $this->AddFont('myarial', 'I', '', TRUE);
    }
  }

  public function getUserSettings($key, $default = FALSE){
    if(isset($this->_settings[$key])){
      return  $this->_settings[$key];
    }

    return $default;
  }

  /**
   * Sets the font
   * Controlls weather to use Arial or Lato
   *
   * @param string $family
   * @param type $style
   * @param type $size
   * @param type $default
   */
  function SetFont($family, $style='', $size=null, $fontfile='', $subset='default', $out=true) {
    parent::SetFont($this -> font_usage, $style, $size, $fontfile, true, $out);
  }

  //Page header
  public function Header() {
    $bg = $this ->getUserSettings('vku_hintergrundfarbe_rgb', [255,255,255]);
    $this->SetFillColor($bg[0], $bg[1], $bg[2]);
    $this -> Rect(0,0, 297, 30, 'F');

    $logo = $this->getUserSettings('logo_oben');
    if($logo){
      $logo_img = \LK\Files\FileGetter::get($logo);
    }

    $verlag_logo_position = $this->getUserSettings('verlag_logo_position');

    if($logo) {
      if($verlag_logo_position === 'right'){
        $size = getimagesize($this -> logo_oben);
        $width = $size[0];
        $height = $size[1];
        $height_calc =  $height / 20;
        $width_calc = $width / $height_calc;


        $this->Image($logo_img, (297 - 25 - $width_calc), 5, 0, 20);
      }
      else {
        $this->Image($logo_img, 25, 5, 0, 20);
      }
    }

    $this -> Line(0,30, 297, 30);
  }
 
  // Page footer
  public function Footer() {
    $bg = $this ->getUserSettings('vku_hintergrundfarbe_rgb', [255,255,255]);

    $this->SetFillColor($bg[0], $bg[1], $bg[2]);
    $this -> Rect(0,185, 297, 30, 'F');
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this -> SetDrawColor(105,105,105);
    $this -> Line(0,185, 297, 185);

    $logos = $this->getUserSettings('logos_unten', []);

    $y = 25;
    foreach($logos as $logo){
      $logo_img = \LK\Files\FileGetter::get($logo);
      $this->Image($logo_img, $y, 190, 30);
      $y += 35;
    }
  }

  /**
   * Converts Markup in the PDF
   *
   * @param string $html
   */
  function WriteOwnHTML($html) {
    $html = strip_tags($html, "<br><b><strong><u><i><em>"); //remove all unsupported tags
    $html = str_replace("\n", '', $html); //replace carriage returns by spaces
    $html = str_replace("\t", '', $html); //replace carriage returns by spaces
    $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //explodes the string

    foreach($a as $i=>$e) {
        if($i%2 == 0){
          if(!empty(trim($e))){
            $this->Write(5, $e);
          }
        }
        else {
          //Tag
          if($e{0}=='/') {
            $this->_CloseTag(strtoupper(substr($e, 1)));
          }
          else {
            //Extract attributes
            $a2 = explode(' ', $e);
            $tag = strtoupper(array_shift($a2));
            $this->_OpenTag($tag);
          }
        }
    }
  }

  /**
   * Opens a TAG
   *
   * @param string $tag
   */
  private function _OpenTag($tag) {
      //Opening tag
      switch($tag){
        case 'I':
        case 'EM':
          $this->SetFont('DejaVu', 'I');
          break;

        case 'B':
        case 'STRONG':
        case 'U':
          $this->SetFont('DejaVu', 'B');
          break;

        case 'BR':
          $this->Ln(5.5);
          break;
      }
  }

  /**
   * Closes a Tag
   *
   * @param string $tag
   */
  private function _CloseTag($tag) {
    
    if($tag=='STRONG') {
      $tag='B';
    }
    
    if($tag=='EM'){
      $tag='I';
    }
    
    if($tag=='B' or $tag=='I' or $tag=='U' OR $tag=='EM') {
      $this->SetFont('');
    }
  }
}
