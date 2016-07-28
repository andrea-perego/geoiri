<?php

// Load the required libraries.

  require('./lib/composer/vendor/autoload.php');

// Specify the relative path/URL of the GeoIRI library.

  $includepaths = array(
    "./lib/GeoIRI/"
  );

  set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $includepaths));

  require_once("GeoIRI.php");
  $geoiri = new GeoIRI();

  $title = $geoiri->getToolName();
  $defaultGeometry = "MULTIPOLYGON(((40 40, 20 45, 45 30, 40 40)),((20 35, 45 20, 30 5, 10 10, 10 30, 20 35),(30 20, 20 25, 20 15, 30 20)))";
  $defaultCRS = "4326";
  $footer = '<a target="_blank" href="https://github.com/andrea-perego/geoiri">' . $geoiri->getToolName() . ' @ GitHub</a>';

?>
<!DOCTYPE html>
<html xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title; ?></title>
<link type="text/css" rel="stylesheet" href="https://bootswatch.com/readable/bootstrap.css" media="screen">
<link type="text/css" rel="stylesheet" href="https://bootswatch.com/assets/css/custom.min.css" media="screen">
<link type="text/css" rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css">
<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="https://bootswatch.com/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://bootswatch.com/assets/js/custom.js"></script>
<script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
<script type="text/javascript">
$("html").css("visibility","hidden");
</script><script type="text/javascript" src="./js/style.js"></script><script type="text/javascript" src="./js/map.js"></script>
<script type="text/javascript" src="./js/script.js"></script>
</head>
<body>
<header><h1><?php echo $title; ?></h1></header>
<nav></nav>
<section id="map-box">
<div id="map"></div>
</section>
<section id="geoiri-section">
<form id="geoiri" action="." method="get" onsubmit="getGeoIRI();return false;">
<h4><label for="geometry-wkt">Geometry (as <a href="https://en.wikipedia.org/wiki/Well-known_text" target="_blank" title="Well-Known Text (Wikipedia)">WKT</a>)</label></h4>
<div id="geometry-wkt-box">
<p id="geometry-wkt-help" class="help">Type or copy &amp; paste a WKT-encoded geometry</p>
<textarea id="geometry-wkt" rows="6" style="resize:vertical;" placeholder="Type or copy &amp; paste a WKT-encoded geometry" title="Type or copy &amp; paste a WKT-encoded geometry"><?php echo $defaultGeometry; ?></textarea>
</div>
<div id="srid-box">
<label for="srid"><a class="info" href="http://www.epsg-registry.org/" target="_blank" title="Click here for the list of EPSG coordinate reference systems."></a> EPSG</label><input type="text" title="Coordinate Reference System" id="srid" value="<?php echo $defaultCRS; ?>" minLength="4" maxLength="8">
</div>
<input type="submit" id="getgeoiri" value="Get GeoIRI">
</form>
</section>
<section id="format-list-section"><h4>Available formats</h4>
<div>
<p>None available yet - no geometry specified.</p>
<div id="format-list-rdf">
</div>
<div id="format-list-others">
</div>
</div>
</section>
<section id="about-section">
<h4>About GeoIRI</h4>
<div>
<p>GeoIRI is an experimental implementation of a URI/IRI space meant to denote arbitrary geometries, in arbitrary coordinate reference systems, by dereferenceable HTTP URI/IRIs, resolving to multiple representations (HTML, RDF) and encodings (WKT, GML, GeoJSON, KML).</p>
<p>For more information: <?php echo $footer; ?></p>
</div>
</section>
<aside></aside>
<footer></footer>
</body>
</html>

