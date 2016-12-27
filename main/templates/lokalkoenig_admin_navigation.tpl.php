<?php 
    $tree = menu_tree_all_data('navigation');
?>


<div>
    <ul class="list-admin">
    <?php 
    
        while(list($key, $val) = each($tree)):
            if($val["link"]["hidden"] == 1){
                continue;
            }
            
            print '<li>';
            print l($val["link"]["link_title"], $val["link"]["link_path"]);
            
             if($val["below"]){
                  print '<ul>';
                   while(list($key2, $val2) = each($val["below"])):
                       print l("- " . $val2["link"]["link_title"], $val2["link"]["link_path"]);
                       
                   endwhile;    
                  print '</ul>';
             }
            
            
            print '</li>';
        endwhile;
    ?>  
    </ul>
</div>  