
jQuery(document).ready(function($){
  
  // Test-Open-Editor
  $('#vku-editor-verlag-documents').on('click', 'button', function(){
    var editor = PXEdit();
    editor.loadDocument({'preset': 'Preisliste'}, function(){
       console.log('Event came back!');
    });
  });
  
});

