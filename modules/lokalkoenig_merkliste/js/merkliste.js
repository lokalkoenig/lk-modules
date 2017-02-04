function track(eventCategory, eventAction, eventLabel){

  if(typeof eventLabel === 'undefined'){
    eventLabel = '';
  }

  if(typeof ga === 'undefined'){
    console.log('Track ['+ eventCategory +'/' + eventAction +' ('+ eventLabel +')]');
  }
  else {
    ga('send', 'event', eventCategory, eventAction, eventLabel);
  }
}

(function ($) {
    "use strict";
    
    window.Merkliste = {
      url: '/merkliste/save',
      nid: 0,
      
      getEditForm: function(selected){
        var select = '<select name="items[]" class="items show-tick" multiple="multiple" title="Ihre bestehenden Merklisten">';
        if(Drupal.settings.merkliste.categories.length === 0){
          select +='<option disabled="disabled">Bisher keine Merkliste vorhanden.</option>'
        }
        
        for(var index in Drupal.settings.merkliste.categories) { 
          var name = Drupal.settings.merkliste.categories[index]; 
          
          if(typeof selected[index] === 'undefined'){
            select +='<option value="'+ index +'">'+ name +'</option>';
          }
          else {
            select +='<option selected="selected" value="'+ index +'">'+ name +'</option>';
          }
        }
        
        select += '</select>';
        var input =  '<div class="row mlinputform clearfix"><div class="col-xs-6" id="selectpickerform">' + select + '</div>'; 
        input += '<div class="col-xs-6"><input class="form-control form-text" type="text" placeholder="Neue Merkliste anlegen" name="newitem" id="newitem" /></div></div>';
        input += '<hr /><button class="btn btn-success btn-merkliste-save" data-loading-text="Speichern...">Speichern</button>';
        
        if(Object.keys(selected).length){
          input += ' <button class="btn btn-danger btn-merkliste-remove">Löschen</button>';
        }
        
        return input;  
      },
      registerListeners: function(){
      var reference = this;
        
        $('a.merklistejs').click(function(){
          reference.nid = $(this).attr('data-nid');
          
          if($(this).hasClass('on')){
            track('merkliste', 'edit', reference.nid);
            reference.showCurrentKeywords();
          }
          else {
            track('merkliste', 'add', reference.nid);
            reference.showAvailableKeyWords();
          }
          
          return false;
        });
        
        $('body').on('click', '.btn-merkliste-edit', function(){
          $('.merkliste-show').fadeOut();
          $('.merkliste-edit').fadeIn();
          return false;  
        });
        
        $('body').on('click', '.btn-merkliste-remove', function(){
          reference.removeKampagne();
          return false;
        });
        
        $('body').on('click', '.btn-merkliste-save', function(){
          reference.saveCurrentKeywords();
          return false;
        });
      },
      removeKampagne: function(){
        var reference = this;
        this.performAjax({'action': 'remove', 'nid': this.nid}, function(response){
          reference.merklisteDeMark();
          track('merkliste', 'remove', reference.nid);
          reference.setMessage(response);
        });
      },
      showCurrentKeywords: function(nid){
        var reference = this;
        this.performAjax({'action': 'load', 'nid': this.nid}, function(data){
          
          var content = '<div class="merkliste-show"><p>';
          for(var index in data.load_terms) { 
            content += '<a href="/merkliste/'+ index +'" class="btn btn-success"><span class="glyphicon glyphicon-tag"></span> ' + data.load_terms[index] + '</a> ';
          }
          content += '</p><hr />';
          content += '</p><a href="#" class="btn btn-primary btn-sm btn-merkliste-edit"><span class="glyphicon glyphicon-pencil"></span> Bearbeiten</a></div>';
          content += '<div class="merkliste-edit" style="display:none;">\n\
        <p>Sie können Begriffe verwenden, wie z.B. Kunde XY oder auch allgemeine Begriffe.</p>' + reference.getEditForm(data.load_terms) + '</div>'
          
          lk_add_js_modal_optin('Merkliste', content, '#', '');
       
          $('#selectpickerform select').selectpicker(); 
        });
      },
      setMessage: function(response){
        // save the Terms in the global scope 
        if(typeof response.terms !== 'undefined'){
          Drupal.settings.merkliste.categories = response.terms;
        } 
        
        $('#mlcount').html(response.total);
        $('#dynamicmodal .modal-body').html('<p>' + response.message + '</p>');
        setTimeout("jQuery('#dynamicmodal').modal('hide');", 1500);
      },
      performAjax(data, cb){
        $('#dynamicmodal btn').attr('disabled', 'disabled');

        jQuery.ajax({
          url : this.url,
          type: "post",
          data : data,
          success: function(data, textStatus, jqXHR){
            $('#dynamicmodal btn').removeAttr('disabled');
            cb(data);
          },
          error: function (){
            alert('Ein Fehler ist aufgetreten'); 
          }
        });  
      },
      merklisteMark: function(){
        $('a.merklistejs[data-nid="'+ this.nid  +'"]').addClass('on'); 
        $('a.merklistejs[data-nid="'+ this.nid  +'"]').parent('li').addClass('hover');
      },
      merklisteDeMark: function(){
        $('a.merklistejs[data-nid="'+ this.nid  +'"]').removeClass('on'); 
        $('a.merklistejs[data-nid="'+ this.nid  +'"]').parent('li').removeClass('hover');
      },
      saveCurrentKeywords: function(){
        
        var selected_terms = [];
        $('#selectpickerform select option:selected').each(function(){
          selected_terms.push($(this).val());
        });
        
        var data = {
          'action': 'save',
          'terms': selected_terms,
          'nid': this.nid,
          'new': $('#newitem').val()
        };
        
        if(data.new === '' && data.terms.length === 0){
          $('#newitem').focus();
          return ;
        }
        
        var reference = this;
        this.performAjax(data, function(response){
            reference.merklisteMark();
            track('merkliste', 'edit', reference.nid);
            reference.setMessage(response);
        });
      },
      showAvailableKeyWords: function (){
        
        lk_add_js_modal_optin('Merkliste', '<div class="ml-edit">\n\
        <p>Sie können Begriffe verwenden, wie z.B. Kunde XY oder auch allgemeine Begriffe.<br />\n\
        </p>' + this.getEditForm({}) + '</div>', '#', '');
        
        $('#selectpickerform select').selectpicker(); 
      }
    };    
}( jQuery ));       

// Autoload
jQuery(document).ready(function(){
  window.Merkliste.registerListeners();
});
