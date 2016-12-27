
jQuery(document).ready(function(){
  
  jQuery('#telefonmitarbeiter-set-plz .error').removeClass('error');
  
  jQuery('#telefonmitarbeiter-set-plz input[type=checkbox]:checked').each(function(){
      jQuery(this).parent('div').find('.well').addClass('selected');
  });
  
  jQuery('#telefonmitarbeiter-set-plz input[type=checkbox]').click(function(){
      if(jQuery(this).attr('checked')){
         jQuery(this).parent('div').find('.well').addClass('selected'); 
      }
      else {
         jQuery(this).parent('div').find('.well').removeClass('selected'); 
      }
  });
  
  
  jQuery('#telefonmitarbeiter-set-plz .form-submit').attr('data-loading', 'Bitte warten...');
  jQuery('#telefonmitarbeiter-set-plz .form-submit').click(function(){
       jQuery(this).button('loading')
  
  });
  
});