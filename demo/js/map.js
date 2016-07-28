function onEachFeature(feature, layer) {
  if (feature.properties && feature.properties.popupContent) {
    layer.bindPopup(feature.properties.popupContent);
  }
}

$(window).ready(function () {
  var map = L.map('map').setView([0,0],2);
  L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
    maxZoom: 18
  }).addTo(map);
//      map.invalidateSize();
  if (typeof featurecollection !== 'undefined') {
    var geometry = L.geoJson(featurecollection, { onEachFeature : onEachFeature });
    geometry.addTo(map);
    map.fitBounds(geometry.getBounds());//, {padding: [10,10]});
  }
  $(window).resize(function () {
    $('#map').css('width', $(window).width()).css('height', $(window).height() - $('nav').outerHeight() - $('footer').outerHeight());
    map.invalidateSize();
    if (typeof featurecollection !== 'undefined') {
      geometry.addTo(map);
      map.fitBounds(geometry.getBounds());//, {padding: [10,10]});
    }
  }).resize();
});

