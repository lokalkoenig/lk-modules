
$(document).ready(function($){

 $('.vku-generator').on('click', '.btn-document-edit', function(event){
    event.preventDefault();
    var editor = PXEdit();
    var id = $(this).data('edit-id');

    editor.loadDocument({'action': 'load-document', 'id': id}, function(){
      editor.performAjax({'action': 'update-documents'}, function(data){
          editor.loading(-1);
       });
    });

    return false;
  });

});
