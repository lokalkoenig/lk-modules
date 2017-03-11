
// Test
(function ($) {
    "use strict";

  $(document).ready(function(){
    new vku_active_handler();

    // Workarround MOZ / IE, click Button
    $(".btn-group-vku>button.btn-transparent").click(function(){
      var link = $(this).children('a').attr('href');
      window.location.href = link;
    });

    var value = $('.btn-group-vku').attr('data-nid');
    if(value){
      window.VKU2SetupHandler.markUsedNodes(value);
    }

    $('a.addvku2js').click(function(){
      window.VKU2SetupHandler.nodeAddHandler(this);
    });
  });

  window.VKU2SetupHandler = {

    markUsedNodes : function(nodes){
      var res = nodes.split(",");

      $('.list_vku.hover').tooltip('destroy');
      $('.list_vku.hover').attr('title', '').removeClass('hover');

       for (var i = 0; i < res.length; i++) {
        var element = $('a.addvku2js[data-nid=' + res[i] + ']').parents('.list_vku');
        $(element).addClass('hover');
        $(element).attr('data-toggle', 'tooltip');
        $(element).attr('data-placement', 'top');
        $(element).attr('title', 'Die Kampagne ist bereits in Ihrer aktiven Verkaufsunterlage');
        $(element).tooltip();
      }
    },

    nodeAddHandler: function(element){
      var url = $(element).attr('href');
      var parent = $(element).parents('.list_vku');
      var ref = this;


      if($(parent).hasClass('hover') || $(parent).hasClass('clicked')) {
        return false;
      }

      $(parent).addClass('clicked');

      $.ajax({
        data: {ajax: 1},
        type: 'POST',
        url: url}).
        done(function( data ) {
          $(parent).removeClass('clicked');

          if(data.error == 1){
            lk_js_modal2('Hinweis', data.message);
          }
          else if(data.menu) {
            $('.btn-group-vku').replaceWith(data.menu);
          }

          $('.btn-group-vku>button .count').html(data.total);
          $('li.vku-menu-' + data.vku_id + " span.count").html(data.total);

          if(data.message){
             lk_js_modal2('Hinweis', data.message);
          }

          if(data.kampagnen){
            ref.markUsedNodes(data.kampagnen);
          }
        });
    }
  };


var vku_active_handler = function(){
    jQuery('.vku-make-active').click(function(event){
        jQuery('.dropdown-menu-vku').addClass('sending-data');
        event.preventDefault();
        
        var url = jQuery(this).attr("data-url");
        
        jQuery.ajax({
            data: {ajax: 1},
            type: 'POST',
            url: url
            }).done(function( data ) {
            
                if(data.error == 0){
                  jQuery('.btn-group-vku>button .vku-title').html(data.title);
                  jQuery('.btn-group-vku>button .vku-main-link').attr('href', data.url);
                  jQuery('.btn-group-vku>button .count').html(data.total);
                  jQuery('.btn-group-vku .item.vku-menu-' + data.vku_id).insertBefore('.btn-group-vku .item.active');
                  jQuery('.btn-group-vku .item.active').removeClass('active');
                  jQuery('.btn-group-vku .item.vku-menu-' + data.vku_id).addClass("active");
                  jQuery('.btn-group-vku .item.vku-menu-' + data.vku_id + ' .date').html(data.date);
                  window.VKU2SetupHandler.markUsedNodes(data.kampagnen);
                }
                else {
                  lk_js_modal2('Fehler', data.message);
                }
                
                jQuery('.dropdown-menu-vku').addClass('done');
                 
                setTimeout(function(){
                   jQuery('.dropdown-menu-vku').removeClass('sending-data done');
                }, 1500);
          });
        return false;
    });
};

}( jQuery ));

function lk_js_modal2(title, content){
  jQuery('#dynamicmodal').remove();
  jQuery('.modal-backdrop').remove();
  
  if(!title) title = 'Fehler';
  
  modal = '<div class="modal modal-lk fade" id="dynamicmodal" style="display: none;"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4 class="modal-title">Modal title</h4></div><div class="modal-body clearfix"><p>One fine body&hellip;</p></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
  jQuery(modal).insertAfter("#wrap");
  
  jQuery('#dynamicmodal .modal-title').html(title);
  jQuery('#dynamicmodal .modal-body p').html(content);
  jQuery('#dynamicmodal').modal('show');
}