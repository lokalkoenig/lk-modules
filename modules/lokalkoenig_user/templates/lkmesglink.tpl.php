<?php
  // Account
?>

 <div class="list-group-item list-group-item-success">

 <?php
 
  if(!$account) { print '-</div>';  return ; }
  
  
   $prof = profile2_load_by_user($account, 'main');
  
 ?>


<div class="pull-right">
  <a href="<?php print url("messages/new/" . $account -> uid, array('query' => drupal_get_destination())); ?>"><span class="glyphicon glyphicon-envelope" title="Nachricht senden"></span> Nachricht senden</a>
</div>


<?php 

print l($account -> name, "user/" . $account -> uid); 

if($prof->field_profile_adresse['und'][0]['locality']){
  print ' ('. $prof->field_profile_adresse['und'][0]['locality'] .')';

}



?>
<br /><small>
<?php
 
  print $prof->field_profile_adresse['und'][0]['organisation_name'];
  if(lk_is_verlag($account)){
   print ' (Verlag)';
}  
  //dpm($prof);
?>

</small>
</div>
