
$(document).ready(function($){

  // Test-Open-Editor
  $('#vku-editor-verlag-documents').on('click', '.btn-document-create', function(){
    
    var editor = PXEdit();
    var preset = $(this).data('preset');
    
    editor.loadDocument({'action': 'load-present', 'preset': preset}, function(){
      editor.performAjax({'action': 'update-documents'}, function(data){
          $('#vku-editor-verlag-documents').html(data.documents); 
          editor.loading(-1);
       });
    });
  });
  
  $('#vku-editor-verlag-documents').on('click', '.btn-document-edit', function(){
    var editor = PXEdit();
    var id = $(this).data('edit-id');
    
    editor.loadDocument({'action': 'load-document', 'id': id}, function(){
      editor.performAjax({'action': 'update-documents'}, function(data){
          $('#vku-editor-verlag-documents').html(data.documents); 
          editor.loading(-1);
       });
    });
    
    return false;
  }); 
});

