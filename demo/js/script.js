function getGeoIRI() {
  var srid = document.getElementById("srid").value;
  var geometryWKT = document.getElementById("geometry-wkt").value.replace(/\s/g, '_').replace(/\+/g, '%2B');
  var pathPrefix = document.getElementById("geoiri").action;
  window.location.href = pathPrefix + '/id/geometry/' + srid + '/' + geometryWKT;
}    
