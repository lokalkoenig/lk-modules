
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
    
    return false; 
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
  
  $('#vku-editor-verlag-documents').on('click', '.btn-document-toggle', function(){
     var editor = PXEdit();
     var id = $(this).data('edit-id');
     
     editor.performAjax({'action': 'toggle-state', 'id': id}, function(data){
          $('#vku-editor-verlag-documents').html(data.documents); 
          editor.createMessage(data.message, 2500);
     });
     
     return false; 
   });
   
   $('#vku-editor-verlag-documents').on('click', '.btn-document-remove', function(){
     var editor = PXEdit();
     var id = $(this).data('edit-id');
     editor.performAjax({'action': 'load-document', 'id': id}, function(data){
          editor.options.id = data.options.id;
          editor.removeDialoge();
          editor.cb = function(data){
             $('#vku-editor-verlag-documents').html(data.documents); 
             editor.loading(-1);
          };
     });
   }); 
});

