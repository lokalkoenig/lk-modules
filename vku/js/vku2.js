/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function(){
    
    new vku_save_basic_info();
    new vku_general_listener();
    new vku_unload_listener();
    new vku_entry_listener();
    new vku_base_sort_listener();
    new vku_get_badge_count_update();
    new vku_accordion_drop_zone_handler();
    new vku_finalize_handler();
    
    // Check the Signature
    vku2.save({type: 'check'}, function(reference, msg){
        setTimeout(function(){
            jQuery('.generate-info-loading').fadeOut(400);
        }, 500);    
    });
    
    
    
});




var sending_data_done = function(){
    
  jQuery('.sending-data').addClass('done');
  
  setTimeout(function(){
      jQuery('.sending-data').removeClass('done sending-data');
  }, 1500);   
};

var vku2 = {
  changed: false,  
  template: 0,
  gotoContent: function(){
      this.goto('vku-content');
  },
  gotoTitle: function(){
      this.goto('vku-title-wrapper');
  },
  goto: function(id){
      jQuery('.item-active .content').slideUp('slow', function(){  });
      jQuery('.item-active').removeClass('item-active');
      
      var reference = this;
      var element = jQuery("#" + id);
     
      jQuery('#'+ id +' .content').fadeIn('slow', function(){ 
          reference.scrollTo(element, 500);
      });
      jQuery('#' + id).addClass('item-active');
  },
  scrollTo: function(element, time){
    jQuery('html, body').animate({
            scrollTop: jQuery(element).offset().top
     }, time);  
  },    
  resetItems: function(html){
    // maybe need todo some other stuff  
    jQuery('#vku2_items_container').html(html);
    console.log("RESET ITEMS");
    new vku_entry_listener();
    new vku_base_sort_listener();
    new vku_get_badge_count_update();
    
  },            
  setTitle: function(title){
     jQuery('span.vku-title').html(title); 
     jQuery('.entry[data-orig=default-title] .action-preview').attr('data-preview-title', title);
  },
  setChanged: function(){
    this.changed = true;
    jQuery('.vku-generator').addClass('data-unsaved');
  },
  setSaved: function(){
    this.changed = false;
    jQuery('.vku-generator').removeClass("data-unsaved");  
  },
  
  saveAllFinish: function(){  
    jQuery('#vku-content').addClass('sending-data');  
    
    var save_data = {type: 'savelast'};
    if(jQuery('.vku-generator').attr("data-status") == 'template'){
        save_data.vku_template_title = jQuery('#edit_vku_title').val();
        
        if(save_data.vku_template_title == ''){
             jQuery('#edit_vku_title').addClass('error');
             jQuery('#vku-content').removeClass('sending-data');  
             lk_js_modal2('Hinweis', 'Bitte vergeben Sie einen Titel der Vorlage');
             return false;
        }
        
        
        jQuery('.vku-generator').removeClass('missing-title');
    }
    
    this.save(save_data, function(obj, data){
        new sending_data_done();
        
        if(data.msg){
           lk_js_modal2('Hinweis', data.msg);
        }
        
        if(data.error == 0){
            
            if(jQuery('.vku-generator').attr("data-status") == 'template'){
                window.location.href = jQuery('.vku-generator').attr('data-template-url'); 
                return ;
            }
            
            obj.goto('vku-final');
        }
    });  
  },
  saveAll: function(after_callback){
    data = new vku_collect_saveables();  
    jQuery('#vku-content').addClass('sending-data');
    
    console.log('Save All');
    var save_data = {type: 'save', data: data};
    
    // add vku_title when template
    if(jQuery('.vku-generator').attr("data-status") == 'template'){
        save_data.vku_template_title = jQuery('#edit_vku_title').val();
    }
    
    var callback_after = after_callback;  
    
    this.save(save_data, function(reference, msg){
       new sending_data_done();
       new vku_base_sort_listener_destroy();
       jQuery('.last-saved-time-data').html(msg.changed);
       
       if(msg.vku_title){
           reference.setTitle(msg.vku_title);
       }
       
       reference.scrollTo(jQuery("#vku-content"), 500);
       
       // we replace it
       if(msg.replace_sid){
           jQuery('.entry.state-new[data-sid='+ msg.replace_sid +']').replaceWith(msg.replace);
       }
       
       jQuery('#vku2_items_container').html(msg.items);
       new vku_base_sort_listener();
       //new vku_entry_listener();
       new vku_get_badge_count_update();
       reference.setSaved();
       
       if(msg.msg){
           lk_js_modal2('Hinweis', msg.msg);
       }
        
       if(callback_after){
          callback_after(reference, msg); 
       }
    });
    
  },
  save: function(obj, callback){
      url = jQuery('.vku-generator').attr('data-save-url');
      var callback_function = callback;
      var reference = this;
      obj.signature = jQuery('.vku-generator').attr("data-signature");
      
      jQuery.ajax({
            data: {save: obj},
            type: 'POST',
            url: url
            }).done(function( data ) {
                if(data.signature_error){
                    // hide other overlays
                    jQuery('.generate-info').hide();
                    jQuery('.sending-data').removeClass('done sending-data');
                    jQuery('.generate-info-error').fadeIn();
                    reference.changed = false;
                }
                else {
                    jQuery('.vku-generator').attr("data-signature", data.signature);
                    jQuery('.last-saved-time-data').html(data.changed);
       
                    callback_function(reference, data);
                }
            }).fail(function() {
                lk_js_modal2('Fehler', 'Bei der Anfrage wurde ein Fehler festgestellt.');
            });
            
    return false;        
  }
};


var vku2_disable_used_documents = function(){
  
  jQuery('#accordion .ui-draggable').removeClass('disabled');
  
  jQuery('.vku-items .entry:not(.state-deleted)[data-orig]').each(function(){
      orig = jQuery(this).attr('data-orig');
      
      if(orig){
        jQuery('#accordion .ui-draggable#' + orig).addClass('disabled');  
      }
  });
    
    
};

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




var vku_finalize_handler = function(){
    jQuery('#finalize-vku').click(function(){
        jQuery('.generate-info-end').show();
        jQuery('.remove-vku-link').hide();
        
        vku2.save({type: 'finalize'}, function(reference, msg){
            jQuery('.generate-info-end').fadeOut();
            
            if(msg.msg){
                lk_js_modal2('Hinweis', msg.msg);
                jQuery('.vku-generator').removeClass("vku-done");
            }
            else {
              jQuery('.vku-generator').addClass('finalized');
              jQuery('.finalize-vku-wrapper').fadeOut();
              jQuery('.download-wrapper').fadeIn();
              jQuery('.btn-pdf-download').attr('href', msg.pdf_download_link);
              jQuery('.btn-pdf-download span').html(msg.pdf_download_size);
              jQuery('.btn-ppt-download').attr('href', msg.ppt_download_link);
              jQuery('.btn-ppt-download span').html(msg.ppt_download_size);
              jQuery('.vku-detail-link').attr('href', msg.vku_link);
            }
        });
    }); 
};   


var vku_accordion_drop_zone_handler = function(){
  
    
    // add 
    jQuery('#accordion').on('shown.bs.collapse', function (e) {
        link = jQuery(e.target).siblings('.vku2-panel-heading').find('a');
        id = jQuery(link).attr('data-id');
        
        if(!id){
            return ;
        }
        
        if(id == 'default'){
            jQuery('#vku2_items_container').addClass('drop-note');
            return ;
        }   
       
        classname = '.dropable-items-' + id;
        jQuery(classname).addClass('drop-note');
        if(!jQuery(classname).hasClass('opened')){
            jQuery(classname).addClass('opened'); 
        }
    });
    
    // We remove the Active Drop-Box. Gets first
    jQuery('#accordion').on('hide.bs.collapse', function (e) {
        link = jQuery(e.target).siblings('.vku2-panel-heading').find('a');
        id = jQuery(link).attr('data-id');
        
        if(!id){
            return ;
        }
        
        
        jQuery('.drop-note').removeClass('drop-note');
    }); 
    
    
};    


var vku_get_badge_count_update = function(){
    
    new vku2_disable_used_documents();
    
    jQuery('.entry.entry-collapsed').each(function(){
       count = jQuery(this).find('.entry:not(.entry-dummy, .state-deactivate, .state-deleted, .ui-droppable, .ui-sortable-placeholder)').length;
       
        
       pages = 0; 
       jQuery(this).find('.entry:not(.entry-dummy, .state-deactivate, .state-deleted, .ui-droppable, .ui-sortable-placeholder)').each(function(){
           pages += parseInt(jQuery(this).attr("data-pages"));
       });
       
       if(pages == 1){
         jQuery(this).find('.child-count').html('(' + pages  + " Seite)");
       }
       else {
         jQuery(this).find('.child-count').html('(' + pages  + " Seiten)");
       }    
       
       jQuery(this).attr("data-pages", pages); 
        
       if(count == 0){
         jQuery(this).find('.entry-dummy').show();  
       }
       else {
         jQuery(this).find('.entry-dummy').hide();  
       }
    });
    
    var overall = 0;
    
    jQuery('.vku-items>.entry:not(.drop-zone, .state-deactivate, .state-deleted)').each(function(){
        overall += parseInt(jQuery(this).attr("data-pages"));
    });
    
    
    if(overall === 1){
        jQuery('.page-count').html(overall + " Seite");
    }    
    else {
        jQuery('.page-count').html(overall + " Seiten");
    }
    
    jQuery('.vku2-document-empty').hide();
    
    // if only 2 documents
    if(overall <= 2){
        jQuery('.vku2-document-empty').show();
    }
    
    var kampagnen =  jQuery('.vku-items>.entry-kampagne:not(.state-deleted)').length; // entry-kampagne
    jQuery('.kampagnen-count').html(kampagnen);
    
    jQuery('#collapseKampagnen').removeClass('disabled'); 
   
    if(kampagnen >= 3){
        jQuery('#collapseKampagnen').addClass('disabled');
    }   
};



var vku_collect_item_state = function(element){
   sid = jQuery(element).attr('data-sid'); 
   new_status = 1;
   
   // deactivated
   if(jQuery(element).hasClass('state-deactivate')){
       new_status = 0;
   }
   
   // new item
   if(jQuery(element).hasClass('state-new')){
      new_status = 3;
   }
   
   // to delete
   if(jQuery(element).hasClass('state-deleted')){
      new_status = 2;
    }
        
   var data = {status: new_status, sid: sid, children: {}};
   
return data;   
};


var vku_collect_saveables = function(delete_items){
    
    var data = {};
    var x = 0; //drop-zone
    jQuery(".vku-items>.entry:not(.ui-droppable,.entry-dummy)").each(function(){
        
        if(jQuery(this).hasClass('state-delete')){
            jQuery(this).addClass('state-deleted');
        }
   
        data[x] = new vku_collect_item_state(this);
        
        // to delete
        if(jQuery(this).hasClass('state-deleted')){
            jQuery(this).remove();
        }
        
        if(jQuery(this).find('.children').length == 1){
            var children = {};
            var i = 0;
            
            jQuery(this).find('.children .entry:not(.ui-droppable,.entry-dummy)').each(function(){
               if(jQuery(this).hasClass('state-delete')){
                jQuery(this).addClass('state-deleted');
               }
               
               var child = new vku_collect_item_state(this);
               children[i] = child;
               
               // to delete
                if(jQuery(this).hasClass('state-deleted')){
                    jQuery(this).remove();
                }
               
               i++;
             });
             
             data[x].children = children;
        }
        
        x++;
    });
    
return data;    
};
    
    
var vku_unload_listener = function(){
  jQuery(window).on('beforeunload', function(){
    if(jQuery('.vku-generator').hasClass('missing-title') || vku2.changed){
        return 'Sind Sie sicher, dass Sie diese Seite verlassen möchten? Nicht gespeicherte Daten gehen dann verloren.';
    }
  });
};    
    
var vku_rebuild_dropzones = function(){
   jQuery('.drop-zone-generic').remove();  
   jQuery('<div class="drop-zone drop-zone-generic entry ui-sortable-placeholder"></div>').insertAfter(".vku-items>.entry:not(.drop-zone-top)");
   jQuery('<div class="drop-zone-print drop-zone-generic entry ui-sortable-placeholder"></div>').insertAfter(".dropable-items-print .entry:not(.drop-zone-top, .entry-dummy)");
  jQuery('<div class="drop-zone-online drop-zone-generic entry ui-sortable-placeholder"></div>').insertAfter(".dropable-items-online .entry:not(.drop-zone-top, .entry-dummy)");

    new vku_base_dropables();

};     
    

var vku_base_dropables_save = function(drop, element){
  title = jQuery(element).attr('data-title');
  sid = jQuery(element).attr('id');
  newItem = '<div class="entry state-new" data-sid="'+ sid +'"><div class="entry-item"><div class="part-title">'+ title +'</div></div>';
  jQuery(newItem).insertAfter(drop); 
         
  // save all
  vku2.saveAll();
}

    
var vku_base_dropables = function(){
    
   if(jQuery(".vku-items .drop-zone").data("ui-draggable")){
      jQuery(".vku-items .drop-zone").droppable( "destroy" );
      jQuery(".vku-items .drop-zone-print").droppable( "destroy" );
      jQuery(".vku-items .drop-zone-online").droppable( "destroy" );
      
      
      jQuery(".dropable-general").draggable( "destroy" );
      jQuery(".dropable-print").draggable( "destroy" );
      jQuery(".dropable-online").draggable( "destroy" );
        
      console.log('rebuild DROP');
   } 
   
   // General 
   jQuery( ".vku-items .drop-zone" ).droppable({
      accept: ".dropable-general",  
      activeClass: "ui-state-hover",
      hoverClass: "highlight",
     
      drop: function( event, ui ) {
          new vku_base_dropables_save(this, ui.draggable);
      },
      deactivate: function( event, ui ) {  
          jQuery('.drop-zone').removeClass('active');
          //jQuery('#vku2_items_container').removeClass('drop-note');
      },
      activate: function( event, ui ) {  
          jQuery('.drop-zone').addClass('active');
          //jQuery('#vku2_items_container').addClass('drop-note');
      }
    });
    
    // Print
    jQuery( ".vku-items .drop-zone-print" ).droppable({
      accept: ".dropable-print",  
      activeClass: "ui-state-hover",
      hoverClass: "highlight",
     
      drop: function( event, ui ) {
          new vku_base_dropables_save(this, ui.draggable);
      },
      deactivate: function( event, ui ) {  
          jQuery('.drop-zone-print').removeClass('active');
          //jQuery('.dropable-items-print').removeClass('drop-note');
         
      },
      activate: function( event, ui ) {  
          jQuery('.drop-zone-print').addClass('active');
          
          if(!jQuery('.dropable-items-print').hasClass('opened')){
             jQuery('.dropable-items-print').addClass('opened'); 
          }
          
           //jQuery('.dropable-items-print').addClass('drop-note');
          
          
      }
    });
    
    
    // Online
    jQuery( ".vku-items .drop-zone-online" ).droppable({
      accept: ".dropable-online",  
      activeClass: "ui-state-hover",
      hoverClass: "highlight",
     
      drop: function( event, ui ) {
          new vku_base_dropables_save(this, ui.draggable);
      },
      deactivate: function( event, ui ) {  
          jQuery('.drop-zone-online').removeClass('active');
      },
      activate: function( event, ui ) {  
          jQuery('.drop-zone-online').addClass('active');
          
          if(!jQuery('.dropable-items-online').hasClass('opened')){
             jQuery('.dropable-items-online').addClass('opened'); 
          }
      }
    });
    
    
    jQuery( ".dropable-general").draggable({
      //cancel: "a.ui-icon", // clicking an icon won't initiate dragging
      revert: "invalid", // when not dropped, the item will revert back to its initial position
      containment: "document",
      helper: "clone",
      //greedy: true,
      cursor: "move",
      accept: '.vku-items .drop-zone',
     }); 
    
    jQuery( ".dropable-print").draggable({
      //cancel: "a.ui-icon", // clicking an icon won't initiate dragging
      revert: "invalid", // when not dropped, the item will revert back to its initial position
      containment: "document",
      helper: "clone",
      //greedy: true,
      cursor: "move",
      accept: '.vku-items .drop-zone-print',
     }); 
     
     
    jQuery( ".dropable-online").draggable({
      //cancel: "a.ui-icon", // clicking an icon won't initiate dragging
      revert: "invalid", // when not dropped, the item will revert back to its initial position
      containment: "document",
      helper: "clone",
      //greedy: true,
      cursor: "move",
      accept: '.vku-items .drop-zone-online',
     }); 
};    
    
  
var vku_base_sort_listener_destroy = function(){
    jQuery(".vku-items").sortable( "destroy" );
    jQuery(".dropable-items-print .children").sortable( "destroy" );
    jQuery(".dropable-items-online .children").sortable( "destroy" );
};


var vku_base_sort_listener = function(){
   new vku_rebuild_dropzones();
    
    jQuery(".vku-items" ).sortable({ 
      handle: ".part-title",
      update: function( event, ui ) { 
        vku2.setChanged();
        new vku_rebuild_dropzones();
      }
    });   
    
    
    jQuery(".dropable-items-print .children").sortable({ 
      handle: ".part-title",
      update: function( event, ui ) { 
        vku2.setChanged();
        new vku_rebuild_dropzones();
      }
    });   
    
    
    jQuery(".dropable-items-online .children").sortable({ 
      handle: ".part-title",
      update: function( event, ui ) { 
        vku2.setChanged();
        new vku_rebuild_dropzones();
      }
    });   
};     
 
 
 
var vku_general_listener = function(){
  
    jQuery('.vku-generator').on('focus', 'input.error', function(){
         var element = this;
         
         setTimeout(function(){
              jQuery(element).removeClass('error');
         },1500);   
     });
     
     jQuery('.templates').on('click', '.template', function(){
         jQuery('.templates .template.selected').removeClass('selected');
         jQuery(this).addClass('selected');
     });
    
     jQuery('#preview-all').click(function(){
        sid = jQuery('.vku-items > .entry:not(.state-disabled, .drop-zone, .dropable-items-print, .dropable-items-online)').attr("data-sid");
        new vku_preview_navigation_show('complete');   
        return false;
     });
  
    jQuery('#preview-container .close').click(function(){
         jQuery('#preview-container').hide();
     });
     
     
     jQuery( ".preview-navigation" ).on( "click", "li:not(.active)", function() {
        jQuery(".preview-navigation li.active").removeClass('active');
         sid = jQuery(this).attr('data-sid');
         url = jQuery('.action-preview[data-sid='+ sid +']').attr('data-preview-url');
         jQuery('#preview-container .well').html('<iframe src="'+ url +'"></iframe>');
         jQuery(this).addClass('active');
     });
     
     // Saves the Search and goto Search
    jQuery('#save_search').click(function(){
        vku2.saveAll(function(){
            window.location.href = jQuery('#save_search').attr('href');
            return true; 
        });
        
     return false;   
    });
};

 
 
 var vku_entry_listener = function(){
     jQuery('.vku-items').on('dblclick', '.entry-collapsed>.entry-item>.part-title', function(){
          jQuery(this).parents('.entry').find('.caret-toggle').click();
     });
     
     jQuery('.vku-items').on('click', '.caret-toggle', function(){
          var element = jQuery(this).parents('.entry');
          
          jQuery(element).find('.children').slideToggle('fast', function(){
              jQuery(element).toggleClass('opened');
          });
     });
     
     jQuery('.vku-items').on('click', '.btn-restore', function(){
        jQuery(this).parents('.entry.state-delete').removeClass('state-delete'); 
        return false;
     });
   
     jQuery('.vku-items').on('click', '.btn-delete', function(){
        jQuery(this).parents('.entry.state-delete').fadeOut('slow', function(){
            jQuery(this).removeClass('.state-delete').addClass('state-deleted');
            new vku_get_badge_count_update();
        }); 
     });
     
     jQuery( ".vku-items" ).on( "click", ".btn-actions a", function() {
         state = jQuery(this).attr('data-action');
         
       if(!state){
         return ;
       }

       var sid = jQuery(this).parent().parent().attr('data-sid');
         
         
         
         if(state == 'preview'){
             var sid = jQuery(this).parent().attr('data-sid');
             new vku_preview_navigation_show(sid);   
             return false;
         }
         
         if(state == 'delete'){
             // remove previous marked
             jQuery('.entry.state-delete').slideDown('slow', function(){
                 jQuery(this).addClass('state-deleted').removeClass('state-delete').hide();
             });
         }
         
         
         if(state == 'activate'){
             jQuery('.entry[data-sid='+ sid + ']').removeClass('state-deactivate');
         }
         else {
             jQuery('.entry[data-sid='+ sid + ']').addClass('state-' + state);
         }
         
         new vku_get_badge_count_update();
         
         jQuery(this).parents('.btn-actions').removeClass('open');
         jQuery('.dropdown-backdrop').remove();
         vku2.setChanged();
         
         return false;
     });
 };
 


/** Preview Container */

var vku_preview_navigation_update = function(){
  
    var list = '';
    x = 1;
    jQuery('.action-preview').each(function(){
        
        parent = jQuery(this).closest('.entry');
        pages = parseInt(jQuery(parent).attr('data-pages')); 
        console.log(pages);
        
        if(!jQuery(parent).hasClass('state-deactivate') && 
                !jQuery(parent).hasClass('state-deleted') && 
                !jQuery(parent).hasClass('state-delete')){
            
               if(pages > 1){
                    calc = x + pages - 1;
                    badge_content = x  + '-' + calc;
                    x+= pages;
                }
                else {
                   badge_content = x;
                   x++;  
                 }
     
            
            
            list += '<li data-sid="'+ jQuery(this).attr('data-sid') +'"><label class="badge">'+ badge_content +'</label><span>' + jQuery(this).attr('data-preview-title') + '</span></li>';
           
        }
    });
    
    jQuery('.preview-navigation').html(list);
};


var vku_preview_navigation_show = function(sid){
  new vku_preview_navigation_update();
  
  jQuery('#preview-container').removeClass('complete');
  
  var url = null;
  
  if(sid == 'complete'){
    url = jQuery('.vku-generator').attr('data-preview-url');
    jQuery('#preview-container').addClass('complete');
  }
  else {
    url = jQuery('.action-preview[data-sid='+ sid +']').attr('data-preview-url');
    jQuery('.preview-navigation li[data-sid='+ sid +']').addClass('active');
  }
  
  jQuery('#preview-container .well').html('<iframe src="'+ url +'"></iframe>');
  jQuery('#preview-container').show();
};


var vku_save_basic_info = function(){
    
    // test if Title
    title = jQuery('#edit_vku_title').val();
    
    if(!title){
        jQuery('.vku-generator').addClass('missing-title');
    }    
    
    
    jQuery('button#basic_information').click(function(){
        title = jQuery('#edit_vku_title').val();
       
        if(title === ''){
           jQuery('#edit_vku_title').addClass('error').focus();
           return ;
        }
        
        company = jQuery('#edit_vku_company').val();
        untertitel = jQuery('#edit_vku_untertitel').val();
        
        vorlagen_id = parseInt(jQuery('.templates .selected').attr('data-id'));
        var vorlage = 0;
        
        if(vku2.template != vorlagen_id){
           vku2.template = vorlage = vorlagen_id;  
           console.log("Apply Vorlage " + vorlage);
        }   
        
        
        jQuery('#vku-title-wrapper').addClass('sending-data');
        vku2.save({type: 'title', template: vorlage, vku_title: title, vku_company: company, vku_untertitel: untertitel}, function(ref, data){ 
            jQuery('#vku-title-wrapper').removeClass('sending-data');
            ref.setTitle(data.vku_title);
            jQuery('.vku-generator').removeClass('missing-title');
            
            if(data.renew_items){
                ref.resetItems(data.renew_items);       
            }    
            
            ref.gotoContent();
        });
    });
}




