

function nodeadd2vku_form(link){
    values = 'ajax=1'; 
    
    if(jQuery('#vku_new_button').hasClass("saving")){
        return ;
    }
    
    jQuery('#vku_new_button').addClass('saving');
    var $btn = jQuery('#vku_new_button').button('loading');
    
    nodeadd2vku(link, true);
    
return false;
}

function nodeadd2vku(link, form){
 
    if(form){
         data_values = { ajax: 1, 
                        vku_new: true,
                        vku_title: jQuery('#vku_new_title').val(), 
                        vku_company: jQuery('#vku_new_company').val(),
                        vku_untertitel: jQuery('#vku_new_untertitel').val()
                };
                
        url = link;        
    }
    else {
       data_values = { ajax: 1 };
       url = jQuery(link).attr('href'); 
        
    }
    
     
     
     jQuery.ajax({
      url : url,
      type: "post",
      data : data_values,
      success: function(data, textStatus, jqXHR){
          jQuery('#vku_cart .count').html(data.total);
          
          
          
          if(data.added == 1) {
            jQuery('a.addvkujs[nid=' +  data.nid +']').parent("li").addClass('hover');
            lk_add_js_modal_optin('Ihre Verkaufsunterlage', data.msg + '<br /><br /><a href="' + data.link_vku + '" class="btn btn-success"><span class="glyphicon glyphicon-shopping-cart"></span> Verkaufsunterlagen jetzt generieren</a> <a href="#" class="btn btn-primary pull-right close" data-dismiss="modal"><span class="glyphicon glyphicon-search"></span> Weitersuchen</a>', '', '');
          }
          else {
             if(typeof data.link_vku == 'string'){
                  data.msg = data.msg + '<br /><a href="' + data.link_vku  + '" class="btn btn-success"><span class="glyphicon glyphicon-shopping-cart"></span> Verkaufsunterlagen jetzt generieren</a>';
             }
        
             lk_add_js_modal_optin('Ihre Verkaufsunterlage', data.msg , '', '');
          }

          jQuery('#vkuload').remove();      
      },
      error: function (jqXHR, textStatus, errorThrown){
         alert('Ein Fehler ist aufgetreten'); 
      }
      });
}


jQuery(document).ready(function(){

 jQuery('.addvkujs').on('click', function(){
      nodeadd2vku(this);
      return false;
  }); 

});