
                   <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                       <div class="panel">
                            <div class="vku2-panel-heading" role="tab" id="headingPrint">
                             <h4>
                               <a data-id="print" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePrint" aria-expanded="false" aria-controls="collapsePrint">
                                 <span class="caret caret-main pull-right"></span>Argumentation Print
                               </a>
                             </h4>
                           </div>


                            <div id="collapsePrint" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingPrint">
                                <div class="well well-white"> 
                                    <?php while(list($key, $val) = each($print)): ?>
                                  <div class="item dropable dropable-print" data-title="<?php print strip_tags($val); ?>" id="<?php print $key; ?>">
                                             <?php print $val; ?>
                                         </div>
                                    <?php endwhile; ?>
                                </div>    
                            </div>
                                
                       
                       </div>
                       
                       
                       <div class="panel">
                        <div class="vku2-panel-heading" role="tab" id="headingOnline">
                        <h4>
                          <a  data-id="online" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOnline" aria-expanded="false" aria-controls="collapseOnline">
                             <span class="caret caret-main pull-right"></span>Argumentation Online
                          </a>
                        </h4>
                      </div>
                       
                       
                      <div id="collapseOnline" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOnline">
                          <div class="well well-white"> 
                          <?php while(list($key, $val) = each($online)): ?>
                                <div class="item dropable dropable-online" data-title="<?php print strip_tags($val); ?>" id="<?php print $key; ?>">
                                    <?php print $val; ?>
                                </div>
                           <?php endwhile; ?>
                          </div>   
                       </div> 
                       </div>
                       
                       <?php if($kampagnen): ?>
                       
                       
                       <div class="panel">
                       <div class="vku2-panel-heading" role="tab" id="headingKampagnen">
                        <h4>
                          <a data-id="default" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseKampagnen" aria-expanded="false" aria-controls="collapseKampagnen">
                            <span class="caret caret-main pull-right"></span>Kampagnen
                          </a>
                        </h4>
                      </div>
                       
                       
                      <div id="collapseKampagnen" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingKampagnen">
                          <div class="well well-white">  
                          
                           <div class="disabled-text help-block">Sie haben die maximale Anzahl von Kampagnen in Ihrer Verkaufsunterlage.</div>
                   
                           <div class="wrapper-state">
                          
                      <ul class="nav nav-tabs" role="tablist">
                          <li role="presentation" class="active"><a href="#vku2-merkliste" role="tab" data-toggle="tab">Merklisten</a></li>
                          <li role="presentation" class=""><a href="#vku2-last-viewed" role="tab" data-toggle="tab">Zuletzt angesehen</a></li> 
                          <li role="presentation" class=""><a href="#vku2-search" role="tab" data-toggle="tab">Suchen</a></li> 
                     </ul>
                     
                              
                   
                   <div class="tab-content">
                   <div class="tab-pane panel panel-default active" role="tabpanel" id="vku2-merkliste">
                      
                       
                       <?php if(!$kampagnen["merkliste"]): ?>
                            <p class="help-block">Sie haben bisher keine Merklisten erstellt.</p>
                       <?php else: ?>
                       
                       <div class="panel-group" id="accordion-merkliste" role="tablist" aria-multiselectable="true">
                        <?php while(list($id, $array) = each($kampagnen["merkliste"])) :?>
                            <div class="panel">
                              <div class="panel-heading" role="tab" id="heading<?php print $id; ?>">
                                <h4 class="panel-title">
                                  <a role="button" class="collapsed" data-toggle="collapse" data-parent="#accordion-merkliste" href="#collapse<?php print $id; ?>" aria-expanded="false" aria-controls="collapse<?php print $id; ?>">
                                      <span class="caret pull-right"></span>
                                      <span class="badge badge-primary pull-right"><?php print count($array["nodes"]); ?></span>
                                      
                                      <?php print $array["title"]; ?>
                                  </a>
                                </h4>
                              </div>
                              <div id="collapse<?php print $id; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php print $id; ?>">
                                <div class="panel-body">
                                 <?php 
                                    foreach($array["nodes"] as $nid){
                                        print theme('vku2_kampagne', array("nid" => $nid));
                                    }
                                  ?>
                                </div>
                              </div>
                            </div>
                        <?php endwhile; ?>   
                    </div>
                    <?php endif; ?>   
                   </div>    
                   
                    <div class="tab-pane panel panel-default" role="tabpanel" id="vku2-last-viewed">
                  
                   <?php 
                        foreach($kampagnen["last"] as $nid){
                            print theme('vku2_kampagne', array("nid" => $nid));
                        }
                        
                        if(count($kampagnen["last"]) == 0):
                            ?>
                              <p class="help-block">Sie haben bisher keine Kampangen angesehen.</p>
                            <?php
                        endif;
                   ?>
                    </div>     
                   <div class="tab-pane panel panel-default" role="tabpanel" id="vku2-search">
                       <a id="save_search" href="<?php print url('suche'); ?>" class="btn btn-success btn-block">Speichern und zur Suche springen</a>
                   </div> 
                   </div>  
                          
                           </div>
                       </div></div>  
                       </div>    
                  
                  <?php endif; ?> 
          
                   
                   <div class="panel">
                    <div class="vku2-panel-heading" role="tab" id="headingSonstiges">
                        <h4>
                          <a data-id="default" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseSonstiges" aria-expanded="false" aria-controls="collapseSonstiges">
                            <span class="caret caret-main pull-right"></span>Sonstiges
                          </a>
                        </h4>
                      </div>
                      
                      
                       <div id="collapseSonstiges" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingSonstiges">
                           <div class="well well-white"> 
                            <?php while(list($key, $val) = each($sonstiges)): ?>
                                 <div class="item dropable dropable-general" data-title="<?php print strip_tags($val); ?>" id="<?php print $key; ?>">
                                     <?php print $val; ?>
                                 </div>
                            <?php endwhile; ?>
                           </div>     
                       </div>
                   </div>
            </div>        
                  
                  