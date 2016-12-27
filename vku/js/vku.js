
jQuery(document).ready(function(){
   jQuery(".special-moveable" ).sortable({ 
      handle: ".glyphicon-move",
      update: function( event, ui ) {
          var data = jQuery(".special-moveable").sortable('serialize');
          saveNewNodeOrder(data);
      }
    });
    
    delteVkuPageListener();    
});




  jQuery(document).ready(function(){
     jQuery('#edit-submit--2').attr('data-loading-text', 'Wird gespeichert...');
     
     
     jQuery('.node-kampagne-settings').submit(function(){
         url = jQuery(this).attr('action');
         data = jQuery(this).serialize();
         
         jQuery(this).addClass('sending');
         
         var form_element = this;
         
         jQuery.ajax({
            data: data,
            type: 'POST',
            url: url
            }).done(function( data ) {
                if(data.error == 0){
                  jQuery(form_element).parents('li.page').attr("pages", data.pages);  
                  updatePages();
                  lk_add_js_notfication(data.message);
                  jQuery(form_element).parents('li.page').find('.show-form').click(); 
                  jQuery(form_element).removeClass('sending');
                }
          });
         
       
         return false;
     });
     
     
     
     jQuery('.node-kampagne-settings .cancel').click(function(){
          jQuery(this).parents('li.page').find('.show-form').click();  
          return false;  
     });
     
     
     jQuery('.show-form').click(function(){
        jQuery(this).parents('li.page').find('form').slideToggle();
        jQuery(this).toggleClass('active');
        
        if( jQuery(this).hasClass('active')){
              topPos = jQuery(this).offset();
              jQuery("html, body").animate({ scrollTop: topPos.top }, 500);
        }
         
        return false;
     });
     
     
  });
    

function updatePages(){
  var pages = 0;
  
  jQuery('ul.special-moveable>li').each(function(){
    
    if(!jQuery(this).hasClass('disabled')) {
      page = parseInt(jQuery(this).attr('pages'));
      pages += page;

      if(page == 1){
          jQuery(this).find('span.page').html(pages); 
      }
      else {
        jQuery(this).find('span.page').html((pages - page + 1) + ' - ' + pages);   
      }
    }
  });
  
  jQuery('.todo').html(pages + ' Seiten');  
}


function startgenerate_pdf(element){
  link = jQuery(element).attr('preview');
  lk_add_js_modal_optin('Vorschau-PDF', '<iframe style="width: 100%; height: 500px;" src="' + link + '"></iframe>', '', '');
  jQuery('#dynamicmodal .modal-dialog').animate({width: '1000px'}, 500);
}



function vkuchangestatus(element){
  
  link = jQuery(element).attr('href');

  jQuery.ajax({
    url: link + "?ajax=1"
  })
  .done(function( data ) {
      if(data.error == 0){
        if(data.status == 'disabled'){
            jQuery('#page_' + data.id).addClass('disabled');
        }
        else {
            jQuery('#page_' + data.id).removeClass('disabled');
        }
        
        updatePages();
      }
      else {
        lk_add_js_notfication(data.message, '.page-header');        
      }
  });
  
  return false;
} 


function saveNewNodeOrder(args){
  jQuery.ajax({
        data: args,
        type: 'POST',
        url: vkunodeorderurl
  }).done(function( data ) {
      if(data.error == 0){
        // Post Message to ...
        lk_add_js_notfication(data.message);
        updatePages();
      }
      else {
        lk_add_js_notfication(data.message);
        // Error Occured
      
      }
  });
  

}

function lk_add_js_notfication2(msg){
    jQuery('.msg-full').remove();
    
    template = '<div class="msg-full"><div class="inner"><div class="center">'+ msg +' [<a href="javascript:lk_add_js_notfication2_remove();">schließen]</div></div>';
    jQuery(template).insertAfter('#skip-link');
    jQuery('.msg-full').fadeIn();
    
}


function lk_add_js_notfication2_remove(msg){
    jQuery('.msg-full').fadeOut(); 
}


var delteVkuPageListener = function(){
 
    jQuery('.optindelete-vku').click(function(){
        link = jQuery(this).attr('href');
        title = jQuery(this).attr('optintitle');
        content = jQuery(this).attr('optin');
     
        jQuery('#dynamicmodal').remove();
        jQuery('.modal-backdrop').remove();
        modal = '<div class="modal fade" id="dynamicmodal" style="display: none;"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Modal title</h4></div><div class="modal-body clearfix"><p>One fine body&hellip;</p></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
        jQuery(modal).insertAfter("#wrap");
       
         content += '<p style="padding-top: 10px;"><button href="'+ link +'" class="btn btn-danger ajax-vku-delete" onclick="deleteAjaxVkuItem(this); return false;">' + title + '</button></p>';
        jQuery('#dynamicmodal .modal-title').html(title);
        jQuery('#dynamicmodal .modal-body p').html(content);
        jQuery('#dynamicmodal').modal('show'); 
        
        return false;
    });
};

function deleteAjaxVkuItem(link){
    
   jQuery(link).attr('data-loading-text', 'Wird gelöscht...'); 
   jQuery(link).button('loading')
   url = jQuery(link).attr('href');
   
    
   jQuery.ajax({
    url: url + "?ajax=1"
  })
  .done(function( data ) {
      if(data.error == 0){
          jQuery('#page_' + data.id).remove();
          jQuery('#dynamicmodal .modal-body p').html(data.message);
          
          setTimeout(function(){
                jQuery('#dynamicmodal button.close').click();
                updatePages();
          }, 2000);
          
      }
      else {
        jQuery('#dynamicmodal .modal-body p').html(data.message);  
      }
  });
}


