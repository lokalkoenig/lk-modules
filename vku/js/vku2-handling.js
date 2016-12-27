
// Test

jQuery(document).ready(function(){
    new vku_active_handler();
    new vku_add_node_handler();
    
    value = jQuery('.btn-group-vku').attr('data-nid');
    
    
    // Workarround MOZ / IE, click Button  
    jQuery(".btn-group-vku>button.btn-transparent").click(function(){
        link = jQuery(this).children('a').attr('href');
        window.location.href = link;
    });
    
    if(value){
       new vku_add_node_set_used_nodes(value); 
    }
});


var vku_add_node_set_used_nodes = function(nodes){
    var res = nodes.split(",");
    
    //take out old ones
    jQuery('.list_vku.hover').tooltip('destroy');
    jQuery('.list_vku.hover').removeClass('hover');
    jQuery('.list_vku.hover').attr('title', '');
   
    
    for (i = 0; i < res.length; i++) {
        element = jQuery('a.addvku2js[data-nid=' + res[i] + ']').parents('.list_vku');
        jQuery(element).addClass('hover');
        jQuery(element).attr('data-toggle', 'tooltip');
        jQuery(element).attr('data-placement', 'top');
        jQuery(element).attr('title', 'Die Kampagne ist bereits in Ihrer aktiven Verkaufsunterlage');
        jQuery(element).tooltip();
    }
};


var vku_add_node_handler = function(){
    
    jQuery('a.addvku2js').click(function(){
       url = jQuery(this).attr('href');
       var parent = jQuery(this).parents('.list_vku'); 
       
       
        
       // parent check
       if(jQuery(parent).hasClass('hover')){
         return false;  
       };
       
        if(jQuery(parent).hasClass('clicked')){
         return false;  
       };
       
       var vku_count = parseInt(jQuery('.btn-group-vku').attr('data-vku-count'));
       
       if(vku_count === 0){
           return true;
       }
       
       jQuery(parent).addClass('clicked'); 
       
       jQuery.ajax({
            data: {ajax: 1},
            type: 'POST',
            url: url
            }).done(function( data ) {
               jQuery(parent).removeClass('clicked');
                
               if(data.error == 1){
                   lk_js_modal2('Hinweis', data.message);
               } 
               else {
                   if(data.menu){
                      jQuery('.btn-group-vku').replaceWith(data.menu); 
                   }
                    
                   jQuery('.btn-group-vku>button .count').html(data.total);
                   jQuery('li.vku-menu-' + data.vku_id + " span.count").html(data.total);
                   
                   if(data.message){
                      lk_js_modal2('Hinweis', data.message); 
                   }
                   
                   if(data.kampagnen){
                       vku_add_node_set_used_nodes(data.kampagnen);
                   }
                   
                }
            });
        
       return false; 
    });
    
}


var vku_active_handler = function(){
    jQuery('.vku-make-active').click(function(event){
        jQuery('.dropdown-menu-vku').addClass('sending-data');
        event.preventDefault();
        
        url = jQuery(this).attr("data-url");
        
        jQuery.ajax({
            data: {ajax: 1},
            type: 'POST',
            url: url
            }).done(function( data ) {
            
                if(data.error == 0){
                    jQuery('.btn-group-vku>button .vku-title').html(data.title);
                    jQuery('.btn-group-vku>button .vku-main-link').attr('href', data.url);
                    jQuery('.btn-group-vku>button .count').html(data.total);
                    jQuery('.btn-group-vku .item.vku-menu-' + data.vku_id).insertBefore('.btn-group-vku .item.active');
                    
                    jQuery('.btn-group-vku .item.active').removeClass('active');
                    jQuery('.btn-group-vku .item.vku-menu-' + data.vku_id).addClass("active");
                    jQuery('.btn-group-vku .item.vku-menu-' + data.vku_id + ' .date').html(data.date);
                    
                    vku_add_node_set_used_nodes(data.kampagnen);
                }
                else {
                       lk_js_modal2('Fehler', data.message);
                }
                
                 jQuery('.dropdown-menu-vku').addClass('done');
                 
                 setTimeout(function(){
                    jQuery('.dropdown-menu-vku').removeClass('sending-data done');
                 }, 1500);
          });
        return false;
    });
};



function lk_js_modal2(title, content){
  jQuery('#dynamicmodal').remove();
  jQuery('.modal-backdrop').remove();
  
  if(!title) title = 'Fehler';
  
  modal = '<div class="modal modal-lk fade" id="dynamicmodal" style="display: none;"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Modal title</h4></div><div class="modal-body clearfix"><p>One fine body&hellip;</p></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
  jQuery(modal).insertAfter("#wrap");
  
  jQuery('#dynamicmodal .modal-title').html(title);
  jQuery('#dynamicmodal .modal-body p').html(content);
  jQuery('#dynamicmodal').modal('show');
}