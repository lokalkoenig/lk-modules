<style>
  h4.list-group-item-heading {
    font-size: 14px;
}

</style>

<div class="list-group">
<?php

global $user;

$items[] = array(
  "title" => "Kampagnenübersicht",
  "text" => NULL,
  "link" => "user/".$user -> uid  ."/kampagnen",
  "icon" => "chevron-left",
  'showlink' => true 
 );
 
 
$items[] = array(
  "title" => "Ihre Kampagne",
  "text" => 'Ihre Kampagne nochmals in der Vollansicht.',
  "link" => "node/".arg(1)  ."/status",
  "icon" => "lock",
  'showlink' => true  
 ); 
 
$items[] = array(
  "title" => "Statistik",
  "text" => 'Aufrufstatitiken zu dieser Kampagne.',
  "link" => "node/".arg(1)  ."/stats",
  "icon" => "align-justify",
  'showlink' => true  
 );  

$items[] = array(
  "title" => "Admin-Team kontaktieren",
  "text" => 'Sie können hier Kontakt zum Admin-Team aufnehmen und eine Editierung beantragen.',
  "link" => "node/".arg(1)  ."/contact",
  "icon" => "user",
  'showlink' => true  
 );   


 if(arg(2) == 'status') $items[1]["active"] = true;
 if(arg(2) == 'stats')  $items[2]["active"] = true;
 if(arg(2) == 'contact')  $items[3]["active"] = true;
 


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
 <?php
 return ;
 ?>