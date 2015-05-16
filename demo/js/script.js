function getGeoIRI() {
  var srid = document.getElementById("srid").value;
//  var geometryWKT = document.getElementById("geometry-wkt").value.replace(/\s/g, '_').replace(/\+/g, '%2B');
  var geometryWKT = encodeWKT(normaliseWKT(document.getElementById("geometry-wkt").value));
  var pathPrefix = document.getElementById("geoiri").action;
  window.location.href = pathPrefix + '/id/geometry/' + srid + '/' + geometryWKT;
}
// Removes unnecessary white spaces from the WKT string.
function normaliseWKT(wkt) {
  return wkt.trim().replace(/[\s]*([\(\),])[\s]*/g, '$1').replace(/[\s]+/g, ' ').toLowerCase();
}
// Encodes the WKT string to be used in the URI path.
function encodeWKT(wkt) {
  return wkt.replace(/\s/g, '_').replace(/\+/g, '%2B');
}
