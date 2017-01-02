    <!doctype html>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Lokalkönig Nachricht</title>
                                                                                                                                                                                                                                                                                                                                                                                                            
    <style type="text/css">
        .ReadMsgBody {width: 100%; background-color: #ffffff;}
        .ExternalClass {width: 100%; background-color: #ffffff;}
        body     {width: 100%; background-color: #ffffff; margin:0; padding:0; -webkit-font-smoothing: antialiased;font-family: Arial, Helvetica, sans-serif}
        table {border-collapse: collapse;}
        
        @media only screen and (max-width: 640px)  {
                        body[yahoo] .deviceWidth {width:440px!important; padding:0;}    
                        body[yahoo] .center {text-align: center!important;}  
                }
                
        @media only screen and (max-width: 479px) {
                        body[yahoo] .deviceWidth {width:280px!important; padding:0;}    
                        body[yahoo] .center {text-align: center!important;}  
                }
    </style>
    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" yahoo="fix" style="font-family: Arial, Helvetica, sans-serif">

    <!-- Wrapper -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
        <tr>
            <td width="100%" valign="top" bgcolor="#f7f7f7" style="padding-top:20px">
                
            <!--Start Header-->
            <table width="700" bgcolor="#34495e" border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                <tr>
                    <td style="padding: 6px 0px 0px">
                        <table width="650" border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                            <tr>
                                <td width="100%" >
                                    <!--Start logo-->
                                    <table  border="0" cellpadding="0" cellspacing="0" align="left" class="deviceWidth">
                                        <tr>
                                            <td class="center" style="padding: 20px 0px 20px 0px">
                                                <a href="<?php print url('<front>', array("absolute" => true)); ?>"><img src="<?php print url("sites/all/themes/bootstrap_lk/logo.png", array("absolute" => true));?>"></a>
                                            </td>
                                        </tr>
                                    </table><!--End logo-->
                                    <!--Start nav-->
                                    <table  border="0" cellpadding="0" cellspacing="0" align="right" class="deviceWidth">
                                        <tr>
                                            <td  class="center" style="font-size: 13px; color: #272727; font-weight: light; text-align: center; font-family: Arial, Helvetica, sans-serif; line-height: 25px; vertical-align: middle; padding: 20px 0px 10px 0px;">
                                                <a href="<?php print url("messages/view/" . $message -> thread_id, array("absolute" => true)); ?>" style="text-decoration: none; color: White">Nachricht Online anschauen</a>                           
                                           </td>
                                        </tr>
                                    </table><!--End nav-->
                                </td>
                            </tr>
                        </table>
                   </td>
                </tr>
            </table> 
            <!--End Header-->

            
             

                <!--Start Left Picture-->
                <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                    <tr>
                        <td width="100%" bgcolor="#fff">
                            <!-- Left box  -->
                            <table width="40%" align="left"  border="0" cellpadding="0" cellspacing="0" class="deviceWidth">
                                <tr>
                                    <td valign="top" style="padding: 20px 20px" >
                                       <?php
                                          $aui = $message->author -> uid;
                                       ?> 
                                        <b>Von:</b><br />
                                        <?php
                                          print $message->author -> name;
                                        ?>                                            
                                        <br />
                                       
                                    </td>
                                </tr>
                            </table> <!--End left box-->
                            <!--Right box-->
                            <table width="60%" align="left" border="0" cellpadding="0" cellspacing="0"  class="deviceWidth">
                                <tr>
                                    <td  style="padding-top: 20px; font-size: 16px; color: #303030; font-weight: bold; text-align: left; font-family: Arial, Helvetica, sans-serif; line-height: 25px; vertical-align: middle; ">
                                         <?php print $message -> subject; ?>                         
                                   </td>
                                </tr>
                                <tr>
                                    <td   style="font-size: 12px; color: #303030; text-align: left; font-family: Arial, Helvetica, sans-serif; line-height: 25px; vertical-align: middle; ">
                                        <?php print nl2br($message -> body); ?>   
                                        <br /><br />
                                        
                                       
                                        
                                        
                                   </td>
                                </tr>
                               
                            </table><!--End right box-->
                        </td>
                    </tr>
                </table>
                <!--End Left Picture-->
                
                  <?php 
                  
                  
                  $search_query = NULL; 
                  if(isset($message->field_search_query['und'][0]['value'])){
                     $search_query = unserialize($message->field_search_query['und'][0]['value']);
                  }
                  
                  
                if(isset($message->field_neuigkeit['und'][0]['target_id'])){
                    $entities = entity_load('neuigkeit', array($message->field_neuigkeit['und'][0]['target_id']));
                    $id = $message->field_neuigkeit['und'][0]['target_id'];
                    
                    
                    if($entities[$id]){
                      if(isset($entities[$id]->field_suchwort['und'][0]['value']) AND $entities[$id]->field_suchwort['und'][0]['value']){
                           $sort = 'search_api_relevance';
          
                            switch($entities[$id]->field_sortierung['und'][0]['value']){
                              case 'neueste':
                                $sort = 'created';
                                break;
                                
                              case 'beliebteste':
                                 $sort = 'field_kamp_beliebtheit';   
                                break;  
                            }        
                         
                            $wort = $entities[$id]->field_suchwort['und'][0]['value'];
                         
                            $search_query = array('sort_by'  => $sort, 'search_api_views_fulltext' => $wort);
                      }
                    }
                    
                    
                    ?>
                      <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                      <tr>
                        <td width="100%" bgcolor="#fff">
                          <?php
                            $news = entity_view('neuigkeit', $entities, 'message'); 
                            print render($news); 
                          ?>
                    
                        </td>
                       </tr>   
                   
                    
                    
                    <?php
                }
                
                
                ?>
                
                <?php 
                $searchindex = false;
                $nodes = array();
        
                if($search_query){
                    if($search_query){
                        $searchindex = lk_theme_search_result_view($search_query); 
                        
                        foreach($searchindex["nodes"] as $nid){
                          $nodes[] = $nid;
                        }
                        
                         ?>
                            <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                          <tr>
                        <td width="100%" bgcolor="#fff" style="padding: 20px;">
                             Suchergebnisse: <?php print $searchindex["show"] ?> von <?php print $searchindex["total"] ?> Kampagnen / <a href="<?php print $searchindex["link"]; ?>" style="color: #34495e;"><b>Suchergebnisse öffnen</b></a>
                        
                        </td>
                        </tr>
                        </table>  
                           
                           <?php
                        
                    }
                }
                
                
                if(isset($message->field_msg_kampagnen['und'][0]['nid'])){
                    foreach($message->field_msg_kampagnen['und'] as $n){
                         $nodes[] = $n["nid"];
                    }
                }
                
                
                
                if($nodes) { 
                
                ?><?php
                
                   foreach($nodes as $item){
                      $node = node_load($item);
                      $view = node_view($node, 'emailsend');
                      ?>
                      
                      <table width="700" border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                      <tr>
                        <td width="100%" bgcolor="#fff">
                          <?php print render($view); ?>
                        </td>
                       </tr>   
                   
                   
                      <?php
                   }
                } ?>
              
              
                

                <!-- Footer -->
                <table width="700"  border="0" cellpadding="0" cellspacing="0" align="center" class="deviceWidth">
                    <tr>
                        <td  class="center" style="font-size: 12px; color: #687074; font-weight: bold; text-align: center; font-family: Arial, Helvetica, sans-serif; line-height: 25px; vertical-align: middle; padding: 20px 10px 0px; " >
                            Diese E-Mail kann vertrauliche und/oder rechtlich geschützte Informationen enthalten. Wenn Sie nicht der beabsichtigte Empfänger sind oder diese E-Mail irrtümlich erhalten haben, informieren Sie bitte sofort den Absender telefonisch oder per E-Mail und löschen Sie diese E-Mail aus Ihrem System. Das unerlaubte Kopieren sowie die unbefugte Weitergabe dieser Mail ist nicht gestattet.                          
                        </td>
                    </tr>
                     
                      <tr>
                        <td class="center" style="font-size: 12px; color: #687074; font-weight: bold; text-align: center; font-family: Arial, Helvetica, sans-serif; line-height: 25px; vertical-align: middle; padding: 20px 50px 0px 50px; ">
                            Copyright © Lokalkönig GmbH 2016                            
                        </td>
                    </tr>
                </table>
                <!--End Footer-->

                <div style="height:15px">&nbsp;</div><!-- divider-->


            </td>
        </tr>
    </table> 
    <!-- End Wrapper -->
    </body>
    </html>
    
    <?php if($searchindex) $message->field_msg_kampagnen['und'] = array(); ?>