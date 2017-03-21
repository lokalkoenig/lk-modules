<?php
namespace LK\Tests;

/**
 * Description of TestVKU
 *
 * @author Maikito
 */
class TestVKU extends TestCase {
    //put your code here
   
    function build() {
        //$this ->printLine('', 'Formate');
        
        $terms = taxonomy_get_tree(5);
        $medias = array(); 
        $header = array('TID', 'Name', 'Medias', 'VKU-Anzeigen-Breite', 'Seiten', "Beispiel");
        $rows = array();
    
        foreach($terms as $data):
        
        // Haupt-Kategorie
        if($data->parents[0] == 0) {
            $rows[] = array("", '<strong>'. $data -> name .'</strong>', "", "", "", "");
            
            continue;
        }
        
        $pages = $ab = '<em class="small">n/a</em>';
        $term = taxonomy_term_load($data -> tid);
        
        if(isset($term->field_medientyp_pdf_width['und'][0]['value'])){
            $ab = $term->field_medientyp_pdf_width['und'][0]['value'];   
        }
        
        if(isset($term->field_medientyp_vku_pages['und'][0]["value"])){
           $pages = $term->field_medientyp_vku_pages['und'][0]["value"]; 
        }
        
        $dbq = db_query("SELECT count(*) as count FROM field_data_field_medium_typ WHERE field_medium_typ_tid='". $data -> tid ."'");
        $all = $dbq -> fetchObject();
        
        $beispiel = '';
        
        $dbq2 = db_query("SELECT entity_id FROM field_data_field_medium_typ WHERE field_medium_typ_tid='". $data -> tid ."' AND entity_id > '210'  ORDER BY entity_id ASC LIMIT 1");
        $all2 = $dbq2 -> fetchObject();
        if($all2){
            $entity = entity_load_single("medium", $all2 -> entity_id);
            $nid = $entity->field_medium_node['und'][0]['nid'];
            $node = node_load($nid);
            $medias[] = $entity;
            $beispiel = l($node -> title, "node/" . $nid) . " (NODE:". $nid ." / ENTITY:". $entity -> id  .")";
        }
        
        $rows[] = array($term -> tid, l($term-> name, 'taxonomy/term/'. $term->tid .'/edit', array("query" => drupal_get_destination())) . '<br /><small>' . $term -> description . '</small>', $all -> count, $ab, $pages, $beispiel);
        endforeach;
        
        $this->append(theme('table', array('header' => $header, 'rows' => $rows)));

        if(isset($_GET['render']) AND $_GET['render'] == 'pdf'){
          $node = node_load(88);
          $node -> title = 'Test-Kampagne';
          $node -> medien = $medias;
                
          $pdf = \LK\PDF\PDF_Loader::renderTestNode($node, FALSE);
          $mydir = 'public://test'; 
          file_prepare_directory($mydir, FILE_CREATE_DIRECTORY);

          $dir = drupal_realpath($mydir);
          $pdf->Output($dir . "/test-pdf.pdf", 'F');
          $size = filesize($dir . "/test-pdf.pdf");
          $this ->printInfo("Sie koennen nun die Test-PDF " .l('Test-PDF', 'sites/default/files/test/test-pdf.pdf') . ' ('.format_size($size) .') herunterladen.');
        }

        if(isset($_GET['render']) AND $_GET['render'] == 'ppt'){
          $node = node_load(88);
          $node -> title = 'Test-Kampagne';
          $node -> medien = $medias;

          $ppt = \LK\PPT\PPTX_Loader::load();
          $ppt->setAccount(\LK\current());
          
          $node_renderer = new \LK\PPT\Pages\RenderNode($ppt);
          \LK\VKU\Pages\PageKampagne::_vku_load_vku_settings($node);
          $node_renderer->render($node);

          $file_name = 'test-ppt';
          $mydir = 'public://test';
          $dir = drupal_realpath($mydir);
        
          $rendered_file_name = \LK\PPT\PPTX_Loader::save($ppt, $dir, $file_name);
          $size = filesize($dir . "/" . $rendered_file_name);
          $this ->printInfo("Sie koennen nun die Test-PPT " .l('Test-PPT', 'sites/default/files/test/' . $rendered_file_name) . ' ('.format_size($size) .') herunterladen.');
        }

        $actions = array(
            '<a href="'. url('backoffice/test', array('query' => array('case' => __CLASS__, "render" => 'pdf'))) .'" class="btn btn-primary"><span class="glyphicon glyphicon-file"></span> Test-PDF Generieren</a>',
            '<a href="'. url('backoffice/test', array('query' => array('case' => __CLASS__, "render" => 'ppt'))) .'" class="btn btn-primary"><span class="glyphicon glyphicon-file"></span> Test-PPT Generieren</a>'
        );
        
        $this->append('<hr />' . implode(' ', $actions));
    }
}
