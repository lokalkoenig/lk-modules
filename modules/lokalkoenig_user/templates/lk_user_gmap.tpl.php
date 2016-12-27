<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=visualization"></script>

 <script>
// This example creates a simple polygon representing the Bermuda Triangle.
// Note that the code specifies only three LatLng coordinates for the
// polygon. The API automatically draws a
// stroke connecting the last LatLng back to the first LatLng.
var map, pointarray, heatmap;

function initialize() {
  
   var bound = new google.maps.LatLngBounds();
     <?php
      foreach($points as $p){
         print 'bound.extend( new google.maps.LatLng(' . $p . '));';
         print "\n"; 
      }
    ?>   
    
  var mapOptions = {
    zoom: 8,
    center: bound.getCenter(),
    mapTypeId: google.maps.MapTypeId.STREET
  };

  var bermudaTriangle;

  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);
  map.fitBounds(bound);
    
  // Define the LatLng coordinates for the polygon's path. Note that there's
  // no need to specify the final coordinates to complete the polygon, because
  // The Google Maps JavaScript API will automatically draw the closing side.
  var taxiData = [
    <?php
      foreach($points as $p){
        print 'new google.maps.LatLng('. $p .'),';
      }
    ?>
  ];

  
  var pointArray = new google.maps.MVCArray(taxiData);

  heatmap = new google.maps.visualization.HeatmapLayer({
    data: pointArray
  });
  
  heatmap.setMap(map);
}


jQuery('a').on('shown.bs.tab', function (e) {
  if(jQuery(this).attr('href') == '#plzaccount'){
    initialize();
  }
})


jQuery(document).ready(function(){
 initialize();
});

</script>

<div id="map-canvas" style="width: 716px; height: 450px;"></div>    
    