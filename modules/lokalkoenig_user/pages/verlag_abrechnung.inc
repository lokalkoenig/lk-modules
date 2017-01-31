<?php


/**
 *  Agenturabrechnungsfunktion
 *
 */      

function lokalkoenig_user_stats_agenturabrechnung($agentur){
  $options = array();

    $dbq = db_query("SELECT 
      YEAR(FROM_UNIXTIME(lizenz_date)) as year,
      COUNT(*) as count 
      FROM lk_vku_lizenzen 
      WHERE lizenz_verlag_uid != '0' AND node_uid='". $agentur -> uid ."'
      GROUP BY YEAR(FROM_UNIXTIME(lizenz_date))
      ORDER BY year DESC");
   foreach($dbq as $all){
      $all -> txt = $all -> year;
      $options[] = $all;
   
      $dbq2 = db_query("SELECT 
        MONTH(FROM_UNIXTIME(lizenz_date)) as month,
        COUNT(*) as count 
        FROM lk_vku_lizenzen 
        WHERE lizenz_verlag_uid != '0' AND YEAR(FROM_UNIXTIME(lizenz_date)) = '". $all -> year ."'
        AND node_uid='". $agentur -> uid ."' 
        GROUP BY MONTH(FROM_UNIXTIME(lizenz_date))
        ORDER BY month DESC");
      foreach($dbq2 as $all2){
         $all2 -> year = $all -> year; 
         $all2 -> txt = $all2 -> month . "-" . $all2 -> year;
         $options[] = $all2; 
      }  
   }
  
  
   drupal_set_title("Abrechnung");

   if(count($options) == 0){
      return '<div class="well">Keine Abrechnungsinformationen vorhanden.</div>';
   } 
    
    $have = $options[0]; 

   $form = drupal_get_form("lk_abrechnung_select_options", $options);
   if(isset($_GET["select"])){
      $select = $_GET["select"];
      
      if(isset($options[$select])){
         $have = $options[$select];
      }
   }
   
  
   
   $excel = false;
   
   if(isset($_GET["download"])){
     
      foreach($options as $test){
          if($test -> txt == $_GET["download"]){
               $excel = true; 
               $have = $test;
               break;
          }
      }
      
      if(isset($_GET["show"])){
         $excel = false; 
      }
      
   }
   
   $download = $have -> year;
   
   if(isset($have -> month)){
      $download = $have -> month . "-" . $have -> year;
   }
   
   
   $tables = _lk_agentur_abrechnung($agentur, $have, $excel);
 
    $output = '<div class="well clearfix">
      <div class="pull-right" style="width: 400px;">'. render($form) .'</div>
      <p>Die Abrechnung liefert Ihnen detailiert die lizensierten Kampagnen innerhalb des Abrechnungsmonats. Diese Abrechnung lassen sich alternativ in das Format Excel exportieren.</p>
    
      
   </div>
   ';
   
   foreach($tables as $table){
    
      $output .= '<hr /><h4>' . $table["title"] . '</h4>';
    
      if(isset($table["header"])){
        $output .= theme('table', array('header' => $table["header"],
                  	                  'rows' => $table["rows"] ));
      }
      else {
        $output .= theme('table', array('rows' => $table["rows"]));
      }
    }
   
   $exel = '<strong><img src="/sites/all/modules/lokalkoenig/excel.png" /></strong> ' .
     l("Als EXCEL-Dokument herunterladen", arg(0) . "/" . arg(1) . '/' . arg(2), array("query" => array("download" => $download)));
    
    $output .= '<hr /><div class="well">' . $exel  .'</div>';
    
    
    
    return $output;   
}




function _lk_agentur_abrechnung($agentur, $have, $excel = false){
  
  
  
  $table = array();
  
  $table[0] = array('title' => 'Übersicht');
  
  if(isset($have -> month)){
    $table[0]["rows"][] = array("Monat", _format_abrechnung_my($have -> month, $have -> year));
    
    $fn =  $have -> year . "_" . $have -> month;
    $title = _format_abrechnung_my($have -> month, $have -> year); 
  }
  else {
    $table[0]["rows"][] = array("Jahr", $have -> year);
    $fn =  $title = $have -> year;
    
  }

  $table[0]["rows"][] = array("Bestellte Lizenzen", $have -> count);
  
   if(isset($have -> month)){ 
   $dbq = db_query("SELECT lizenz_paket, count(*) as count FROM lk_vku_lizenzen 
    WHERE node_uid='". $agentur -> uid ."'
    AND 
      YEAR(FROM_UNIXTIME(lizenz_date)) = '". $have -> year ."' AND
      MONTH(FROM_UNIXTIME(lizenz_date)) = '". $have -> month ."' AND 
      lizenz_verlag_uid != '0' 
      GROUP BY lizenz_paket
      ORDER BY count DESC
    "); 
   }
   else {
      $dbq = db_query("SELECT lizenz_paket, count(*) as count FROM lk_vku_lizenzen 
    WHERE node_uid='". $agentur -> uid ."'
    AND 
      YEAR(FROM_UNIXTIME(lizenz_date)) = '". $have -> year ."' AND
      lizenz_verlag_uid != '0' 
      GROUP BY lizenz_paket
      ORDER BY count DESC"); 
   }
    
   $table[1] = array('title' => 'Lizenzen pro Paketgröße');
    
      
   foreach($dbq as $all){
      $term = taxonomy_term_load($all -> lizenz_paket);  
      $table[1]["rows"][] = array('Stückzahl Paket ' . $term -> name, $all -> count);
   }   
   
   
   // Kampagnen auflisten
    $table[2] = array('title' => 'Lizenzen pro Kampagne');
  
    if(isset($have -> month)){ 
   $dbq = db_query("SELECT nid, count(*) as count FROM lk_vku_lizenzen 
    WHERE node_uid='". $agentur -> uid ."'
    AND 
      YEAR(FROM_UNIXTIME(lizenz_date)) = '". $have -> year ."' AND
      MONTH(FROM_UNIXTIME(lizenz_date)) = '". $have -> month ."' AND
      lizenz_verlag_uid != '0' 
     GROUP BY nid
     ORDER BY count DESC
    "); 
    }
    else {
      $dbq = db_query("SELECT nid, count(*) as count FROM lk_vku_lizenzen 
    WHERE node_uid='". $agentur -> uid ."'
    AND 
      YEAR(FROM_UNIXTIME(lizenz_date)) = '". $have -> year ."' AND
      lizenz_verlag_uid != '0' 
     GROUP BY nid
     ORDER BY count DESC"); 
    
    }
    
    
    $table[2]["header"] = array("Anzahl Lizenzen", "Kampagne", "Nummer", "Paketgröße");
    
    foreach($dbq as $all){
      $node = node_load($all -> nid);
      $term = taxonomy_term_load($node->field_kamp_preisnivau['und'][0]['tid']);
      $table[2]["rows"][] = array($all -> count, l($node -> title, "node/" . $node -> nid), $node->field_sid['und'][0]['value'], $term -> name);
    }
    
   
    
   if($excel){
      _lk_download_excel("Abrechnung ". ucfirst($agentur -> name) ." " . $title, "abrech_". $agentur -> name ."_". $fn .".xls", $table);
   }  

return $table;
}






function _lk_get_monthes_abrechnung($sql_options){
  
  $options = array();
   $dbq = db_query("SELECT 
      YEAR(FROM_UNIXTIME(lizenz_date)) as year,
      COUNT(*) as count 
      FROM lk_vku_lizenzen 
      WHERE lizenz_verlag_uid != '0' AND 
      ". implode(" AND ", $sql_options) ."
      GROUP BY YEAR(FROM_UNIXTIME(lizenz_date))
      ORDER BY year DESC");
   foreach($dbq as $all){
      $all -> txt = $all -> year;
      $options[] = $all;
   
      $dbq2 = db_query("SELECT 
        MONTH(FROM_UNIXTIME(lizenz_date)) as month,
        COUNT(*) as count 
        FROM lk_vku_lizenzen 
        WHERE lizenz_verlag_uid != '0' 
        AND YEAR(FROM_UNIXTIME(lizenz_date)) = '". $all -> year ."'
        AND  ". implode(" AND ", $sql_options) ."
        GROUP BY MONTH(FROM_UNIXTIME(lizenz_date))
        ORDER BY month DESC");
      foreach($dbq2 as $all2){
         $all2 -> year = $all -> year; 
         $all2 -> txt = $all2 -> month . '-' . $all2 -> year;
         $options[] = $all2; 
      }  
   }

return $options;
}


function lokalkoenig_team_stats_abrechnung_page($team_id){
    
  $team = \LK\get_team($team_id);
  $v = $team->getVerlag();

    // First-Month - Get From Lizenzen-Tabelle
   $options = _lk_get_monthes_abrechnung(
    array("lizenz_verlag_uid='".  $v . "'", 
          "lizenz_team='" . $team_id . "'")
   );
   
   if(count($options) == 0){
      return '<div class="well">Keine Abrechnungsinformationen vorhanden.</div>';
   } 
   
   $form = drupal_get_form("lk_abrechnung_select_options", $options);
   $have = $options[0];
   
   if(isset($_GET["select"])){
      $select = $_GET["select"];
      
      if(isset($options[$select])){
         $have = $options[$select];
      }
   }
  
  
     $form = drupal_get_form("lk_abrechnung_select_options", $options);
  
   $have = $options[0];
   
   if(isset($_GET["select"])){
      $select = $_GET["select"];
      
      if(isset($options[$select])){
         $have = $options[$select];
      }
   }
   
   
   
   $excel = false;
   
   if(isset($_GET["download"])){
     
      foreach($options as $test){
          if($test -> txt == $_GET["download"]){
               $excel = true; 
               $have = $test;
               break;
          }
      }
      
      if(isset($_GET["show"])){
         $excel = false; 
      }
      
   }
   
   $download = $have -> year;
   
   if(isset($have -> month)){
      $download = $have -> month . "-" . $have -> year;
   }
   
   //drupal_set_title("Abrechnung");
   
   
   $tables = _lk_verlag_abrechnung($v, $have, $excel, $team_id);
   
    $output = '<div class="well clearfix">
      <div class="pull-right" style="width: 400px;">'. render($form) .'</div>
      <p>Hier sehen Sie die lizensierten Kampagnen innerhalb des ausgewählten Abrechnungsmonats. Die Liste kann in Excel exportiert werden.</p>
   </div>
   ';
   
   foreach($tables as $table){
    
      $output .= '<hr />
      <div class="panel panel-info">
      <div class="panel-body">
      <h4>' . $table["title"] . '</h4>';
    
      if(isset($table["header"])){
        $output .= theme('table', array('header' => $table["header"],
                  	                  'rows' => $table["rows"] ));
      }
      else {
        $output .= theme('table', array('rows' => $table["rows"]));
      }
      
      $output .= '</div></div>';
    }
   
   $exel = '<strong><img src="/sites/all/modules/lokalkoenig/excel.png" /></strong> &nbsp;&nbsp;' .
     l("Als EXCEL-Dokument herunterladen", arg(0) . "/" . arg(1) . '/' . arg(2), array("query" => array("download" => $download)));
    
    
    $output .= '<hr /><div class="well">' . $exel  .'</div>';
        
    return $output;   
}


/** 
 *
 *  Abrechnung Verlag
 *
 */   

function lokalkoenig_user_stats_page_abrechnung($verlag){
  
  // Current Month
  
  
  // First-Month - Get From Lizenzen-Tabelle
  $options = array();
   $dbq = db_query("SELECT 
      YEAR(FROM_UNIXTIME(lizenz_date)) as year,
      COUNT(*) as count 
      FROM lk_vku_lizenzen 
      WHERE lizenz_verlag_uid != '0' AND 
      lizenz_verlag_uid='". $verlag -> uid ."'
      GROUP BY YEAR(FROM_UNIXTIME(lizenz_date))
      ORDER BY year DESC");
   foreach($dbq as $all){
      $all -> txt = $all -> year;
      $options[] = $all;
   
      $dbq2 = db_query("SELECT 
        MONTH(FROM_UNIXTIME(lizenz_date)) as month,
        COUNT(*) as count 
        FROM lk_vku_lizenzen 
        WHERE lizenz_verlag_uid != '0' AND YEAR(FROM_UNIXTIME(lizenz_date)) = '". $all -> year ."'
        AND lizenz_verlag_uid='". $verlag -> uid ."' 
        GROUP BY MONTH(FROM_UNIXTIME(lizenz_date))
        ORDER BY month DESC");
      foreach($dbq2 as $all2){
         $all2 -> year = $all -> year; 
         $all2 -> txt = $all2 -> month . '-' . $all2 -> year;
         $options[] = $all2; 
      }  
   }
  
   if(count($options) == 0){
    return '<div class="well">Keine Abrechnungsinformationen vorhanden.</div>';
   } 
    
   $form = drupal_get_form("lk_abrechnung_select_options", $options);
  
   $have = $options[0];
   
   if(isset($_GET["select"])){
      $select = $_GET["select"];
      
      if(isset($options[$select])){
         $have = $options[$select];
      }
   }
   
   
   
   $excel = false;
   
   if(isset($_GET["download"])){
     
      foreach($options as $test){
          if($test -> txt == $_GET["download"]){
               $excel = true; 
               $have = $test;
               break;
          }
      }
      
      if(isset($_GET["show"])){
         $excel = false; 
      }
      
   }
   
   $download = $have -> year;
   
   if(isset($have -> month)){
      $download = $have -> month . "-" . $have -> year;
   }
   
   drupal_set_title("Abrechnung");
   
   
   $tables = _lk_verlag_abrechnung(user_load($verlag), $have, $excel);
   
    $output = '<div class="well clearfix">
      <div class="pull-right" style="width: 400px;">'. render($form) .'</div>
      <p>Die Abrechnung liefert Ihnen detailiert die lizensierten Kampagnen innerhalb des Abrechnungsmonats. Diese Abrechnung lassen sich alternativ in das Format Excel exportieren.</p>
    
      
   </div>
   ';
   
   foreach($tables as $table){
    
      $output .= '<hr />
      <div class="panel panel-info">
      <div class="panel-body">
      <h4>' . $table["title"] . '</h4>';
    
      if(isset($table["header"])){
        $output .= theme('table', array('header' => $table["header"],
                  	                  'rows' => $table["rows"] ));
      }
      else {
        $output .= theme('table', array('rows' => $table["rows"]));
      }
      
      $output .= '</div></div>';
    }
   
   $exel = '<strong><img src="/sites/all/modules/lokalkoenig/excel.png" /></strong> &nbsp;&nbsp;' .
     l("Als EXCEL-Dokument herunterladen", arg(0) . "/" . arg(1) . '/' . arg(2), array("query" => array("download" => $download)));
    
    
    
    
    $output .= '<hr /><div class="well">' . $exel  .'</div>';
        
    return $output;   
}

function _lk_verlag_abrechnung($verlag, $have, $excel, $team_id = 0){
  
  $table = array();
  
  $table[0] = array('title' => 'Übersicht');
  
  if(isset($have -> month)){
    $table[0]["rows"][] = array("Monat", _format_abrechnung_my($have -> month, $have -> year));
    
    $fn =  $have -> year . "_" . $have -> month;
    $title = _format_abrechnung_my($have -> month, $have -> year); 
  }
  else {
    $table[0]["rows"][] = array("Jahr", $have -> year);
    $fn =  $title = $have -> year;
    
  }

  $table[0]["rows"][] = array("Bestellte Lizenzen", $have -> count);
  
  $extras = array();
  $extras[] = "lizenz_verlag_uid='". $verlag -> uid ."'";
  $extras[] = "YEAR(FROM_UNIXTIME(lizenz_date)) = '". $have -> year ."'";
  
  if(isset($have -> month)){ 
    $extras[] = "MONTH(FROM_UNIXTIME(lizenz_date)) = '". $have -> month ."'";
  }
  
  if($team_id){
    $extras[] = "lizenz_team='". $team_id ."'";
  }
   
 
  $dbq = db_query("SELECT lizenz_paket, count(*) as count 
      FROM lk_vku_lizenzen 
    WHERE 
      ". implode(" AND ", $extras) ." AND
      lizenz_verlag_uid != '0' 
      GROUP BY lizenz_paket
      ORDER BY count DESC"); 
    
   $table[1] = array('title' => 'Lizenzen pro Paketgröße');
      
   foreach($dbq as $all){
      $term = taxonomy_term_load($all -> lizenz_paket);  
      $table[1]["rows"][] = array('Stückzahl Paket ' . $term -> name, $all -> count);
   }   
   
   // Lizenzen pro Ausgabe (Doppelte Ausgaben möglich)
  $extras = array();
  $extras[] = "l.lizenz_verlag_uid='". $verlag -> uid ."'";
  $extras[] = "YEAR(FROM_UNIXTIME(l.lizenz_date)) = '". $have -> year ."'";
  
  if(isset($have -> month)){ 
    $extras[] = "MONTH(FROM_UNIXTIME(l.lizenz_date)) = '". $have -> month ."'";
  }
   
  if($team_id){
    $extras[] = "l.lizenz_team='". $team_id ."'";
  } 
   
   
   /**
  $dbq = db_query("SELECT 
    a.ausgabe_id, count(*) as count 
    FROM lk_vku_lizenzen l, lk_vku_lizenzen_ausgabe a 
    WHERE 
       ". implode(" AND ", $extras) ." AND l.id = a.lizenz_id AND 
      lizenz_verlag_uid != '0' 
      GROUP BY a.ausgabe_id
      ORDER BY count DESC"); 
   
  $table[1] = array('title' => 'Lizenzen pro Ausgabe');
  foreach($dbq as $all){
    $table[1]["rows"][] = array('Ausgabe ' . lk_get_ausgaben_title($all -> ausgabe_id), $all -> count);
  }   */ 
   
   
  
    $extras = array();
    $extras[] = "l.lizenz_verlag_uid='". $verlag -> uid ."'";
    $extras[] = "YEAR(FROM_UNIXTIME(l.lizenz_date)) = '". $have -> year ."'";
    
    if(isset($have -> month)){ 
      $extras[] = "MONTH(FROM_UNIXTIME(l.lizenz_date)) = '". $have -> month ."'";
    }
     
    if($team_id){
      $extras[] = "l.lizenz_team='". $team_id ."'";
    } 
    
   $dbq = db_query("SELECT * FROM lk_vku_lizenzen as l
    WHERE 
    ". implode(" AND ", $extras)  . "
    AND 1 
     ORDER BY lizenz_date ASC
    "); 
    
    $header = array("Datum", "Bestellt von", "Kampagne", "Titel", "Paketgröße", "Ausgaben", 'Ausgaben-Stückzahl');
    
    $overall = array();
    
    $sammel = array();
    
    foreach($dbq as $all){
      $sammel[$all -> lizenz_team][] = $all;  
    }  
    
    
    $tablecountsf = 3;
    
    while(list($key, $val) = each($sammel)){
     
      
      if(!$key){
          $title = 'Allgemein / Kein Team';
        
      }
      else{
        $team = \LK\get_team($key);
        $title = $team ->getTitle();
      }
      
      $overall = array(); 
       foreach($val as $all){
          $node = node_load($all -> nid);
          $term = taxonomy_term_load($all -> lizenz_paket);
          $account = user_load($all -> lizenz_uid);
        
          $ausgaben = array();
          $dbq3 = db_query("SELECT ausgabe_id FROM lk_vku_lizenzen_ausgabe WHERE lizenz_id='". $all -> id ."'");
          foreach($dbq3 as $grabbing){
               $ausgaben[] = lk_get_ausgaben_title_kurz($grabbing -> ausgabe_id);
          
          }
          
        
        
          $overall[] = array(format_date($all -> lizenz_date), (l($account -> name, "user/" . $account -> uid)), $node->field_sid['und'][0]['value'], (l($node -> title, "node/" . $node -> nid)), $term -> name,  implode(", ", $ausgaben), (string)count($ausgaben));
       }
      
      $table[$tablecountsf] = array(
        'title' => 'Detaillierte Auflistung ('. $title .')',
        'header' => $header,
        'rows' => $overall,
      ); 
      
    
      $tablecountsf++;
    }
    
    
    if($excel){
      _lk_download_excel("Abrechnung ". ucfirst($verlag -> name) ." " . $title, "abrechnung_". $verlag -> name ."_". $fn .".xls", $table);
    }  
  
  
   return $table;
}


function lk_abrechnung_select_options_submit($form, $form_state){
  
   $select = $form_state["values"]["select"];
   
   drupal_goto("user/". arg(1) ."/" . arg(2), array("query" => array("select" => $select)));
   drupal_exit();
}


function _format_abrechnung_my($month, $year){
 
  $time = strtotime($year . "-" . $month . "-1");
   
 return format_date($time, 'custom', "F Y");
}


function lk_abrechnung_select_options($form, $form_state, $options){

  $select = array();
  $form['#method'] = 'post';
  
  foreach($options as $opt){
    if(isset($opt -> month)){
      $select[] = _format_abrechnung_my($opt -> month, $opt -> year);
    }
    else {
      $select[] = '- '. $opt -> year . ' -';
    }
  }
  
  
  
  
  $form['select'] = array(
       '#type' => 'select',
       '#title' => ('Abrechnungszeitraum'),
       '#options' => $select,
       //'#default_value' => $category['selected'],
       //'#description' => t('Set this to <em>Yes</em> if you would like this category to be selected by default.'),
   );

 if(isset($_GET["select"])){
  $select = $_GET["select"];
  
  if(isset($options[$select])){
     $form['select']["#default_value"] = $select;
  }
 }


 $form['submit'] = array('#type' => 'submit', '#value' => ('Auswahl'));

return $form;
}

function _lk_download_excel($title, $filename, $table){
    $objPHPExcel = new PHPExcel();
    
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
    
    $objPHPExcel->getActiveSheet()->setTitle('Abrechnung');
    
    $cols = array("A", "B", "C", "D", "E", "F", 'G', 'H', 'I', 'J', 'K', 'L', 'M');
    
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Lokalkönig: ' . $title); 
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', 'Generiert am:'); 
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', date("d.m.Y")); 
    
    
    $rows = 8;
    foreach($table as $tab){
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $rows, $tab["title"]); 
 
      $objPHPExcel->getActiveSheet()->getStyle('A' . $rows)->getFont()->setSize(14);
      $objPHPExcel->getActiveSheet()->getStyle('A' . $rows)->getFont()->setBold(true);
      
      $objPHPExcel->getActiveSheet()->getStyle('A'. $rows .':H'. $rows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
      $objPHPExcel->getActiveSheet()->getStyle('A'. $rows .':H'. $rows)->getFill()->getStartColor()->setARGB('FFEEEEEE');
   
      
      $rows++;
      
      if(isset($tab["header"])){
          for($x = 0; $x < count($tab["header"]); $x++) {
             $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$x] . $rows, strip_tags($tab["header"][$x])); 
             $objPHPExcel->getActiveSheet()->getStyle($cols[$x] . $rows)->getFont()->setBold(true);
          }
          
          $rows++;
      
      }
      
      
      for($x = 0; $x < count($tab["rows"]); $x++) {
         for($e = 0; $e < count($tab["rows"][$x]); $e++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cols[$e] . $rows, strip_tags($tab["rows"][$x][$e])); 
         }
          $rows++;
      }
      
      $rows++; $rows++;
    }
    
    
   ob_clean();
   
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'. $filename .'"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');      
    exit;
}

?>