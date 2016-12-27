<?php 

$categories = array();
$categories[] = array(
    "title" => "Mitarbeiter <small class='label label-default'>". count($users) ."</small>",
    'id' => "mitarbeiter",
    "user" => $users,
    "active" => true
);

$categories[] = array(
    "title" => "Verkaufsübergreifende Mitarbeiter <small class='label label-default'>". count($vums) ."</small>",
    'id' => "vum",
    "user" => $vums,
    "active" => false
);

$categories[] = array(
    "title" => "Deaktivierte Accounts <small class='label label-default'>". count($deactive_users) ."</small>",
    'id' => "deactivated",
    "user" => $deactive_users,
    "active" => false
);

$has_edit_account = $current -> hasRight('edit account');


?>


 <div class="well well-white">
            <p><strong>Ihre Mitarbeiter sind in Ausgaben und Teams unterteilt.</strong></p>
            <p>Ausgaben geben den buchbaren Postleitzahlenbereich vor und Teams ordnet ihre Mitarbeiter in Gruppen, wobei Verkaufsleiter diese separat vom Verlagsaccount administrieren können.</p>

           
            <?php if($current -> hasRight('add account')): ?>  
                
              <?php if($teams) : ?>
              <div class="btn-group">
                  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-plus"></span> Neuen Mitarbeiter anlegen <span class="caret"></span>
                  </button>

                  <ul class="dropdown-menu" role="menu">
                     <?php foreach($teams as $team) : ?>
                        <li><a href="<?php print url("user/" . $vid . "/struktur", array("query" => array('action' => "addaccount", "team" => $team -> getId()))); ?>"><?php print $team -> getTitle(); ?></a></li>  
                    <?php endforeach; ?>
              </div>
              <?php endif; ?>

             

              <a href="<?php print url('user/' . $vid . '/struktur', array("query" => array('action' => "addaccount", 'vkl' => 1))); ?>" class="btn btn-success"><span class="glyphicon glyphicon-tower"></span> Neuen Verkaufsleiter erstellen</a>
              <?php endif; ?>
              <a href="<?php print url('user/' . $vid . '/ausgaben'); ?>" class="btn btn-success"><span class="glyphicon glyphicon-globe"></span> Ausgabenübersicht</a>
            
</div>   


<ul class="nav nav-tabs" role="tablist" style="border-bottom: 0;">
 <?php foreach($categories as $cat): ?>
    <li role="presentation" <?php if($cat["active"]): ?>class="active"<?php endif; ?>><a href="#<?php print $cat["id"]; ?>" role="tab" data-toggle="tab"><?php print $cat["title"]; ?></a></li>
 <?php endforeach; ?>   
</ul>

<div class="tab-content">

    <?php foreach($categories as $cat): ?>
          
<div style="border-top-left-radius:0; border-top-right-radius: 0;" class="tab-pane panel panel-default <?php if($cat["active"]): ?>active<?php endif; ?>" role="tabpanel" id="<?php print $cat["id"]; ?>">
    <div class="panel-body">
        
       <?php if($cat["id"] == "vum"): ?>
            <div class="well well-white">
                <p><strong>Verkaufsübergreifende Mitarbeiter</strong></hp>
                <p>Diese Mitarbeiter haben administrative Funktionen und können alle Verlagsbereiche kontrollieren</p>
                <?php if($current -> hasRight('add account')): ?>  
                    <p><a href="<?php print url('user/' . $vid . '/struktur', array('query' => array('action' => 'addvuema'))); ?>" class="btn btn-success"><span class="glyphicon glyphicon-user"></span> Neuen verkaufsübergreifenden Mitarbeiter anlegen</a></p>
                <?php endif; ?>
            </div>    
        <?php endif; ?>
        
<table class="views-table table table-striped table-hover">
   <tr>
    <th class="col-xs-6">Name</th>
    <th></th>
    <th>Team</th>
    
    <?php if($has_edit_account) : ?>
        <th class="col-xs-2">Bearbeiten</th>
    <?php endif; ?>
   </tr>
   
   <?php
     if(count($cat["user"]) == 0):
         ?>
                <tr><td colspan="<?php print 3 + $has_edit_account; ?>" class="text-center"><em>-Keine Einträge-</em></td></tr>
         <?php 
     endif;    
   
   
      foreach($cat["user"] as $account):
          $uid = $account -> getUid();
          
       ?>
       <tr>
        <td>
          <?php print (String)$account; ?>
          <?php if($account -> user_data -> login) :?>
              <br /><em><?php print format_date($account -> user_data -> access); ?></em>
          <?php endif; ?>
        </td>
        <td>
          <?php if($account -> getStatus()) : ?>
            <a href="<?php print url('messages/new/' . $uid); ?>"><span class="glyphicon glyphicon-envelope"></span>
          <?php endif; ?>
        </td>
        <td>
        <?php 
        if($team = $account -> getTeamObject()):
              print '<a href="'. url("team/" . $team -> getId() . '/members') .'">' . $team -> getTitle() . '</a>'; 
              else:
                  print '-';
              
        endif;
        ?>
        </td>
          <?php if($current -> hasRight('edit account')) : ?>
  
        <td>
          <div class="btn-group">
              <button type="button" class="btn btn-primary btn-sm  dropdown-toggle" data-toggle="dropdown">
                <span class="glyphicon glyphicon-pencil"></span> Editieren <span class="caret"></span>
              </button>
  
                  <ul class="dropdown-menu" role="menu">
                     <li><a href="<?php url("user/" . $vid. '/struktur')?>?action=editaccount&uid=<?php print $uid; ?>">Account bearbeiten</a></li>  
                   <?php if($account -> getStatus()) : ?>
                     <li><a href="<?php url("user/" . $vid. '/struktur')?>?action=disable&uid=<?php print $uid; ?>">Deaktivieren</a></li>  
                   <?php else : ?>
                     <li><a href="<?php url("user/" . $vid. '/struktur')?>?action=enable&uid=<?php print $uid; ?>">Aktivieren</a></li>  
                   <?php endif; ?>
                 </ul>
          </div>
        </td>
        <?php endif; ?>
       </tr>
       <?php
      endforeach;
   ?> 
</table>
</div>
</div>
    
    <?php endforeach; ?>
    
    
</div>

<?php return ; ?>
    
    
    
    <div class="tab-pane panel panel-default panel-info" role="tabpanel" id="verlagcontroller">
        <div class="panel-body">
            <div class="well well-white">
                <p><strong>Verkaufsübergreifende Mitarbeiter</strong></hp>
                <p>Diese Mitarbeiter haben administrative Funktionen und können alle Verlagsbereiche kontrollieren</p>
                <?php if($current -> hasRight('add account')): ?>  
                     <a href="<?php print url('user/' . $vid . '/struktur', array('query' => array('action' => 'addvuema'))); ?>" class="btn btn-success"><span class="glyphicon glyphicon-user"></span> Neuen verkaufsübergreifenden Mitarbeiter anlegen</a>
                <?php endif; ?>
            </div>
    
    <table class="views-table table table-striped table-hover">
    <tr>
        <th>Name</th>
        <th></th>
        <?php if($current -> hasRight('edit account')): ?>  
            <th>Bearbeiten</th>
        <?php endif; ?>
    </tr>
   
   <?php while(list($uid, $account) = each($verlagscontroller)) :?>
    
    <?php
       if(!$account -> getStatus()){
           $deactive[] = $account; 
           continue;
       }
    ?>
    
    
    <tr>
        <td>
          <?php print $account; ?>
          <?php if($account -> user_data -> login) :?>
              <br /><em><?php print format_date($account -> user_data-> access); ?></em>
          <?php endif; ?> 
        </td> 
        <td>
          <?php if($account -> getStatus()) : ?>
            <a href="<?php print url('messages/new/' . $uid); ?>"><span class="glyphicon glyphicon-envelope"></span>
          <?php endif; ?>
        </td>
       
       <?php if($current -> hasRight('edit account')): ?>  
         
            <td>
               <div class="btn-group">
                   <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                     <span class="glyphicon glyphicon-pencil"></span> Editieren <span class="caret"></span>
                   </button>

                       <ul class="dropdown-menu" role="menu">
                          <li><a href="<?php url("user/" . $vid. '/struktur')?>?action=editaccount&uid=<?php print $uid; ?>">Account bearbeiten</a></li>  
                        <?php if($account -> getStatus()) : ?>
                          <li><a href="<?php url("user/" . $vid. '/struktur')?>?action=disable&uid=<?php print $uid; ?>">Deaktivieren</a></li>  
                        <?php else : ?>
                          <li><a href="<?php url("user/" . $vid. '/struktur')?>?action=enable&uid=<?php print $uid; ?>">Aktivieren</a></li>  
                        <?php endif; ?>
                      </ul>
               </div>
             </td> 
        
        <?php endif; ?>
    </tr>
    
    
    <?php endwhile; ?>
   
    </table>
      </div>
    </div>
</div>
