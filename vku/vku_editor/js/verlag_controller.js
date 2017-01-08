
jQuery(document).ready(function($){
  
  // Test-Open-Editor
  $('#vku-editor-verlag-documents').on('click', '.btn-create-document', function(){
    
    var editor = PXEdit();
    var preset = $(this).data('preset');
    
    editor.loadDocument({'preset': preset}, function(){
      editor.performAjax({'action': 'update-documents'}, function(data){
          $('#vku-editor-verlag-documents').html(data.documents); 
          editor.loading(-1);
       });
    });
  }); 
});

