
'use strict';

jQuery(document).ready(function($){
  $('#edit-vku-vordergrundfarbe-titel').prop({type:"color"}).css('width', 100);
  $('#edit-vku-hintergrundfarbe-titel').prop({type: "color"}).css('width', 100);
  $('#edit-vku-hintergrundfarbe').prop({type: "color"}).css('width', 100);
  $('<p><a href="#" class="vku-preview">Vorschau Farbgebung</a></p><hr />').insertAfter('.form-item-vku-vordergrundfarbe-titel');
  
  $('.vku-preview').click(function(){

    var params = $('form#lokalkoenig-user-verlag-admin-settings-form').serialize();
    console.log(params);

    lk_add_js_modal_optin('PDF-Vorschau', '<iframe style="width: 100%; height: 500px;" src="'+ Drupal.settings.lokalkoenig_admin.preview_url +'?'+ params +'">', '', '');
  
    
    return false;
  });
});
