

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<?php $x = 0; foreach($items as $item) :?>

	<?php
            if(isset($item["link"])){
                
                $title = $item["title"];
		$desc = $item["desc"];
                $icon = $item["icon"];
                $x++;
                
                ?>
             <div class="well well-white">    
                <div class="panel2">
                    <div class="panel2-heading" role="tab" id="headingOne">
                      <h4 class="panel2-title" style="margin: 0;">
                        <a role="button" href="<?php print url($item["link"]); ?>">
                          <span class="glyphicon glyphicon-<?php print $icon; ?>"></span> <?php print $title; ?>
                          <br /><small><?php print $desc; ?></small>
                        </a>
                      </h4>
                    </div>
                </div>
             </div>  
               <?php
               continue;
            }
            elseif(count($item["items"]) == 0){
               continue;
            }
        
		$id = $x;
		$title = $item["title"];
		$desc = $item["desc"];
		$icon = $item["icon"];
		$x++;
	?>

  <div class="panel panel">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php print $id; ?>" aria-expanded="false" aria-controls="collapse<?php print $id; ?>">
          <span class="glyphicon glyphicon-<?php print $icon; ?>"></span> <?php print $title; ?>
          <br /><small><?php print $desc; ?></small>
        </a>
      </h4>
    </div>
    <div id="collapse<?php print $id; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php print $id; ?>">
      <div class="panel-body">
      		<table class="table">

      		<?php
       			
       			while(list($key, $val) = each($item["items"])) {
       			?>
       				<tr><td><?php print $val["title"]; ?>
       					<?php if(isset($val["preview"])) :?>
       						<br /><small><a href="#" onclick="startgenerate_pdf(this); return false;" preview="<?php print url($val["preview"]); ?>"><span class="glyphicon glyphicon-zoom-in"></span> Vorschau</a></small>
                                 	<?php endif; ?>		
       				</td><td class="text-right">
                                  
                                        <a data-toggle="tooltip" title="<?php if(isset($val["link_title"])): print $val["link_title"]; else : ?>Hinzufügen<?php endif; ?>" href="<?php print url($val["link"]); ?>" class="btn btn-success <?php if(isset($val["optin"])) print ' optindelete'; ?>" <?php  if(isset($val["optin"])): ?> optintitle="<?php print $val["optin"]; ?>" optin_label="<?php print $val["optin_label"]; ?>" optin="<?php print $val["optin_text"]; ?>" <?php endif;  ?>>
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </a>
                                    
                                </td></tr>	
       			<?php
       			}

       		?>
       		</table>


      </div>
    </div>
  </div>
 
<?php endforeach; ?>
</div>