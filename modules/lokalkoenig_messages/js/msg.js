
(function ($) {
  "use strict";

  $(document).ready(function(){
    if($('#privatemsg-new').length == 0) {
      return ;
    }

    $('.form-item-recipient').hide();
    $(sendto_kampas).insertBefore('.form-item-recipient');

    var value;

    if(value = $('.form-item-recipient input').val()){
       var items = value.split(',');
       $.each(items, function( index, value ) {
          var val = $.trim(value);
          $('.selectpicker option[uname=\"'+ val +'\"]').attr('selected', true);
       });
    }
    
    $('.selectpicker').prop('multiple', true);
    $('.selectpicker').selectpicker();

    $('#privatemsg-new .selectpicker').selectpicker();

    $('#privatemsg-new').submit(function(){
      var selected = $('.selectpicker').selectpicker('val');
      var myArray = [];

      if(selected) {
        for(var x = 0; x < selected.length; x++) {
           myArray.push($('.selectpicker.form-select').find('option[value="'+ selected[x] +'"]').attr('uname'));
        }
      }
      
      var str  = myArray.join();
      $('.form-item-recipient input').attr('value', str);
    });
});


}( jQuery ));
