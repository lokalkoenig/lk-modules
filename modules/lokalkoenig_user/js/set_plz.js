
(function ($) {
  "use strict";

  $(document).ready(function(){
    $('#telefonmitarbeiter-set-plz .error').removeClass('error');
    $('#telefonmitarbeiter-set-plz .form-submit').attr('data-loading', 'Bitte warten...');
    $('#telefonmitarbeiter-set-plz .form-submit').click(function(){
      $(this).button('loading');
    });
  });

}( jQuery ));



  
  
 