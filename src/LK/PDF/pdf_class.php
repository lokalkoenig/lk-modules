<?php
//if(!defined("LK_DEBUG")) error_reporting(0);  

define("VKU_FONT", 'DejaVu');
require(dirname(__FILE__).'/tpdf/tFPDF.php');
require(dirname(__FILE__).'/tpdf/table/pdftable.inc.php');
define("FPDF_VERSION", "Lokalkoenig");


/**
 * Lokalkoenig PDF Class
 * uses tPDF as Base
 * 
 */
class PDF extends PDFTable {

    var $logo_oben = NULL;
    var $logos_unten = array();

    var $bg_color = array(255, 255, 255);
    var $title_bg_color = array(100, 100, 100);
    var $title_vg_color = array(255, 255, 255);
    var $B;
    var $I;
    var $U;
    var $HREF;
    var $fontList;
    var $issetfont;
    var $issetcolor;
    var $tdwidth = 1;
    var $tdbegin = '';
    var $noimagerendering = false;
    var $verlag_font = 'lato';
    var $verlag_logo_position = 'left';
    var $verlag_contact_layout = 'default';
    var $verlag_hide_size_online = 'no';
    
    public function disableImagerendering(){
        $this -> noimagerendering = true;
    }


    /** 
    * Constructor 
    * inittialize the Object
    * Add fonts for the use
    */ 
  public function PDF(){
       parent::tFPDF('L');
       
      $this->AddFont(VKU_FONT,'','Lato-Regular.ttf',true);
      $this->AddFont(VKU_FONT,'B','Lato-Bold.ttf',true);
      $this->AddFont(VKU_FONT,'I','Lato-Italic.ttf',true);
      
      // Add font Arial
      $this->AddFont(VKU_FONT . '_arial','','myarial.ttf',true);
      $this->AddFont(VKU_FONT. '_arial','B','myarial_bold.ttf',true);
      $this->AddFont(VKU_FONT . '_arial','I','myarial_italic.ttf',true);
  }
  
  /**
   * Set VKU-Defaults
   *  currently not in use
   */
  function setVKUDefaults(){

  }

  /** 
   * Sets the a color definied by var
   * 
   * @param String $varname
   * @param String (Hex-Code) $hex
   * 
   */
function setVKUColor($varname, $hex){
    if($hex) {
        $this -> $varname =  hex2rgb($hex);
    }
}


/** 
 * Set the Verlag for the PDF
 * Assign variables based on the Verlag
 * 
 * @param Int $verlag_user
 * @return boolean
 * 
 */
function setVerlag($verlag_user){
  
  if(!$verlag_user) return false;
  
  $account = \LK\get_user($verlag_user);
  
  if(!$account ->isVerlag()){
      return false;
  }
  
  $logo_oben = $account -> getVerlagSetting("verlag_logo", false, 'uri');
  if($logo_oben){
      $this -> logo_oben = max_res_img_test($logo_oben, 'small');
  }
  
  if($color = $account -> getVerlagSetting("vku_hintergrundfarbe", false, 'jquery_colorpicker')){
     $this -> setVKUColor("bg_color", $color);
  }

  if($color = $account -> getVerlagSetting("vku_hintergrundfarbe_titel", false, 'jquery_colorpicker')){
     $this -> setVKUColor("title_bg_color", $color);
  }

  if($color = $account -> getVerlagSetting("vku_vordergrundfarbe_titel", false, 'jquery_colorpicker')){
     $this -> setVKUColor("title_vg_color", $color);
  }
  
  $this -> verlag_contact_layout = $account -> getVerlagSetting("verlag_kontakt_vorlage", 'default', 'value');
  $this -> verlag_logo_position = $account -> getVerlagSetting("verlag_logo_position", 'left', 'value');
  $this -> verlag_font = $account -> getVerlagSetting("verlag_font", 'lato', 'value');
  $this -> verlag_hide_size_online = $account -> getVerlagSetting("verlag_online_formate", 'no', 'value');
  
   $logos = array();
   if(isset($account->profile['verlag']->field_verlag_marken_logos['und'])){
        foreach($account->profile['verlag']->field_verlag_marken_logos['und'] as $logo){
          $logos[] = max_res_img_test($logo["uri"], 'small'); 
        }
    }
    $this -> logos_unten = $logos;
  
    return true;
}

/**
 * Escape Text, convert to UTF-8
 * 
 * @deprecated since version number
 * @param type $txt
 * @return type
 * 
 */
function escapeText($txt){
    $txt = utf8_decode($txt);
return $txt;     
}


/**
 * Removed the Drupal Query parameter from the Image
 * and calls the interited Method from the Base-Class
 * 
 * @param type $file
 * @param type $x
 * @param type $y
 * @param type $w
 * @param type $h
 * @param type $type
 * @param type $link
 * 
 */

function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link=''){
   $bild = explode("?", $file);
   $file = $bild[0];
   
   $useimage = \LK\Files\FileGetter::get($file);
   
   /**
   $size = @getimagesize($file);  
   //$file = str_replace("http://lk.dev/", "", $file);
   //$file = str_replace("http://www.lokalkoenig.de/", "", $file);
   
   if(!$size){
       $file = 'sites/all/modules/lokalkoenig/vku/pages/place-text.png';   
   }    
   
   // When no Image Rendering
   if($this -> noimagerendering){
       return ;
   }
   */
  parent::Image($useimage, $x, $y, $w, $h, $type, $link);
}


/** 
 * AcceptPageBreak
 * 
 * @return boolean
 * 
 */
function AcceptPageBreak() {

return false;
}


function RotatedText($x, $y, $txt, $angle){
    //Text rotated around its origin
    $this->Rotate($angle, $x, $y);
    $this->Text($x, $y, $txt);
    $this->Rotate(0);
}

var $angle=0;

function Rotate($angle,$x=-1,$y=-1){
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function _endpage(){
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}

function SetFont($family, $style = '', $size = 0, $default = false) {
    
    if($this -> verlag_font == 'arial' || $this -> verlag_font == 'Arial'){
        $family = VKU_FONT . '_arial';
    }
    
    
    parent::SetFont($family, $style, $size, $default);
}

/** 
 * Generates the Header of the PDF
 * 
 */

function Header(){
    $this->SetFillColor($this -> bg_color[0], $this -> bg_color[1], $this -> bg_color[2]);
    
    $this -> Rect(0,0, 297, 30, 'F');  
    $this-> SetFont('Arial','B',15);
    
    if($this -> logo_oben) {
         
         if($this -> verlag_logo_position == 'right'){
            $size = getimagesize($this -> logo_oben);
            $width = $size[0];
            $height = $size[1];
            $height_calc =  $height / 20;
            $width_calc = $width / $height_calc;
            $this->Image($this -> logo_oben, (297 - 25 - $width_calc), 5, 0, 20);
         }
         else {
            $this->Image($this -> logo_oben, 25, 5, 0, 20);
         }   
    }   
    
    $this -> Line(0,30, 297, 30);
}

/**
 * 
 * Footer
 * 
 */
function Footer(){
    $this->SetFillColor($this -> bg_color[0], $this -> bg_color[1], $this -> bg_color[2]);
    $this -> Rect(0,185, 297, 30, 'F');  
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this -> SetDrawColor(105,105,105);
    $this -> Line(0,185, 297, 185);
    
    $y = 25;
    foreach($this -> logos_unten as $logo){
       $this->Image($logo, $y, 190, 30); 
       $y += 35;
    }
}        #

function WriteHTML($html)
{
    //$html = str_replace('&nbsp;', " ", $html);
    //$html = utf8_decode($html);
    $html=strip_tags($html, "<br><b><u><i><a><img><p>
<strong><em><font><tr><blockquote><hr><td><tr><table><sup>"); //remove all unsupported tags
    $html=str_replace("\n", '', $html); //replace carriage returns by spaces
    $html=str_replace("\t", '', $html); //replace carriage returns by spaces
    $a=preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //explodes the string
    
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF, $e);
            elseif($this->tdbegin) {
                if(trim($e)!='' and $e!=" ") {
                    $this->Cell($this->tdwidth, $this->tdheight, $e, $this->tableborder, '', $this->tdalign, $this->tdbgcolor);
                }
                elseif($e==" ") {
                    $this->Cell($this->tdwidth, $this->tdheight, '', $this->tableborder, '', $this->tdalign, $this->tdbgcolor);
                }
            }
            else
                $this->Write(5, stripslashes(txtentities($e)));
        }
        else
        {
            //Tag
            if($e{0}=='/')
                $this->CloseTag(strtoupper(substr($e, 1)));
            else
            {
                //Extract attributes
                $a2=explode(' ', $e);
                $tag=strtoupper(array_shift($a2));
                $attr=array();
                foreach($a2 as $v)
                    if(@ereg('^([^=]*)=["\']?([^"\']*)["\']?$', $v, $a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                $this->OpenTag($tag, $attr);
            }
        }
    }
}

function OpenTag($tag, $attr)
{
    //Opening tag
    switch($tag){

        case 'SUP':
            if($attr['SUP'] != '') {    
                //Set current font to: Bold, 6pt     
                $this->SetFont('', '', 6);
                //Start 125cm plus width of cell to the right of left margin         
                //Superscript "1" 
                $this->Cell(2, 2, $attr['SUP'], 0, 0, 'L');
            }
            break;

        case 'TABLE': // TABLE-BEGIN
            if(isset($attr['BORDER']) AND $attr['BORDER'] != '' ) $this->tableborder=$attr['BORDER'];
            else $this->tableborder=0;
            break;
            
        case 'TR': //TR-BEGIN
            break;
        case 'TD': // TD-BEGIN
            if( $attr['WIDTH'] != '' ) $this->tdwidth=($attr['WIDTH']/4);
            else $this->tdwidth=40; // SET to your own width if you need bigger fixed cells
            if( $attr['HEIGHT'] != '') $this->tdheight=($attr['HEIGHT']/6);
            else $this->tdheight=6; // SET to your own height if you need bigger fixed cells
            
            if(isset($attr['ALIGN']) AND $attr['ALIGN'] != '' ) {
                $align=$attr['ALIGN'];        
                if($align=="LEFT") $this->tdalign="L";
                if($align=="CENTER") $this->tdalign="C";
                if($align=="RIGHT") $this->tdalign="R";
            }
            else $this->tdalign="L"; // SET to your own
            if(isset($attr['BGCOLOR']) AND $attr['BGCOLOR'] != '' ) {
                $coul=hex2dec($attr['BGCOLOR']);
                    $this->SetFillColor($coul['R'], $coul['G'], $coul['B']);
                    $this->tdbgcolor=true;
                }
            $this->tdbegin=true;
            break;

        case 'HR':
              $this->Ln(10);
            $Width = $this->w - $this->lMargin-$this->rMargin;
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetLineWidth(0.1);
            $this->Line($x, $y, $x+$Width, $y);
            $this->SetLineWidth(0.1);
            $this->Ln(1);
            break;
        
        case 'STRONG':
            $this->SetFont('DejaVu', 'B');
            break;
            
            
        case 'I':    
        case 'EM':
            $this->SetFont('DejaVu', 'I');
            break;
        case 'B':
        case 'U':
            $this->SetFont('DejaVu', 'B');
            break;
       
        //case 'TR':
        case 'BLOCKQUOTE':
        case 'BR':
            $this->Ln(5.5);
            break;
            
        case 'P':
            $this->Ln(10);
            break;
       
    }
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='SUP') {
    }

    if($tag=='TD') { // TD-END
        $this->tdbegin=false;
        $this->tdwidth=0;
        $this->tdheight=0;
        $this->tdalign="L";
        $this->tdbgcolor=false;
    }
    if($tag=='TR') { // TR-END
        $this->Ln();
    }
    if($tag=='TABLE') { // TABLE-END
        //$this->Ln();
        $this->tableborder=0;
    }

    if($tag=='STRONG')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' or $tag=='I' or $tag=='U' OR $tag=='EM')
        $this->SetFont('DejaVu');
    if($tag=='A')
        $this->HREF='';
    
}

function SetStyle($tag, $enable)
{
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B', 'I', 'U') as $s)
        if($this->$s>0)
            $style.=$s;
    $this->SetFont('', $style);
}

function PutLink($URL, $txt)
{
    //Put a hyperlink
    $this->SetTextColor(0, 0, 255);
    $this->SetStyle('U', true);
    $this->Write(5, $txt, $URL);
    $this->SetStyle('U', false);
    $this->SetTextColor(0);
}


}


  
  function swca($string){
    $string = str_replace("–", "-", $string);
    $string = str_replace("'", "'", $string);
    $string = txtentities($string);
    
    //die($string);
    return $string;
  }


  function max_res_img_test($url, $size = 'medium'){
      $class = 'max-res';
      
      if(!$url) return null;
      
      switch($size){
          case 'small':
            $class = 'pdf_small';
            break;
          
          case 'big':
            $class = 'pdf_big';
            break;
          
          default:
            $class = 'pdf_medium';
            break;
        }
      
      if(defined('PART_ONLY')){
          $class .= '_preview';
      }
  
      $url = image_style_url($class, $url);

  return $url;    
  }

function hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}


//function hex2dec
//returns an associative array (keys: R, G, B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000"){
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['G']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter in 72 dpi
function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

?>