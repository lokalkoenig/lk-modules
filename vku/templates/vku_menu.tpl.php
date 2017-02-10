<!-- Kampagnen -->
<?php 
global $user; 

if(!$vkus): ?>

<div class="btn-group-vku" data-nid="" data-vku-count="0">  
   <a href="<?php print url("vku/create"); ?>" class="btn btn-primary btn-transparent">
    <small style="    line-height: 13px; display: block; text-align: center;">Aktive Verkaufsunterlage<br />
        <strong class="vku-title" style="font-size: 14px;">Verkaufsunterlage erstellen</strong>
    </small>
  </a>
</div>    


<?php return; endif;


    $special = $vkus[0];
    $copy = new VKUCreator($special -> getId());
    $last_update = $copy ->get('vku_changed');
    $title = strip_tags($copy ->getTitleTrimmed());
    $link = url($copy ->url());
    $nodes = $copy ->getKampagnen();
    $kampagnen = count($nodes);
?>

<div class="btn-group-vku " data-vku-count="<?php print count($vkus); ?>" data-nid="<?php print implode(",", $nodes); ?>">  
   <button type="button" class="btn btn-primary btn-transparent">
    <a class="vku-main-link" href="<?php print $link; ?>" style="">Aktive Verkaufsunterlage<br />
        <strong class="vku-title" style=""><?php print $title; ?></strong>
    </a>
       <span class="pull-right" style="position: absolute; right: 35px; top: 6px;">
           <span class="label small"><span class="count"><?php print $kampagnen; ?></span>/3</span>         
       </span>
  </button>
    
  <button class="dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
  
   <ul class="dropdown-menu dropdown-menu-vku" role="menu" style="width: 250px;">
       <?php 
        $x = 0;
        foreach($vkus as $vku): 
           $copy = new VKUCreator($vku -> getId());
           $last_update = $copy ->get('vku_changed');
           $title = strip_tags($copy ->getTitleTrimmed());
   
           $kampagnen= count($copy ->getKampagnen());
           $vku_id = $copy ->getId();
           $link = $copy -> url();      
          
           
           
         ?>
        <li  class="item vku-menu-<?php print $vku_id; ?> <?php if($x == 0) print 'active'; ?>"><div style="padding: 3px 20px;">
                <p> 
                    <span class="hide-active"><strong class="vku-title"><a title="Zur Verkaufsunterlage" href="<?php print url($link); ?>"><?php  print ($title); ?></a></strong><br /></span>
                    <span class="label label-primary small pull-right hide-active" title="Kampagnen"><span class="count"><?php print $kampagnen; ?></span>/3</span>
                    <small><span class="glyphicon glyphicon-time"></span> <span class="date"><?php print format_date($last_update, 'short'); ?></span></small>
                </p>
                <p>
                   <a href="#" data-url="<?php print url("vku/" . $vku_id . "/setactive"); ?>" class="hide-active btn btn-xs btn-hollow btn-primary btn-sm vku-make-active">Auswählen</a>
                   <!--<a href="<?php print url($link); ?>" class="btn btn-hollow btn-primary btn-sm"> Fertigstellen</a>-->
                </p>    
        </div></li>
       
       <?php 
        $x++;
        
        if($x == 3){
            break;
        }
        
       endforeach; 
       
       ?>
        <!--<li  class="divider"></li>-->
        <li  class="item no-hover" style="border-top: 1px #EEE solid;">
            <div style="padding: 10px 20px;">
              <?php  
                 if(count($vkus) > 3):
              ?> 
                <p class="small text-right">
                   <a class="vku-more" href="<?php print url('user/' . $user -> uid . "/vku"); ?>">Alle anzeigen</a>
                </p>    
          <?php 
            endif; 
          ?>
                <p>
                   <a href="<?php print url('vku/create'); ?>" class="btn btn-block btn-primary btn-xs">Neue Verkaufsunterlage erstellen</a>
                </p>    
        </div></li> 
        
        
  </ul>
</div> 