
$(document).ready(function($){

  $('.vku-generator').on('change', '.vku-documents-online select', function(){
    var editor = PXEdit();
    var select = this;
    editor.performAjax({'action': 'set-media', 'index': $(this).data('index'), 'id': $(this).data('document-id'), 'media': $(this).val()}, function(data){
      editor.loading(-1);
      online_medium_select_options($(select).closest('.vku-documents-online'));
    });
  });

  $('.vku-generator .vku-documents-online').each(function(){
    online_medium_select_options(this);
  });


 $('.vku-generator').on('click', '.btn-document-edit', function(event){
    event.preventDefault();
    var editor = PXEdit();
    var id = $(this).data('edit-id');
    var reference = this;

    editor.loadDocument({'action': 'load-document', 'id': id}, function(data){
      $(reference).closest('.entry-item').find('.page-title').html('(<span>'+ data.page_title +'</span>)');
    });

    return false;
  });

});


function online_medium_select_options(container){

  var selected_allready = [];

  $(container).find('select').each(function(){
    var selected = $(this).val();
    if(selected !== '0'){
      selected_allready.push(selected);
    }
  });

  $(container).find('select').each(function(){
    var select = this;
    $(select).find('option').removeAttr('disabled').show();
    var selected = $(select).val();
    
    for (var i = 0; i < selected_allready.length; i++) {
      if(selected == selected_allready[i]){
        // pass
      }
      else {
        $(select).find("option[value="+ selected_allready[i] +"]").attr('disabled', 'disabled').hide();
      }
    }
  });
}
