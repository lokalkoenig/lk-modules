<?php

  function dashboard_add_neuigkeiten(&$list, $account){
      $id = 'neuigekeiten';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-tasks"></span> News';
      $list[$id]["class"] = 'special-tab';
      $list[$id]["content"] = @views_embed_view('neuigkeiten', 'default');
      
      if(lk_is_moderator() OR lk_is_telefonmitarbeiter($account)){
        $list[$id]["content"] = '<div class="well well-white"><a class="btn btn-success" href="'. url("user/" . $account -> uid . "/addnews") .'"><span class="glyphicon glyphicon-plus"></span> Neuigkeit erstellen</a></div>' . $list[$id]["content"]; 
      }  
  }
  
  function dashboard_add_vku(&$list, $account){
      $id = 'vku';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-lock"></span> Verkaufsunterlagen';
      $list[$id]["class"] = 'special-tab';
      $list[$id]["content"] = views_embed_view('verkaufsunterlagen', 'dashboard', $account -> uid);
  }
  
  
  
  function dashboard_add_redaktionskampas(&$list){
      $id = 'redaktion';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-plus"></span> Empfehlungen';
      $list[$id]["class"] = 'special-tab';
      
      if(defined('LK_LOCAL')){
          $list[$id]["content"] = 'Local';
      }
      else {
          $list[$id]["content"] = views_embed_view('suchev2', 'attachment_2');
   
      }
  }

   function dashboard_add_ihresuchen(&$list, $account){
      $id = 'suchen';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-search"></span> Suchen';
      $list[$id]["class"] = 'special-tab';
      $list[$id]["content"] = views_embed_view('search_history', 'attachment_1', $account -> uid);
      
      
  }


  function dashboard_add_kampagnen_mod(&$list){
      $id = 'kampagnen';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-lock"></span> Neue';
      $list[$id]["class"] = 'special-tab';
      $list[$id]["content"] =  views_embed_view('lk_user_kampagnen', 'attachment_2');
  }
  
  function dashboard_add_kampagnen_agentur(&$list, $account){
      $id = 'kampagnen';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-lock"></span> Kampagnen';
      $list[$id]["class"] = 'special-tab';
      $list[$id]["content"] =  views_embed_view('lk_user_kampagnen', 'attachment_1', $account -> uid);
  }
  
  
  function dashboard_add_youtube(&$list, $account){
      $id = 'video';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-film"></span> Videos';
      $list[$id]["class"] = 'special-tab';
      $list[$id]["content"] =  views_embed_view('youtube', 'dashboard');
  }
  
   function dashboard_add_alerts(&$list, $account){
      include("sites/all/modules/lokalkoenig/functions/lokalkoenig_user/pages/alerts.inc");
      
      $id = 'alerts';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-flag"></span> Alerts';
      $list[$id]["class"] = 'special-tab';
      //$list[$id]["content"] = \LK\Alert\_lk_alerts_page($account, false);
  }

  function dashboad_add_adminsupport(&$list){
  
      $id = 'msg';
      $list[$id] = array();
      $list[$id]["title"] = '<span class="glyphicon glyphicon-envelope"></span> Support';
      $list[$id]["class"] = 'special-tab';
   
      $list[$id]["content"] = '<div class="well">Dieses Postfach ist das Support-Postfach eines generischen Benutzers im System. Wenn Sie antworten, dann antworten Sie mit ihrem Persönlichen Profil. Die Konversation ist danach in Ihrem <strong><a href="'. url("messages") .'">persönlichen Postfach</a></strong>.</div>'; 
      $msg = privatemsg_list_page('list', 11);
      $list[$id]["content"] .= render($msg);
  }
  

 function _lokalkoenig_dashboard($account){
 global $user; 
    
 
    if(!$account -> uid){
        drupal_goto("user/" . $user -> uid . "/dashboard");
    }
 
    drupal_set_title("Lokalkönig Startseite");
    pathtitle("dashboard");
  
    if(lk_is_agentur($account)){
      
      $dashboad = array();
      dashboard_add_neuigkeiten($dashboad, $account); 
      dashboard_add_kampagnen_agentur($dashboad, $account);
      
      $links = array();
      $links[] ='<a href="'. url("user/". $account -> uid ."/kampagnen") .'"><span class="glyphicon glyphicon-lock"></span> Ihre Kampagnen</a>';
      $links[] ='<a href="'. url("node/add/kampagne") .'"><span class="glyphicon glyphicon-plus"></span> Neue Kampagne erstellen</a>';
      $links[] ='<a href="'. url("user/". $account -> uid ."/agenturabrechnung") .'"><span class="glyphicon glyphicon-euro"></span> Abrechnung</a>';
      
      return theme('lk_ma_dashboard', array('list' => $dashboad, 'links' => $links, 'account' => $account));
    }
    
    
    if(lk_is_moderator($account)){
      
      $dashboard = array();
      dashboard_add_neuigkeiten($dashboard, $account); 
      dashboard_add_youtube($dashboard, $account);
      dashboard_add_alerts($dashboard, $account);
      dashboad_add_adminsupport($dashboard);
      dashboard_add_kampagnen_mod($dashboard);
      
      
      $links = array();
      $links[] = '<a href="'. url("kampagnen") .'"><span class="glyphicon glyphicon-lock"></span> Kampagnen verwalten</a>';
      $links[] = '<a href="'. url("logbuch") .'"><span class="glyphicon glyphicon-paperclip"></span> Logbuch einsehen</a>';
      $links[] = '<a href="'. url("lkabrechnung") .'"><span class="glyphicon glyphicon-euro"></span> Abrechnungsübersicht</a>';
      $links[] = '<a href="'. url("lkstats") .'"><span class="glyphicon glyphicon-search"></span> Suchen</a>';
      $links[] = '<a href="'. url("user/11/messages") .'"><span class="glyphicon glyphicon-envelope"></span> Supportanfragen</a>';
      
      return theme('lk_ma_dashboard', array('list' => $dashboard, 'links' => $links, 'account' => $account));
    }
    
    
  
      $dashboard = array();
      dashboard_add_neuigkeiten($dashboard, $account); 
      //dashboard_add_vku($dashboard, $account);
      dashboard_add_youtube($dashboard, $account);
      dashboard_add_alerts($dashboard, $account);
      dashboard_add_redaktionskampas($dashboard);   
      dashboard_add_ihresuchen($dashboard, $account);
    
      $links = array();
      $links[] = '<a href="'. url("suche", array("query" => array('sort_by' => 'created'))) .'"><span class="glyphicon glyphicon-lock"></span> Neue Kampagnen</a>';
      $links[] = '<a href="'. url("messages") .'"><span class="glyphicon glyphicon-envelope"></span> Nachrichten</a>';
      $links[] = '<a href="'. url("user/" . $account -> uid . "/vku") .'"><span class="glyphicon glyphicon-cloud-download"></span> Verkaufsunterlagen</a>';
   
      return theme('lk_ma_dashboard', array('list' => $dashboard, 'account' => $account, 'links' => $links));
 }






 




?>