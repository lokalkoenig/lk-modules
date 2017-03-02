
var last_plz_element = null;


function updateBadgeCount(){
  count = jQuery('.plz.selected').length;  
  jQuery('.badge-count').text(count);
  updateCountyInfo();
}

function setplztoform(){
    //alert(last_plz_element); 
   var plz = new Array();
   jQuery(".plz-selector").find('span.plz.selected').each(function(){
     plz.push(jQuery(this).attr('plz'));
   });

   jQuery("div[plz_id="+ last_plz_element + "] input.form-text").val(plz.join(', ')); 
   jQuery('.plz-selector').modal('hide');
}


function updateCountyInfo(){
  // Verstecke die Regionen
  jQuery('.county').each(function(){
     jQuery(this).removeClass('has');
   
     count = jQuery(this).parent('li').find('span.plz.selected').length;
     
     if(count > 0){
        jQuery(this).addClass('has');
       
     }
  });
  
  jQuery('.state').removeClass('has');
   
  jQuery('.state').each(function(){
     jQuery(this).removeClass('has');
      count = jQuery(this).parent('li').find('span.county.has').length;
      if(count > 0){
        jQuery(this).addClass('has');
      }
  });

}

function plzselect(element, text_class){
  
  last_plz_element = text_class;
  
  if(jQuery('.plz-selector').length == 0){
    modal = '<div class="modal fade plz-selector"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">PLZ auswählen (<span class="badge-count">0</span>)</h4> <input type="text" name="suche" id="plzsuche" placeholder="PLZ-Suche" class="form-control" style="width: 70%; display: inline-block;" /> <button id="suchesubmit" onclick="return false;" class="btn btn-default">Suchen</button></div><div class="modal-body"><p>Postleitzahlen werden geladen&hellip;</p></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button><button type="button" class="btn btn-primary" onclick="setplztoform(); return true;">Übernehmen</button>';
    modal += '</div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
    jQuery(element).after(modal);
 
    var url = jQuery('div[plz_id='+ last_plz_element +']').attr('fetch_url');
    if(!url){
     url = '/lk/plz'; 
    }
 
    jQuery('.plz-selector .modal-body').load(url, function(){
      jQuery('<span class="glyphicon glyphicon-chevron-right" title="Öffnen"></span>&nbsp;&nbsp;').insertBefore('.state, .county');
      var values = jQuery('div[plz_id='+ last_plz_element +'] input.form-text').val();
      var plz = values.split(",");
      for (i = 0; i < plz.length; i++) {
        var einzel = jQuery.trim(plz[i]);
        jQuery('span.plz[plz='+ einzel +']').addClass('selected');
      }
  
      jQuery('.plz-selector .glyphicon').css('cursor', 'pointer');
  
      if(jQuery('.state').length == 1){
        jQuery('#plzselect .glyphicon:first').click();
      }
  
      updateBadgeCount(); 
    });
    
    jQuery('body').on('click', '.plz-selector .glyphicon', function(){
        element  = jQuery(this).next().next();
        jQuery(element).toggle();
        
          if(jQuery(this).hasClass('glyphicon-chevron-right')){
            jQuery(this).removeClass('glyphicon-chevron-right');  
            jQuery(this).addClass('glyphicon-chevron-down');  
          } 
          else {
            jQuery(this).removeClass('glyphicon-chevron-down');  
            jQuery(this).addClass('glyphicon-chevron-right');  
          }    
    });
    
   jQuery('body').on('click','.plz', function(){
      jQuery(this).toggleClass('selected');
      updateBadgeCount();
   }); 
      
   
   jQuery('body').on('click', '.county', function(){
      // Next UL
      var element  = jQuery(this).next();
      var markierte = jQuery(element).find('.plz').length;
      var nichtmarkierte = jQuery(element).find('.plz.selected').length;
      
      if(markierte == nichtmarkierte){
        jQuery(element).find('.plz').each(function(){
           jQuery(this).removeClass('selected');
        });
      }
      else {
        jQuery(element).find('.plz').each(function(){
           jQuery(this).addClass('selected');
        });
      }
      
      updateBadgeCount();
      
   });
   
     
    
 
  }
      
  jQuery('body').on('click', '#suchesubmit', function(){
      value = jQuery('#plzsuche').val();
      
      if(value == ''){
        jQuery('.plz-selector li').removeClass('hidden');
      }
      else {
          // Suche nach genau diesen PLZ
          jQuery('.plz-selector span.plz').each(function(){
              plz = jQuery(this).attr('plz');
              bla = plz.substring(0,value.length);
              
              // Match Hide LI
              if(bla == value){
                 jQuery(this).parent('li').removeClass('hidden');
              }
              else {
                 jQuery(this).parent('li').addClass('hidden');
              }
          });
      
      
          // Verstecke die Regionen
          jQuery('.county').parent('li').each(function(){
              count = jQuery(this).find('li').length;
              count2 = jQuery(this).find('li.hidden').length;
              
              jQuery(this).removeClass('hidden');
              
              if(count == count2){
                jQuery(this).addClass('hidden');
              
              }
          });
          
          
           // Verstecke die Bundesländer
          jQuery('.state').parent('li').each(function(){
              count = jQuery(this).find('li').length;
              count2 = jQuery(this).find('li.hidden').length;
              
              jQuery(this).removeClass('hidden');
              
              if(count == count2){
                jQuery(this).addClass('hidden');
              
              }
          });
      }
  });
  
  jQuery('.plz-selector').modal('show');
}