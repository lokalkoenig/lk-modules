
function formatResult2(node) {
  
    return '<div>' + node.text + '</div>';
};

function formatSelection2(node) {
    return '[' + node.id + '] ' + node.text;
};
 


jQuery(document).ready(function(){
  jQuery(".form-item-field-kamp-preisnivau-und select").change(function(){
    if(jQuery('.form-item-field-kamp-preisnivau-und select option:selected').attr('value') == "272"){
      jQuery('.field-name-field-kamp-preis').slideDown('slow');
  
    }
    else {
      jQuery('.field-name-field-kamp-preis').hide();
  
    } 
  });

  jQuery(".form-item-field-kamp-preisnivau-und select").change();
  
  
  
  

});

