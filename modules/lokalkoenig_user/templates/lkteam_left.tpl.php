<?php 
    $current = \LK\current(); 
    $team_obj = \LK\get_team($team);
    
    $verlag = $team_obj ->getVerlag();
    $verlag_obj = \LK\get_user($verlag);
?>

<div class="well well-white text-center">
    <?php print $verlag_obj ->getPicture(); ?>
    <h2 class="block-title">Angemeldet als:<br /><?php print (string)$current; ?></h2> 
</div>

<div class="panel panel-success">
  <?php
    for($x = 1; $x < count($links); $x++){
         print lklink($links[$x]["title"], $links[$x]["link"], $links[$x]["icon"]);
    } 
  ?>
</div>


<?php
global $user; 
if($current -> isMitarbeiter()){
     print lklink('Zurück zur Ihrem Profil', 'user/' . $current -> getUid() . "/dashboard", 'user');
}
else {
    if($current -> hasRight('edit_team')){
       print lklink('Team editieren', 'team/' . $team . "/edit", 'pencil');
    }
      // Verlag
    print lklink('Zurück zur Ihrem Profil', 'user/' . $current -> getUid() . "/dashboard", 'user');
}

?>