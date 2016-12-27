

<div class="well well-white">
    <h3>Vorlagen verwalten</h3>
    <p>Mit den benutzerdefinierten Vorlagen generieren Sie Ihre Verkaufsunterlagen schneller.</p>
    
    <p><a href="<?php print url("vku/create/vorlage"); ?>" class="btn btn-success"><span class="glyphicon glyphicon-plus-sign"></span> Neue Vorlage erstellen</a></p>
    
    <hr />
    
    <table class="table">
        <tr>
            <th>
               Vorlagentitel 
            </th> 
            <th>
               Zuletzt geändert 
            </th> 
            
            <th>
               Aktionen
            </th>  
        </tr>
        
        <?php if(count($items) == 0) :?>
                
        <tr><td class="text-center" colspan="3"><p>Keine Vorlagen erstellt.</p></td></tr>
        
        <?php endif; ?>
      
        <?php foreach($items as $item) :?>
            
        <tr>
            <td>
              <?php if($item["default"]) :?>
                <strong><span class="glyphicon glyphicon-star"></span> <?php print $item["title"]; ?></strong><br />
                <small>Standardvorlage</small>
              <?php else: ?>
                <?php print $item["title"]; ?>
              <?php endif; ?>
                </td> 
            <td><?php print format_date($item["changed"]); ?></td> 
            <td>
                <ul class="list-inline"> 
                    <li><a title="Bearbeiten" href="<?php print $item["link_edit"]; ?>"><span class="glyphicon glyphicon-pencil"></span></a></li> 
                    <?php if(!vku_is_update_user()): ?>
                            <li><a title="Standardvorlage setzen" href="<?php print $item["link_star"]; ?>"><span class="glyphicon glyphicon-star-empty"></span></a></li> 
                    <?php endif; ?>    

                    <li><a title="Löschen" class="optindelete" optintitle="Vorlage löschen" optin="Möchten Sie die Vorlage wirklich löschen?" href="<?php print $item["link_delete"]; ?>"><span class="glyphicon glyphicon-trash"></span></a></li>
                </ul>   
            </td> 
        </tr>
        
        
        <?php endforeach; ?>
    </table>
    
    <?php if(!vku_is_update_user()): ?>
    <hr />
    
    <p><em>* Die Standardvorlage wird bei neuen Verkaufsunterlagen verwendet.</em></p>
    <?php endif; ?>
 </div
