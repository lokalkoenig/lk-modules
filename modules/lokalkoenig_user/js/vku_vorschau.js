

jQuery(document).ready(function(){
   
    jQuery('<hr /><a onclick="start_test_vorschau_pdf_titel();" href="javascript:void(0); return false;"><span class="glyphicon glyphicon-search"></span> Vorschau VKU Einstellungen</a>').insertAfter('#edit-profile-verlag-field-vku-vordergrundfarbe-titel');
    
});


function start_test_vorschau_pdf_titel(){
    
    url = verlag_vku_vorschau_url;
    
    hg_color = (jQuery('#edit-profile-verlag-field-vku-hintergrundfarbe-und-0-jquery-colorpicker').val());
    hg_color_titel = (jQuery('#edit-profile-verlag-field-vku-hintergrundfarbe-titel-und-0-jquery-colorpicker').val());
    vg_color_titel = (jQuery('#edit-profile-verlag-field-vku-vordergrundfarbe-titel-und-0-jquery-colorpicker').val());
   
    //create a new Dom Element before the well
    url += '?hg_color=' + hg_color + '&hg_color_titel=' + hg_color_titel + '&vg_color_titel=' + vg_color_titel;
   
    content = '<iframe src="'+ url +'" style="width: 100%; height: 500px;" class="vorschau-container" src=""></iframe>';
   lk_add_js_modal_optin('Vorschau', content, '', ''); 
 } 
