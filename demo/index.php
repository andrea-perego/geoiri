<?php

// Specify the relative path/URL of the required libraries.

  $includepaths = array(
    "./lib/", 
    "./lib/GeoIRI/", 
    "./lib/easyrdf-0.6.2/lib/", 
    "./lib/conNeg_2.0.2/PHP5.x/"
  );

  set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $includepaths));

  require_once("GeoIRI.php");
  $geoiri = new GeoIRI();

  $title = $geoiri->getToolName();
  $defaultGeometry = "MULTIPOLYGON(((40 40, 20 45, 45 30, 40 40)),((20 35, 45 20, 30 5, 10 10, 10 30, 20 35),(30 20, 20 25, 20 15, 30 20)))";
  $defaultCRS = "4326";
  $footer = '<p>' . $geoiri->getToolName() . ' @ GitHub: <a href="https://github.com/andrea-perego/geoiri">https://github.com/andrea-perego/geoiri</a></p>';

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <script type="text/javascript" src="js/script.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  </head>
  <body>
    <header><h1><?php echo $title; ?></h1></header>
    <nav>
    </nav>
    <section>
      <form id="geoiri" action="." method="get" onsubmit="getGeoIRI();return false;">
        <h1>
          <label for="geometry-wkt">Geometry (WKT)</label>
          <span style="float:right"><label for="srid">EPSG : </label>
          <input type="text" title="Coordinate Reference System" id="srid" value="<?php echo $defaultCRS; ?>" maxlength="6" size="6"/>
          <input type="submit" id="getgeoiri" value="Get GeoIRI"/></span>
        </h1>
        <textarea id="geometry-wkt" title="Type or copy &amp; paste a WKT-encoded geometry"><?php echo $defaultGeometry; ?></textarea>
      </form>
    </section>
    <aside>
    </aside>
    <footer><?php echo $footer; ?></footer>
  </body>
</html>
