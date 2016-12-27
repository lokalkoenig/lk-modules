
jQuery(document).ready(function(){
    
    if(jQuery('#privatemsg-new').length == 0) return ;
    
    jQuery('.form-item-recipient').hide();
    
    
    jQuery(sendto_kampas).insertBefore('.form-item-recipient');
    
    if(value = jQuery('.form-item-recipient input').val()){
       var items = value.split(',');
       
       jQuery.each(items, function( index, value ) {
          val = jQuery.trim(value);
          
          jQuery('.selectpicker option[uname=\"'+ val +'\"]').attr('selected', true); 
       });
       
    
    }
    jQuery('.selectpicker').prop('multiple', true);
    
    //jQuery('#privatemsg-new .selectpicker').selectpicker();  
    
    
    jQuery('#privatemsg-new').submit(function(){
          var myArray = [];
          
          jQuery('.selectpicker option').each(function(){
               if(jQuery(this).attr('selected')){
                 myArray.push( jQuery(this).attr('uname'));
               }
          });
          
          str  = myArray.join();
         
        jQuery('.form-item-recipient input').attr('value', str);  
       
    });
    
    

})