<div class="list-group" style="width: 100%;">
<?php


 if(lk_is_admin() OR lk_is_moderator() AND arg(1) != 'add'){
     
     $items = array(); 
      
     $items[] = array(
        "title" => "Admin: Log",
        "text" => "Ereignisanzeige",
        "link" => "node/" . $node -> nid . "/log",
        "icon" => "search",
        "showlink" => true, 
       ); 
 
 
     $items[] = array(
        "title" => "Moderation",
        "text" => "Kampagne freischalten...",
        "link" => "node/" . $node -> nid . "/admin",
        "icon" => "flag",
        "showlink" => true,  
       ); 
       
    $items[] = array(
        "title" => "PLZ-Sperre",
        "text" => "PLZ-Sperre editieren",
        "link" => "node/" . $node -> nid . "/plz",
        "icon" => "flag",
        "showlink" => true,  
       );     
   
   /**
   if($node -> lkstatus == 'deleted'){
       $items[] = array(
        "title" => "Kampagne löschen",
        "text" => "Daten der Kampagne unwiderruflich löschen",
        "link" => "node/" . $node -> nid . "/delete",
        "icon" => "trash",
        "showlink" => true,  
       );     
   
      if(arg(2) == 'delete'){
       $items[3]["active"] = true; 
      } 
   
   }
   
   */
   
   if(arg(2) == 'admin'){
      $items[1]["active"] = true; 
   }
    if(arg(2) == 'log'){
       $items[0]["active"] = true; 
    }
    
    
    if(arg(2) == 'plz'){
       $items[2]["active"] = true; 
    }
    
    
    
   
       
   foreach($items as $item){
  ?>
  <a href="<?php if(isset($item["showlink"])) print url($item["link"]); else print 'javascript:void(0);' ?>" class="list-group-item<?php if(isset($item["active"])) print ' active'; ?>">
    <h4 class="list-group-item-heading"><span class="glyphicon glyphicon-<?php print $item["icon"]; ?>"></span> <?php print $item["title"]; ?></h4>
    <?php
     if(arg(2) != 'presentation'){
     ?>
        <p class="list-group-item-text small"><?php print $item["text"]; ?></p>
    <?php } ?>
  </a>
 
 
   <?php
 }    
 
 ?>
 </div>
  <div class="list-group">
       
 <?php
 }


?>





<?php
 $items = array(); 
 
 $items[] = array(
  "title" => "1. Kampagne initialisieren",
  "text" => "Erstellen Sie in diesem Schritt die Kampagne und uploaden Sie gleich danach die Quelldateien.",
  "link" => "node/add/kampagne",
  "icon" => "plus" 
 );
 
 $items[] = array(
  "title" => "2. Medien hochladen",
  "text" => "Laden Sie die Quelldateien zu der Kampagne einzeln hoch.",
  "link" => "node/" . $node -> nid ."/media",
  "icon" => "cloud-upload",
   //"secondary" => array("link" => "node/" . $node -> nid ."/media", 
 );
   
 $items[] = array(
  "title" => "3. Freischalten lassen",
  "text" => "Sehen Sie Ihre fertige Kampagne und beantragen Sie die Freischaltung",
  "link" => "node/" . $node -> nid,
  "icon" => "saved" 
 ); 





 if(arg(1) == "add"){
    $items[0]["active"] = true;
    $items[0]["showlink"] = false;
 }
 else {
    if(arg(2) == 'edit') $items[0]["active"] = true; 
 
     $items[0]["showlink"] = true;  
     $items[0]["title"] = '1. Kampagnendaten bearbeiten';  
      $items[0]["link"] = 'node/' . $node -> nid . "/edit";  
    
 
    $items[1]["showlink"] = true;
 
    if(isset($node -> medien)){
       $items[2]["showlink"] = true;
    }
 
   if(arg(2) == "addmedia" OR arg(2) == "media"){
      $items[1]["active"] = true;
   }
   
   if(!arg(2)){
       $items[2]["active"] = true;
   
   }
      
 }
 
 // Kampagne ist bereits freigeschalten
 if(lk_is_moderator() AND $node -> status){
     $items[2]["title"] = "3. Portal-Ansicht";
     $items[2]["text"] = "Die Kampagne im Portal";
 
 }

 if($node -> lkstatus == 'deleted'){
    $items = array();
      $items[] = array(
        "title" => "Kampagnenansicht",
        "text" => "Die Ansicht der Kampagne im Portal",
        "link" => "node/" . $node -> nid . '/status',
        "icon" => "plus",
        'showlink' => true
    );
 }

 foreach($items as $item){
  ?>
  <a href="<?php if(isset($item["showlink"])) print url($item["link"]); else print 'javascript:void(0);' ?>" class="list-group-item<?php if(isset($item["active"])) print ' active'; ?>">
    <h4 class="list-group-item-heading"><span class="glyphicon glyphicon-<?php print $item["icon"]; ?>"></span> <?php print $item["title"]; ?></h4>
    <?php
     if(arg(2) != 'presentation'){
     ?>
        <p class="list-group-item-text small"><?php print $item["text"]; ?></p>
    <?php } ?>
  </a>
 
 
   <?php
 }

 // Sicherstellen das die Node nicht online war
 if(arg(1) != 'add' AND $node -> lkstatus != 'deleted'){
 ?>
  <p><a class="list-group-item <?php if(arg(2) == 'delete') print ' active'; ?>" href="<?php print url("node/". $node -> nid . '/delete'); ?>"><span class="glyphicon glyphicon-trash"></span> Kampagne verwerfen</a></p>
 <?php
 }


?>

</div>  

<style>
   h4.list-group-item-heading {
    font-size: 14px;
   
   }
</style>


