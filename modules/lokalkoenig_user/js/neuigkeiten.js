jQuery(document).ready(function(){
  
  jQuery('.field-name-field-verlage, .field-name-field-message-status').hide();
   
  jQuery('<div class="images images-append" style="float: right;"></div>').insertBefore('.form-item-field-bild-predefined-und'); 
  jQuery('.form-item-field-bild-predefined-und').css('width', '50%');
  
  jQuery('.form-item-field-bild-predefined-und select')
  
  jQuery('#edit-option').on('change', function(){
        
     val = jQuery(this).children('option:selected').attr('value');
     if(val == 'alle_ma_verlage' || val == 'alle_vkl_verlage' || val == 'alle_va_verlage') {
        jQuery('.field-name-field-verlage').show();
        return ;
     }
      
       jQuery('.field-name-field-verlage').hide();
  });
  
  //jQuery('.field-name-field-suchwort input').addClass('apachesolr-autocomplete unprocessed');
  
  jQuery('#edit-option').change();
  
  jQuery('.form-item-field-bild-predefined-und select').on('change', function(){
      if(jQuery(this).children('option:selected').attr('value') == 'own'){
         jQuery('.images-append').hide();
         jQuery('.field-name-field-bild-own').show();
      }
      else {
        value = jQuery(this).children('option:selected').attr('value');
        imagepath = '/sites/all/themes/bootstrap_lk/content/news/' + value + '.jpg';
        jQuery('.images-append').html('<img src="'+ imagepath +'" width="200" height="200" />');
        jQuery('.images-append').show();
        jQuery('.field-name-field-bild-own').hide();
      }
  });
  
  jQuery('.form-item-field-bild-predefined-und select').change();

});