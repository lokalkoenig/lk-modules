
var last_ml_nid = null;

function delete_nid_merkliste(){
   values = 'delete=1&nid=' + last_ml_nid;

   jQuery('.btn-ml-delete').button('loading');

    jQuery.ajax({
      url : ml_save_path,
      type: "post",
      data : values,
      success: function(data, textStatus, jqXHR){
         
          if(data.error == 1){
             alert('Ein Fehler ist aufgetreten'); 
             return ;
          }
         
         jQuery('#dynamicmodal .modal-body p').html('<div class="well">Die Kampagne wurde von Ihrer Merkliste gelöscht.</div>');
         jQuery('#mlcount').html(data.total);
         
         if(data.total == 0) jQuery('#merklistenav').hide();
         else {
            jQuery('#merklistenav').show();
         }
         
         jQuery('a.merklistejs[nid='+ data.nid  +']').removeClass('on'); 
         jQuery('a.merklistejs[nid='+ data.nid  +'] span').html('Zu Merkliste hinzufügen');
         jQuery('a.merklistejs[nid='+ data.nid  +']').attr('items', '');
         jQuery('a.merklistejs[nid='+ data.nid  +']').attr('mlid', '');
         jQuery('a.merklistejs[nid='+ data.nid  +']').parent('li').removeClass('hover');
         
         setTimeout("jQuery('#dynamicmodal').modal('hide');", 1500);
      },
      error: function (jqXHR, textStatus, errorThrown){
         alert('Ein Fehler ist aufgetreten'); 
      }
    });
}

function save_nid_merkliste(){
    values = jQuery('#selectpickerform select').serialize() + '&nid=' + last_ml_nid + '&new=' +  jQuery('#newitem').val();
    jQuery('.btn-ml-save').button('loading');
    // business logic...
    //$btn.button('reset') 
    
    
    jQuery.ajax({
      url : ml_save_path,
      type: "post",
      data : values,
      success: function(data, textStatus, jqXHR){
         
         container = jQuery('#dynamicmodal .modal-body p');
             
          if(data.error == 1){
             jQuery(container).html('<div class="well">' + data.msg + '</div>');
             setTimeout("jQuery('#dynamicmodal').modal('hide');", 1500);
             return ;
          }
          
          if(data.delete == 1){
              jQuery(container).html('<div class="well">' + data.msg + '</div>');
               jQuery('a.merklistejs[nid='+ data.nid  +']').removeClass('on'); 
               jQuery('a.merklistejs[nid='+ data.nid  +'] span').html('Zu Merkliste hinzufügen');
               jQuery('a.merklistejs[nid='+ data.nid  +']').attr('items', '');
               jQuery('a.merklistejs[nid='+ data.nid  +']').attr('mlid', '');
               jQuery('a.merklistejs[nid='+ data.nid  +']').parent('li').removeClass('hover');
               setTimeout("jQuery('#dynamicmodal').modal('hide');", 1500);
               return ;
          }
          
          if(data.nothing){
             jQuery(container).html('<div class="well">' + data.msg + '</div>');
             setTimeout("jQuery('#dynamicmodal').modal('hide');", 1500);
             return ;
          }
          
          
          if(data.new == 0){
            jQuery('a.merklistejs[nid='+ data.nid  +']').attr('items', data.tags);
            jQuery(container).html('<div class="well">'+ data.msg +'</div>');
          } 
          else {
            jQuery('a.merklistejs[nid='+ data.nid  +']').addClass('on'); 
            jQuery('a.merklistejs[nid='+ data.nid  +'] span').html('Auf Ihrer Merkliste');
            jQuery('a.merklistejs[nid='+ data.nid  +']').attr('items', data.tags);
            jQuery('a.merklistejs[nid='+ data.nid  +']').attr('mlid', data.mlid);
            jQuery(container).html('<div class="well">'+ data.msg +'</div>');
            jQuery('a.merklistejs[nid='+ data.nid  +']').parent('li').addClass('hover');
          }
          
          jQuery('#mlcount').html(data.total);
          jQuery('#merklistecontent').html(data.select);
          
          
          if(data.total == 0) jQuery('#merklistenav').hide();
          else {
            jQuery('#merklistenav').show();
          }
          
          setTimeout("jQuery('#dynamicmodal').modal('hide');", 1500);
         
          
      },
      error: function (jqXHR, textStatus, errorThrown){
         alert('Ein Fehler ist aufgetreten'); 
      }
    });

}

function timmaya(el){
     
     jQuery(el).click();  
}


function deleteFromCart(link){
     values = 'ajax=1'; 
     url = jQuery(link).attr('href'); 
  
     jQuery.ajax({
      url : url,
      type: "post",
      data : values,
      success: function(data, textStatus, jqXHR){
          if(data.error == 0){
            jQuery('#vku_cart .count').html(data.total);
            jQuery('a.addvkujs[nid=' +  data.nid +']').parent("li").addClass('hover');
            jQuery('div.node-in-cart.node_' + data.nid).remove();
          }
          
          if(data.total == 0){
               jQuery('#vku_cart').hide();
               jQuery('#vkuload').html('');
                
               lk_add_js_modal_optin('Ihre Verkaufsunterlagen', data.msg + '<br /><br /><a href="#" class="btn btn-primary pull-right close" data-dismiss="modal"><span class="glyphicon glyphicon-search"></span> Weitersuchen</a>', '', '');
       
                
          }
          else {
            lk_add_js_modal_optin('Ihre Verkaufsunterlagen', data.msg + '<br /><br /><a href="/vku" class="btn btn-success">Verkaufsunterlagen jetzt generieren</a> <a href="#" class="btn btn-primary pull-right close" data-dismiss="modal"><span class="glyphicon glyphicon-search"></span> Weitersuchen</a>', '', '');
          }
     },
      error: function (jqXHR, textStatus, errorThrown){
         alert('Ein Fehler ist aufgetreten'); 
      }
      });
      
      return false; 
}  


jQuery(document).ready(function(){
  jQuery('a.merklistejs').click(function(){
     last_ml_nid = jQuery(this).attr('nid'); 
     terms = jQuery(this).attr('items');
     
    select = jQuery('#merklistecontent').html(); 
     
    input =  '<div class="row mlinputform clearfix"><div class="col-xs-6" id="selectpickerform">' + select + '</div>'; // '<div class="input-group"><input class="form-control form-text" type="text" id="mltags" name="mltags" value="'+ jQuery(this).attr('items') +'" size="60" maxlength="1024" autocomplete="OFF" aria-autocomplete="list"><span class="element-invisible" aria-live="assertive" id="edit-field-merkliste-tags-und-autocomplete-aria-live"></span><span class="input-group-addon"><i class="icon glyphicon glyphicon-refresh" aria-hidden="true"></i></span></div>';
     
    input += '<div class="col-xs-6"><input class="form-control form-text" type="text" placeholder="Neue Merkliste anlegen" name="newitem" id="newitem" /></div></div>';
    input += '<div class="input-group"><br /><button onclick="save_nid_merkliste()" class="btn btn-success btn-ml-save" data-loading-text="Speichern...">Speichern</button>';
    if(jQuery(this).attr('nid')){
        if(jQuery(this).attr('mlid')) input += '&nbsp;&nbsp; <button class="btn btn-danger btn-ml-delete" data-loading-text="Löschen..." onclick="delete_nid_merkliste()">Von Merkliste löschen</button>'; 
     }
      
    input += '</div>'; 
       
    lk_add_js_modal_optin('Merkliste', '<div class="ml-edit"><p>Sie können Begriffe verwenden, wie z.B. Kunde XY oder auch allgemeine Begriffe.<br /></p>' + input + '</div>', '#', '');
           
     // Add Autocomplete 
      function split( val ) {
        return val.split( /,\s*/ );
      }
     
      // Set the predefined Items
      arr = split(terms);
     
      jQuery.each( arr, function( i, l ){
        jQuery('#selectpickerform select option[value='+ l +']').attr('selected', 'selected');
      });
     
     jQuery('#selectpickerform select').selectpicker(); 
     
     // if there are Terms, then Link them
     if(terms){
        jQuery('.ml-edit').hide();
        
        content = '<div class="term-show">';
        
        jQuery.each( arr, function( i, l ){
          termname = jQuery('#selectpickerform select option[value='+ l +']').text(); 
          content += '<a href="/merkliste/'+ l +'" class="btn btn-success"><span class="glyphicon glyphicon-tag"></span> ' + termname + '</a> ';
        });
        
        content += '<hr />';
        content += '<a href="#" class="btn btn-primary" onclick="jQuery(\'.term-show\').hide(); jQuery(\'.ml-edit\').show();"><span class="glyphicon glyphicon-pencil"></span> Bearbeiten</a> ';
        
        jQuery(content).insertAfter('.ml-edit');                                                        
     }
  });
});

