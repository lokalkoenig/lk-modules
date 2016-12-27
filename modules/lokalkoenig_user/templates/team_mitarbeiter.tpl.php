<?php
 $verlag = $team -> getVerlag();
 $v = $verlag;
 
 $has_last_access = true;
if($current -> isMitarbeiter() AND !$current -> isTeamleiter()){
     $has_last_access = false;   
}
 
$categories = array();
$categories[] = array(
    "title" => "Mitarbeiter <small class='label label-default'>". count($accounts) ."</small>",
    'id' => "mitarbeiter",
    'url' => '',
    "user" => $accounts,
    'content' => 'table',
    "active" => true
);

if($has_last_access): 
    $categories[] = array(
        "title" => "Deaktivierte Accounts <small class='label label-default'>". count($deactivated) ."</small>",
        'id' => "vum",
        'url' => '',
        'content' => 'table',
        "user" => $deactivated,
        "active" => false
    );
endif;


if($form){
    $categories[] = array(
        "title" => "Verkaufsleiter wechseln",
        'id' => "form",
        'url' => '',
        'content' => render($form),
        "active" => false
    );    
}

if($current -> hasRight("add account")) :
     $url = url("user/" . $v . "/struktur", array("query" => array('action' => 'addaccount', "team" => $team -> id)));
     $categories[] = array(
        "title" => "Mitarbeiter hinzufügen",
        'id' => "add",
        'content' => false,
        'link' => $url, 
        "active" => false
    );       
endif;    




  
?>
<div>&nbsp;</div>
<?php print $header; ?>
<div>&nbsp;</div>

  
 <ul class="nav nav-tabs" role="tablist" style="border-bottom: 0;">
    <?php foreach($categories as $cat): ?>
       <li role="presentation" <?php if($cat["active"]): ?>class="active"<?php endif; ?>>
           <?php if(isset($cat["link"]) && $cat["link"]): ?>
                <a href="<?php print $cat["link"]; ?>" role="tab"><?php print $cat["title"]; ?></a>
           <?php else :?>
                <a href="#<?php print $cat["id"]; ?>" role="tab" data-toggle="tab"><?php print $cat["title"]; ?></a>
           <?php endif; ?>
       </li>
    <?php endforeach; ?>   
</ul>
  
  
<div class="tab-content">

     <?php foreach($categories as $cat): ?>
        <?php 
            if(!$cat["content"]): 
                continue;
            endif;    
         ?>
    
    
        <div style="border-top-left-radius:0; border-top-right-radius: 0;" class="tab-pane panel panel-default <?php if($cat["active"]): ?>active<?php endif; ?>" role="tabpanel" id="<?php print $cat["id"]; ?>">
            <div class="panel-body">
                
                <?php if($cat["content"] == 'table'): ?>
                    <table class="views-table table table-striped table-hover">
                        <tr>
                         <th>Name</th>
                         <th></th>
                         
                         <?php if($has_last_access): ?>
                            <th>Zuletzt angemeldet</th>
                         <?php endif; ?>

                         <?php if($current -> hasRight("edit_account")) : ?>
                           <th>Bearbeiten</th>
                         <?php endif; ?>
                           
                        </tr>
                        <?php  while(list($uid, $account) = each($cat["user"])): ?>
                            <tr>
                                <td><?php print $account; ?></td>
                                <td>
                                  <?php if($account -> getStatus()) : ?>
                                    <a href="<?php print url('messages/new/' . $uid); ?>"><span class="glyphicon glyphicon-envelope"></span></td>
                                  <?php endif; ?>

                                 <?php if($has_last_access) : ?>
                                    <td><?php if(!$account -> getLastAccess()) print ' - '; else print format_date($account -> getLastAccess()); ?></td>
                                <?php endif;?>


                               <?php if($current -> hasRight("edit_accounts")) : ?>
                                <td>

                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                      <span class="glyphicon glyphicon-pencil"></span> Editieren <span class="caret"></span>
                                    </button>

                                        <ul class="dropdown-menu" role="menu">
                                           <li><a href="<?php print url("user/" . $v. '/struktur'); ?>?members=1&action=editaccount&uid=<?php print $uid; ?>">Account bearbeiten</a></li>  
                                         <?php if($account -> getStatus()) : ?>
                                           <li><a href="<?php print url("user/" . $v. '/struktur'); ?>?action=disable&uid=<?php print $uid; ?>">Deaktivieren</a></li>  
                                         <?php else : ?>
                                           <li><a href="<?php print url("user/" . $v. '/struktur'); ?>?action=enable&uid=<?php print $uid; ?>">Aktivieren</a></li>  
                                         <?php endif; ?>
                                       </ul>
                                </div>
                              </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                    
                    <!--NO Entries -->
                    <?php if(!$cat["user"]): ?>
                    <tr>
                        <td colspan="<?php print (2 + $has_last_access + $current -> hasRight("edit_accounts")); ?>" class="text-center">
                            <em>Keine Einträge</em>
                        </td>
                    </tr>
                    
                    <?php endif; ?>
                    
                        
                        
                 </table>    
                
               <?php else: ?>
                    <?php print $cat["content"]; ?>
                <?php endif; ?>
            </div>
        </div>

      
      
      <?php endforeach; ?>
</div>