<?php

define('LK_TD_WIDTH', 20);
define('LK_TD_HEIGHT', 20);
define ('CR', chr(13).chr(10));     //CR mit [CR][LF] definieren 
define ('LZ', '    ');         


function build_calendar($month,$year,$title) {

$zeitstempel = strtotime($year . "-" . $month . "-01 01:00:00"); 
$monat = date("F", $zeitstempel);            //aktuellen Monat ermitteln 
$monatskalender = '<div class="kalender">'.CR; 
$tag_der_woche = date("N", $zeitstempel); //für die generierung von Leerzellen zu Beginn eines Monats 

//Tabellenkopf mit Monat, KW und Wochentagen erstellen 
$monatskalender .= '<table cellspacing="0">'.CR; 
$monatskalender .= LZ.'<tr class="monat"><th width="'. LK_TD_WIDTH * 8 .'">'.$title.'</th></tr>'.CR; 
$monatskalender .= LZ.'<tr bgcolor="#EEE"><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">KW</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">Mo</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">Di</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">Mi</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">Do</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">Fr</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">Sa</td><td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" bgcolor="#e2e4e5">So</td></tr>'.CR; 

//Ende des Tabellenkopfes 
while ($monat == date("F", $zeitstempel)) {    //Schleife wird so lange durchlaufen, bis sich der Monat ändert 
    $aktuelle_kw = date("W", $zeitstempel); 
    $monatskalender .= '<tr>'.CR.LZ.'<td width="'. LK_TD_WIDTH .'" height="'. LK_TD_HEIGHT .'" class="kw" bgcolor="#e2e4e5">'.$aktuelle_kw.'</td>' ; 
    
    if ($tag_der_woche > 1 && date("d", $zeitstempel) == 1) { 
        for ($i = $tag_der_woche; $i > 1; $i--) { 
            $monatskalender .= '<td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'"> </td>'; 
        } 
    } 
 
    while ($aktuelle_kw == date("W", $zeitstempel)) {    //Schleife wird so lange durchlaufen, bis sich die KW ändert 
        $temp_klasse1 = '#fcd5b4'; 
        
        $temp_klasse1 = '';
        $test_saso = date("N", $zeitstempel);
        
        if($test_saso == 6 OR $test_saso == 7){
            $temp_klasse1 = '#fcd5b4'; 
        }
        
        $klasse = ' bgcolor="'.$temp_klasse1 .'"'; 
        $monatskalender .= '<td height="'. LK_TD_HEIGHT .'" width="'. LK_TD_WIDTH .'" '.$klasse.'>'.date("d", $zeitstempel).'</td>'; 
        $zeitstempel = $zeitstempel + (60*60*24); 
        
        if (date("j", $zeitstempel) == 1) break;    //Abbruch, wenn sich wärend der Woche der Monat ändert 
    
    } 
    
    $monatskalender .= '</tr>'.CR ; 
} 

$monatskalender .= '</table>'.CR ; 
$monatskalender .= '</div>'.CR ; 


return $monatskalender; 
}

$pdf->AddPage();
$pdf->SetTextColor(69, 67, 71);
$pdf -> SetTopMargin(30);
$pdf -> SetLeftMargin(25);
$pdf -> SetRightMargin(25);

$pdf -> Ln(15); 

$pdf->SetFont(VKU_FONT,'B',28);

$dates = array();
$years = array();

$dates[] = array(date("m"), date("Y"));

for($x = 1; $x <= 7; $x++){
  $dates[] = array(
    date("m", strtotime("+". $x ." month")), 
    date("Y", strtotime("+". $x ." month"))
  ); 
}

foreach($dates as $date){
    $years[$date[1]] = $date[1];
}


$pdf->MultiCell(0, 0, "Kampagnenplanung " . implode("/", $years), 0, 'L', 0); 
$pdf -> Ln(15); 
$pdf->SetFont(VKU_FONT,'',6);

$dateComponents = getdate();
$curr = date('m');

$monate = array(
0 => 'Januar',
1 => 'Februar',
2 => 'März',
3 => 'April',
4 => 'Mai',
5 => 'Juni',
6 => 'Juli',
7 => 'August',
8 => 'September',
9 => 'Oktober',
10 => 'November',
11 => 'Dezember');


$x = 0;
foreach($dates as $date){
 $int =  $date[0];
 $pdf->WriteHTML(build_calendar( $date[0], $date[1], $monate[$int-1] . " " . $date[1]));
 
 if($x != 3 AND $x != 7) $pdf -> Ln(5);  
 
 if($x == 3){
    $pdf -> SetXY(0 , 60);
    $pdf -> SetLeftMargin((297 / 2) + 5 );
 }  
 
 $x++;
}





$block_add = 30.4;
$breite = 138 - 75; 
$rechts_start = 74;
$linienhoehe = 5.8;

$pdf->SetDrawColor(100, 100, 100); 
$pdf->SetLineWidth(0.1); 
$pdf->SetFont(VKU_FONT,'',8);

for($i = 0; $i < 4; $i++){

  // Linke Seite
  $pdf -> SetXY($rechts_start , 57  + ($i *$block_add));
  $pdf->Cell(50, 10, 'Thema:');
  for($x = 0; $x < 4; $x++){
    $pdf -> Line($rechts_start + 1 , 65.5 + ($i * $block_add) + $x * $linienhoehe, 75 + $breite, 65.5 + ($i * $block_add) + $x * $linienhoehe);
  }
 
  // Rechte Seite
  $pdf -> SetXY(204 , 57  + ($i *$block_add));
  $pdf->Cell(50, 10, 'Thema:');
  for($x = 0; $x < 4; $x++){
    $pdf -> Line(205 , 65.5 + ($i * $block_add) + $x * $linienhoehe, 205 + $breite, 65.5 + ($i * $block_add) + $x * $linienhoehe);
  }
}

// Reset Left Margin
$pdf -> SetLeftMargin(0);

?>