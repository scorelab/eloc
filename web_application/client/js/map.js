/**
 * Created by chamathsilva on 6/1/16.
 */

var previousMaker = null;
var map = null;

function initialize() {
    var mapProp = {
        center:new google.maps.LatLng(7.8985,80.6771),
        zoom:8,
        mapTypeId:google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("googleMap"),mapProp);

    // google.maps.event.addListener(map, "click", function(event) {
    //     var lat = event.latLng.lat();
    //     var lng = event.latLng.lng();
    //
    //     var uluru = {lat: lat, lng: lng};
    //     // placeMarker(event.latLng);
    //     placeMarker(uluru);
    //
    //     // sample
    //     // var uluru = {lat: 7.955877163205325, lng: 80.62591552734375};
    //
    //     console.log(uluru);
    //
    //
    //     //alert("Lat=" + lat + "; Lng=" + lng);
    // });
}


function placeMarker(location) {
    map.setZoom(8);

    /* Remove Previous Markers */
    if (previousMaker != null){
        previousMaker.setMap(null);
    }

    var marker = new google.maps.Marker({
        position: location,
        map: map,
        animation: google.maps.Animation.DROP
    });

    var infowindow = new google.maps.InfoWindow({
        content: location.name + '<br>Latitude: ' + location.lat +
        '<br>Longitude: ' + location.lng
      });

    infowindow.open(map,marker);



    //call bounce animation after second
    //setTimeout(function(){  marker.setAnimation(google.maps.Animation.BOUNCE); }, 300);
    setTimeout(function(){  centerZoom(location); }, 1000);
    previousMaker = marker;
}

function centerZoom(location){
  map.panTo(location);
  map.setZoom(10);
  // smoothZoom(map, 10, map.getZoom());
}


// the smooth zoom function
function smoothZoom (map, max, cnt) {
    if (cnt >= max) {
        return;
    }
    else {
        z = google.maps.event.addListener(map, 'zoom_changed', function(event){
            google.maps.event.removeListener(z);
            smoothZoom(map, max, cnt + 0.5);
        });
        setTimeout(function(){map.setZoom(cnt)}, 80); // 80ms is what I found to work well on my system -- it might not work well on all systems
    }
}

// function resetMap(){
//   map.setZoom(8);
//   var latLong = {lat: 7.8985, lng: 80.6771};
//   map.panTo(latLong);
// }

// google.maps.event.addDomListener(window, 'load', initialize);
