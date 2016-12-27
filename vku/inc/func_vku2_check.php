<?php

/**
 * Checks the entire Taxonomy of Medientyp
 */
function vku2_checkup(){
    
     $terms = taxonomy_get_tree(5);
     
     drupal_set_title("Medientypen-Check");
    
    $medias = array(); 
     
    $header = array('TID', 'Name', 'Medias', 'VKU-Anzeigen-Breite', 'Seiten', "Beispiel");
    $rows = array();
    
   
    
  
    
    $actions = array(
        '<a href="/vku/test?pdf=1" class="btn btn-primary btn-hollow">Test-PDF Generieren</a>',
        '<a href="/vku/test?ppt=1" class="btn btn-primary btn-hollow">Test-PPT Generieren</a>'
    );
    
    if(isset($_GET["pdf"])){
        $pdf = generate_pdf_object_verlag();
        $node = node_load(88);
        $node -> medien = $medias;
        $module_dir = 'sites/all/modules/lokalkoenig/vku/pages/';
        require($module_dir.'/b-medias.php');
        
        $pdf->Output("sites/all/modules/lokalkoenig/vku/pdfrender/test-pdf.pdf", 'F');
        drupal_set_message('<a href="/sites/all/modules/lokalkoenig/vku/pdfrender/test-pdf.pdf" target="_blank">PDF herunterladen ('. format_size(filesize("sites/all/modules/lokalkoenig/vku/pdfrender/test-pdf.pdf")) .')</a>');
        drupal_goto("vku/test");
        
    }
    
    
     if(isset($_GET["ppt"])){
        $node = node_load(88);
        $node -> medien = $medias;
        
        require_once __DIR__ . "/../ppt/test.php";
        
        vku_test_ppt($node, "sites/all/modules/lokalkoenig/vku/pdfrender", "test-pptx");
        drupal_set_message('<a href="/sites/all/modules/lokalkoenig/vku/pdfrender/test-pptx.pptx" target="_blank">PPTX herunterladen ('. format_size(filesize("sites/all/modules/lokalkoenig/vku/pdfrender/test-pptx.pptx")) .')</a>');
        drupal_goto("vku/test");
     }
    
    
    
    // Generate PPT from Data
    
    // Generate PDF from DATA
    
    
return '<div class="well well-white"><h4>Medientypen-Übersicht</h4>'
     . '<p>Die hier generierten Export-Formate enthalten alle referenzierten Medientypen.</p>'
     . '<p>' . implode(" ", $actions) . '</p><hr />' . theme('table', array('header' => $header, 'rows' => $rows)) . '</div>';   
}