<?php


function lokalkoenig_user_overallpage(){
 
  $options = array();
  $dbq = db_query("SELECT 
      YEAR(FROM_UNIXTIME(lizenz_date)) as year,
      COUNT(*) as count 
      FROM lk_vku_lizenzen 
      WHERE lizenz_verlag_uid != '0' 
      GROUP BY YEAR(FROM_UNIXTIME(lizenz_date))
      ORDER BY year DESC");
   foreach($dbq as $all){
      $options[] = $all;
   
      $dbq2 = db_query("SELECT 
        MONTH(FROM_UNIXTIME(lizenz_date)) as month,
        COUNT(*) as count 
        FROM lk_vku_lizenzen 
        WHERE lizenz_verlag_uid != '0' AND YEAR(FROM_UNIXTIME(lizenz_date)) = '". $all -> year ."' 
        GROUP BY MONTH(FROM_UNIXTIME(lizenz_date))
        ORDER BY month DESC");
      foreach($dbq2 as $all2){
         $all2 -> year = $all -> year; 
         $options[] = $all2; 
      }  
   }
 
   drupal_set_title("Abrechnung");
   
   if(count($options) == 0){
      return 'Keine Abrechnungsinformationen vorhanden.';
   } 
   
   $have = $options[0]; 

   $form = drupal_get_form("lk_abrechnung_select_options", $options);
   if(isset($_GET["select"])){
      $select = $_GET["select"];
      
      if(isset($options[$select])){
         $have = $options[$select];
      }
   }
   
   $overall = array();
   
   if(isset($have -> month))
   $overall[] = array('<strong>Monat</strong>', _format_abrechnung_my($have -> month, $have -> year));
   
   $overall[] = array('<strong>Bestellte Lizenzen</strong>', $have -> count);
  
  $aggregation = "YEAR(FROM_UNIXTIME(lizenz_date)) = '". $have -> year ."'";
  $arg = array('download' => $have -> year);
  
  if(isset($have -> month)) {
    $aggregation .= " AND MONTH(FROM_UNIXTIME(lizenz_date)) = '". $have -> month ."'"; 
    $arg = array('download' => $have -> month . "-" . $have -> year);
  }
  
  
  
   $dbq = db_query("SELECT lizenz_paket, count(*) as count FROM lk_vku_lizenzen 
    WHERE 
      ". $aggregation ." AND 
      lizenz_verlag_uid != '0' 
      GROUP BY lizenz_paket
      ORDER BY count DESC
    "); 
    
   foreach($dbq as $all){
      $term = taxonomy_term_load($all -> lizenz_paket);
      $overall[] = array('Stückzahl Paket ' . $term -> name, $all -> count);
   }   
   
   $output = '<div class="well clearfix">
   <div class="pull-right" style="width: 400px;">'. render($form) .'</div>
   
   <h4>Übersicht</h4>
   <p>In der folgenden Übersicht sehen Sie die verkauften Lizenzen pro Monat.</p>
   
   </div>';
   
   $output .= theme('table', array('header' => array(),
                  	         'rows' => $overall ));
  
  
    // Aggregieren nach Verlag
    $dbq = db_query("SELECT lizenz_verlag_uid, count(*) as count FROM lk_vku_lizenzen 
    WHERE 
      ". $aggregation ." AND 
      lizenz_verlag_uid != '0' 
      GROUP BY lizenz_verlag_uid
      ORDER BY count DESC
    "); 
    
    
    $overall = array();
    foreach($dbq as $all){
      
       $overall[] = array(
              \LK\u($all -> lizenz_verlag_uid), 
              $all -> count . " Lizenzen", 
              l("Ansehen", "user/" . $all -> lizenz_verlag_uid . "/abrechnung", array("query" => $arg + array('show' => 1))), 
              l('<img src="/sites/all/modules/lokalkoenig/excel.png" />', "user/" . $all -> lizenz_verlag_uid . "/abrechnung", array("query" => $arg, "html" => true))
       ); 
    
    }
     
     $output .= '<br /><br /><div class="well"><h4>Nach Verlag</h4></div>' .theme('table', array('header' => array(),
                  	         'rows' => $overall ));
  
  
  
  // Aggregieren nach Verlag
    $dbq = db_query("SELECT node_uid, count(*) as count FROM lk_vku_lizenzen 
    WHERE 
      ". $aggregation ." AND 
      lizenz_verlag_uid != '0' 
      GROUP BY node_uid
      ORDER BY count DESC
    "); 
    
    
    $overall = array();
    foreach($dbq as $all){
       $account = \LK\get_user($all -> node_uid);
       $overall[] = array(
              l(ucfirst($account ->getUsername()), "user/" . $account ->getUid()),
              $all -> count . " Lizenzen", 
              l("Ansehen", "user/" . $account -> uid . "/agenturabrechnung", array("query" => $arg + array('show' => 1))), 
              l('<img src="/sites/all/modules/lokalkoenig/excel.png" />', "user/" . $account -> uid . "/agenturabrechnung", array("query" => $arg, "html" => true))
       );
    }
     
     $output .= '<br /><br /><div class="well"><h4>Nach Agentur</h4></div>' .theme('table', array('header' => array(),
                  	         'rows' => $overall ));
  

    $output = '<div class="panel panel-default panel-info"><div class="panel-body">' . $output . '</div></div>'; 

return $output;
}

function _format_abrechnung_my($month, $year){
 
  $time = strtotime($year . "-" . $month . "-1");
   
 return format_date($time, 'custom', "F Y");
}

function lk_abrechnung_select_options_submit($form, $form_state){
  
   $select = $form_state["values"]["select"];
   
   drupal_goto($_GET['q'], array("query" => array("select" => $select)));
   drupal_exit();
}


function lk_abrechnung_select_options($form, $form_state, $options){
  $select = array();
  $form['#method'] = 'post';
  
  foreach($options as $opt){
    if(isset($opt -> month)){
       $select[] = _format_abrechnung_my($opt -> month, $opt -> year) . ' ['. $opt -> count .']'; 
    }
    else $select[] = '-' . $opt -> year . '- ['. $opt -> count .']';
  }
  
  
  
  
  $form['select'] = array(
       '#type' => 'select',
       '#title' => ('Abrechnungsmonat'),
       '#options' => $select,
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

?>