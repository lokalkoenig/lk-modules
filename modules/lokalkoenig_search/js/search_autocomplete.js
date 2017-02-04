
(function ($) {
  "use strict";

  $('document').ready(function(){

    $( ".lk-autocomplete-text").autocomplete({
      delay: 300,
      minLength: 3,
      source: function( request, response ) {
        $.ajax( {
          url: Drupal.settings.search.suggest_path,
          dataType: "json",
          data: {
            term: request.term
          },
          success: function( data ) {
            response($.map(data, function (item) {
                return {
                  label: item.keyword + " <sup class='pull-right'>"+ item.count + "</sup>",
                  value: item.keyword
                };
            }));
          }
        });
      },
      select: function( event, ui ) {
          if($(this).hasClass('lk-autocomplete-search')){
            window.location.href = Drupal.settings.search.search_path + '?search_api_views_fulltext=' + ui.item.value + '&utm_source=autocomplete';
          }
        }
      });

    if( $( ".lk-autocomplete-text").length){
      $( ".lk-autocomplete-text").data("ui-autocomplete")._renderItem = function (ul, item) {
       return $("<li></li>")
           .data("item.autocomplete", item)
           .append("<a>" + item.label + "</a>")
           .appendTo(ul);
       };
    }
 
    $( ".lk-autocomplete-search").autocomplete({
      delay: 500,
      minLength: 3,
      source: function( request, response ) {
        track('ac-suche', 'keyword', request.term);
        var keyword = request.term;

        $.ajax({
          url: Drupal.settings.search.suggest_path,
          dataType: "json",
          data: {
            term: request.term
          },
          success: function( data ) {
            if(data.length === 0){
              // @TODO maybe later
              track('ac-suche', 'no-results', keyword);
              return ;
            }
            
            track('ac-suche', 'show-results', keyword);
            $('.search-info-text').hide();
            $('.search-results ul li').remove();

            $.map(data, function (item) {
              $('.search-results ul').append('<li><a href="' + Drupal.settings.search.search_path + '?search_api_views_fulltext=' + item.keyword + '&utm_source=autocomplete">' +  item.keyword + '<sup class="pull-right">' + item.count + '</sup></a></li>');
            });

            $('.search-results').show();
          }
        });
      }
     });

     // Search-Bar
    $("#searchbegin:not(.open)").focus(function(){
      $('#searchbegin').addClass('open');
      track('ac-suche', 'open');

      $("#searchbegin").animate({
            width: "+=150"}, 300, function() {
            $('.showtext').show(500, 'swing');
      });
    });
  });
}( jQuery ));


function closeSearchHelp(){
  $('.showtext').hide(500, 'swing', function(){
    $(this).find('.search-info-text').show();
    $(this).find('.search-results').hide();
  });
  
  $( "#searchbegin" ).removeClass('open').animate({ width: "150px"}, 300);
}
