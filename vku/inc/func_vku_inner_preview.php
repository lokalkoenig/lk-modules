<?php

/**
 * Vku create Preview from Line-Item Link
 * Changed 2015-11-09, added Template Support
 * 
 * @global type $user
 * @param type $vku_id
 * @param type $line_item
 */

function vku_show_line_item_preview($vku_id, $line_item){
global $user;

	$vku = new VKUCreator($vku_id);
	$vku_status = $vku ->getStatus();
        
        if(!in_array($vku_status, array('active', 'template'))){
            die("Alter Link");	  
        }
       
	$author = $vku -> getAuthor();
	if($author != $user -> uid AND !lk_is_moderator()){
                 $vku ->logEvent('error', 'Kein Zugriff');
		die("Sie haben keinen Zugriff");	
	}

	$page = $vku -> getPage($line_item);
	if(!$page){
                $vku ->logEvent('error', 'Kein Zugriff');
		die("Sie haben keinen Zugriff");
	}

	$pdf = vku_generate_get_pdf_object($vku);	
	$mod = $page["data_module"];
	$func_name = 'vku_generate_pdf_' . $mod;
        
	if(function_exists($func_name)){
		$func_name($vku, $page, $pdf);
	}

	drupal_get_messages();
        
        $vku ->logEvent('pdf-preview', 'Seite ('.  $line_item .'/'. $mod .'/'. $page["data_class"] .') wurde generiert.');
	ob_clean();
        $pdf->Output();
	drupal_exit();
}
?>