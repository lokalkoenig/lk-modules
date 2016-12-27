
jQuery(document).ready(function(){

  jQuery( "#edit-timestamp-min" ).datepicker({
      dateFormat : 'yy-mm-dd',
      changeMonth: true,
      numberOfMonths: 1,  
      onClose: function( selectedDate ) {
        jQuery( "#edit-timestamp-max" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    jQuery( "#edit-timestamp-max" ).datepicker({
      dateFormat : 'yy-mm-dd',
      changeMonth: true,
      numberOfMonths: 1,      
      onClose: function( selectedDate ) {
        jQuery( "#edit-timestamp-min" ).datepicker( "option", "maxDate", selectedDate );
      }
    });


});