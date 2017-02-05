
$(document).ready(function($){

 $('.vku-generator').on('click', '.btn-document-edit', function(event){
    event.preventDefault();
    var editor = PXEdit();
    var id = $(this).data('edit-id');
    var reference = this;

    editor.loadDocument({'action': 'load-document', 'id': id}, function(data){
      $(reference).closest('.entry-item').find('.page-title').text(data.page_title);
    });

    return false;
  });

});
