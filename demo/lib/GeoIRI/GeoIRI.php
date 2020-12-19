<?php

/*************************************************************************

  Copyright (c) 2015-2020, Andrea Perego <http://about.me/andrea.perego>
  Licence: http://opensource.org/licenses/MIT

**************************************************************************/

error_reporting(0);

// If using conNeg = 3.0.0:
use ptlis\ConNeg\Negotiate;
// If using conNeg < 3.0.0:
//require_once('conNeg.inc.php');

class GeoIRI {

// The path to the directory where GeoIRI.php and related resources are available
// The actual value is set in function save().

  private $classdir = null;

  private $toolname = "GeoIRI";
  function getToolName() {
    return $this->toolname;
  }

  private $toolacronym = null;
  function getToolAcronym() {
    return $this->toolacronym;
  }

  private $toolversion = "0.0.1";
  function getToolVersion() {
    return $this->toolversion;
  }

  private $toolstatus = "&alpha;";
  function getToolStatus() {
    return $this->toolstatus;
  }

  function getToolInfo() {
    $info = $this->toolname . " &ndash; v " . $this->toolversion . " " . $this->toolstatus;
    if ($this->toolacronym != '') {
      $info = $this->toolacronym . ": " . $this->title;
    }
    return $info;
  }

  private $dsn = array(
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'geoiri',
    'user' => 'geoiri',
    'password' => ''
  );
  
  private $dboptions = array(
  );
  
  function setDSN($user,$password,$dbname,$host,$port = 5432) {
    $this->dsn['host'] = $host;
    $this->dsn['port'] = $port;
    $this->dsn['dbname'] = $dbname;
    $this->dsn['user'] = $user;
    $this->dsn['password'] = $password;
  }

  private function getConnectionString() {
    return "host='" . $this->dsn['host'] . "' port='" . $this->dsn['port'] . "' dbname='" . $this->dsn['dbname'] . "' user='" . $this->dsn['user'] . "' password='" . $this->dsn['password'] . "'";
  }

  private $idUri = null;
  private $docUri = null;

  private $page = null;

  private $ns = array(
    "xsd" => "http://www.w3.org/2001/XMLSchema#",
    "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
    "rdfs" => "http://www.w3.org/2000/01/rdf-schema#",
    "owl" => "http://www.w3.org/2002/07/owl#",
    "dcat" => "http://www.w3.org/ns/dcat#",
    "dcterms" => "http://purl.org/dc/terms/",
    "foaf" => "http://xmlns.com/foaf/0.1/",
    "prv" => "http://purl.org/net/provenance/ns#",
    "geo" => "http://www.w3.org/2003/01/geo/wgs84_pos#",
    "schema" => "http://schema.org/",
    "gsp" => "http://www.opengis.net/ont/geosparql#",
    "sf" => "http://www.opengis.net/ont/sf#",
    "gml" => "http://www.opengis.net/gml",
    "kml" => "http://www.opengis.net/kml/2.2",
    "locn" => "http://www.w3.org/ns/locn#",
    "svg" => "http://www.w3.org/2000/svg",
//    "mt"  => "http://purl.org/NET/mediatypes/",
    "mt"  => "http://www.iana.org/assignments/media-types/",
    "void" => "http://rdfs.org/ns/void#"
  );

  private $geouris = array(
    "geouri" => array("Geo URI", "geo:",";u=0;crs=wgs84"),
    "geohash" => array("geohash.org", "http://geohash.org/",""),
    "geopoint" => array("PlaceTime.com", "http://placetime.com/geopoint/wgs84/","")
  );

// URL/path of the XSLT that generates the HTML presentation of the geometry from its RDF/XML representation.
// The default value for this variable is set in function save().

  private $xsluri = null;

// Use this function to change the path of the XSLT and/or to use a different XSLT.

  function setXSLT4HTML($url) {
    $this->xsluri = $url;
  }

  private function setGeoURIs() {
    if ($this->geometry != null && ( count($this->geometry->coordinates) == 2 || count($this->geometry->coordinates) == 3 ) && strtolower($this->geometry->type) == "point") {
    foreach ($this->geouris as $k => $v) {
      $path = '';
      switch ($k) {
        case "geouri";
          $path = $this->geometry->coordinates[1] . ',' . $this->geometry->coordinates[0];
          if (count($this->geometry->coordinates) == 3) {
            $path .= ',' . $this->geometry->coordinates[2];
          }
          break;
        case "geohash";
          if (count($this->geometry->coordinates) == 2) {
            $path = $this->format["geohash"];
          }
          break;
        case "geopoint";
          if (count($this->geometry->coordinates) == 2) {
            $path = 'X' . $this->geometry->coordinates[0] . 'Y' . $this->geometry->coordinates[1];
          }
          break;
      }
      if ($path != '') {
        $this->geouris[$k][] = $this->geouris[$k][1] . $path . $this->geouris[$k][2];
      }
    }
    }
  }

  private $fileFormats = array(
    "html" => array("HTML", "text/html", "", "http://www.w3.org/TR/html5/"),
    "rdf" => array("RDF/XML", "application/rdf+xml", "", "http://www.w3.org/TR/rdf-syntax-grammar/"),
//    "nt" => array("N-Triples", "text/plain", "", "http://www.w3.org/TR/n-triples/"),
    "nt" => array("N-Triples", "application/n-triples", "", "http://www.w3.org/TR/n-triples/"),
    "ttl" => array("Turtle", "text/turtle", "", "http://www.w3.org/TR/turtle/"),
    "n3" => array("N3", "text/n3", "Notation 3", "http://www.w3.org/TeamSubmission/n3/"),
    "jsonld" => array("JSON-LD", "application/ld+json", "JSON Linked Data", "http://www.w3.org/TR/json-ld/"),
    "txt" => array("WKT (GeoSPARQL)", "text/plain", "Well-Known Text", "http://www.opengeospatial.org/standards/sfa/"),
    "gml" => array("GML (GeoSPARQL)", "application/gml+xml", "Geography Markup Language", "http://www.opengeospatial.org/standards/gml/"),
    "kml" => array("KML", "application/vnd.google-earth.kml+xml", "Keyhole Markup Language", "http://www.opengeospatial.org/standards/kml/"),
//    "kmz" => array("KMZ", "application/vnd.google-earth.kmz", "zipped KML", ""),
//    "json" => array("GeoJSON", "application/json", "", "http://www.geojson.org/geojson-spec.html")
    "json" => array("GeoJSON", "application/vnd.geo+json", "", "http://www.geojson.org/geojson-spec.html"),
//    "json" => array("GeoJSON", "application/geo+json", "", "https://tools.ietf.org/html/rfc7946"),
//    "geojson" => array("GeoJSON", "application/json", "", "http://www.geojson.org/geojson-spec.html")
    "geojson" => array("GeoJSON", "application/vnd.geo+json", "", "http://www.geojson.org/geojson-spec.html"),
//    "geojson" => array("GeoJSON", "application/geo+json", "", "https://tools.ietf.org/html/rfc7946"),
    "svg" => array("SVG", "image/svg+xml", "Spatial Vector Graphics", "http://www.w3.org/TR/SVG/")
  );

  private $availableFileFormats = array("html", "rdf", "nt", "ttl", "n3", "jsonld", "txt");
//  private $availableFileFormats = array("html", "rdf", "nt", "ttl", "n3", "txt", "gml", "svg", "json");
  private $defsrs = 4326;
  private $SMPsrs = 3857;
  private $srs = null;
  private $georep = null;
  private $candidateFormats = array();
  private $format = array();

  private function getHttpParams() {
    if (isset($_GET["srs"])) {
      $this->srs = $_GET["srs"];
    }
    if (isset($_GET["georep"])) {
      $this->georep = str_replace("_", " ", $_GET["georep"]);
    }
    if (isset($_GET["format"]) && $_GET["format"] != '') {
      $this->candidateFormats[] = $_GET["format"];
    }
  }

  private function getEncodings() {
    $dbconn = pg_connect($this->getConnectionString()) or die();
    $res = pg_query($dbconn, "SELECT srid, srtext FROM PUBLIC.SPATIAL_REF_SYS WHERE srid = " . $this->srs . " ORDER BY srid, srtext");
    if (!$res || pg_num_rows($res) == 0) {
      header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
      $this->createPage404();
      exit($this->page);
    }
    while ($row = pg_fetch_row($res)) {
      $matches = array();
      preg_match("/^[^\[]+\[\"([^\"]+)\"/",$row[1],$matches);
      $srsdescr = null;
      if (isset($matches[1])) {
        $srsdescr = " (" . $matches[1] . ")";
      }
    }
    $dbconn = pg_connect($this->getConnectionString()) or die();
    $string = $this->georep;
    $srs = $this->srs;
    $defsrs = $this->defsrs;
    $SMPsrs = $this->SMPsrs;
    $precision = 15;
    $call = array();
    $call["ewkb"] = "ST_AsEWKB(ST_GeomFromText('" . $string . "'," . $srs . ")) AS ewkb";
    $call["hexewkb"] = "ST_AsHEXEWKB(ST_GeomFromText('" . $string . "'," . $srs . ")) AS hexewkb";
    $call["wkt"] = "ST_AsText(ST_GeomFromText('" . $string . "'," . $srs . ")) AS wkt";
//    $call["txt"] = "ST_AsText(ST_GeomFromText('" . $string . "'," . $srs . ")) AS txt";
    $call["wktas4326"] = "ST_AsText(ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . ")) AS wktAs4326";
    $call["ewkt"] = "ST_AsEWKT(ST_GeomFromText('" . $string . "'," . $srs . ")) AS ewkt";
    $call["gml"] = "ST_AsGML(3,ST_GeomFromText('" . $string . "'," . $srs . ")," . $precision . ",1) AS gml";
    $call["kml"] = "ST_AsKML(ST_GeomFromText('" . $string . "'," . $srs . ")) AS kml";
    $call["geojson"] = "ST_AsGeoJSON(1,ST_GeomFromText('" . $string . "'," . $srs . ")," . $precision . ",4) AS geojson";
    $call["json"] = "ST_AsGeoJSON(1,ST_GeomFromText('" . $string . "'," . $srs . ")," . $precision . ",4) AS json";
    $call["geojsonas4326"] = "ST_AsGeoJSON(1,ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . ")," . $precision . ",4) AS geojsonas4326";
    $call["geojsonassmp"]  = "ST_AsGeoJSON(1,ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $SMPsrs . ")," . $precision . ",4) AS geojsonassmp";
    $call["geohash"] = "ST_GeoHash(ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . ")) AS geohash";
//    $call["svg"] = "ST_AsSVG(ST_GeomFromText('" . $string . "'," . $srs . ")) AS svg";
    $call["svgas4326"] = "ST_AsSVG(ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . ")) AS svgAs4326";
    $call["bbox"] = "ST_Extent(ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . ")) AS bbox";
    $call["bboxaspolygon"] = "ST_AsText(ST_SetSRID(ST_Extent(ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . "))," . $defsrs . ")) AS bboxAsPolygon";
    $call["centroid"] = "ST_AsText(ST_Centroid(ST_Transform(ST_GeomFromText('" . $string . "'," . $srs . ")," . $defsrs . "))) AS centroid";


// Check whether the relevant geometry type is supported by GeoJSON

    foreach ($call as $k => $v) {
// Always check that result is not an error
      if (!($res = pg_query($dbconn, "SELECT " . $call[$k]))) {
        $call[$k] = "'' AS " . $k;
      }
    }

    $res = pg_query($dbconn, "SELECT " . join(",", $call));
// Always check that result is not an error
    if (!$res) {
      header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
      $this->createPage404();
      exit($this->page);
    }

    while ($row = pg_fetch_assoc($res)) {
      foreach ($row as $name => $value) {
        $this->format[$name] = $value;
      }
    }

// Disconnect
    pg_close($dbconn);
    $ns = $this->ns;
    if ($this->format["gml"] != "") {
      $this->availableFileFormats[] = "gml";
    }
    if ($this->format["json"] != "") {
      $this->availableFileFormats[] = "json";
    }
    if ($this->format["kml"] != "") {
      $this->availableFileFormats[] = "kml";
//    $this->availableFileFormats[] = "kmz";
    }
// Uncomment to include SVG as one of the available formats.
//    if ($this->format["svg"] != "") {
//      $this->availableFileFormats[] = "svg";
//    }
    $geometry = json_decode($this->format["geojsonas4326"]);
//    $geometry = json_decode($this->format["geojsonassmp"]);
    $this->geometry = $geometry;
    $this->setGeoURIs();
    $this->format["wkt-geosparql"] = preg_replace("/srid=([\d]+);/i", "<http://www.opengis.net/def/crs/EPSG/0/$1> ", $this->format["ewkt"]);
    $this->format["txt"] = $this->format["wkt-geosparql"];
    $this->format["gml-geosparql"] = preg_replace("/\"urn:ogc:def:crs:EPSG[:]{1,2}([\d]+)\"/i", "\"http://www.opengis.net/def/crs/EPSG/0/$1\"", $this->format["gml"]);
    $this->format["gml-geosparql-with-ns"] = preg_replace("/^(<gml:[a-z]+)/i", "$1" . ' xmlns:gml="' . $ns["gml"] . '"', $this->format["gml-geosparql"]);
    $this->format["kml"] = '<kml xmlns="' . $ns["kml"] . '"><Placemark><name>Geometry (WKT): ' . $this->format["wkt"] . '</name>' . $this->format["kml"] . '</Placemark></kml>';
// TBD: This needs to be fixed, to decide which should be the bounds to be used (here, those of WGS84 are hard-coded)
//    $this->format["svg"] = '<svg xmlns="' . $ns["svg"] . '" width="360" height="180"><path transform="translate(180,90)" d="' . $this->format["svg"] . '"/></svg>';
    $this->format["svgas4326"] = '<svg xmlns="' . $ns["svg"] . '" width="360" height="180"><path transform="translate(180,90)" d="' . $this->format["svgas4326"] . '"/></svg>';

/*
    $this->format["svg"] = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg width="1024" height="768" preserveAspectRatio="xMinYMin" viewBox="-90 -90 180 180" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="#176C65" fill-opacity="0.5" stroke="#ffffff" stroke-width="0.01" d="' . $this->format["svg"] . '"/></svg>';
*/
    $this->format["svgas4326"] = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg width="1024" height="768" preserveAspectRatio="xMinYMin" viewBox="-90 -90 180 180" version="1.1" xmlns="http://www.w3.org/2000/svg"><path fill="#176C65" fill-opacity="0.5" stroke="#ffffff" stroke-width="0.01" d="' . $this->format["svgas4326"] . '"/></svg>';

    $docUri = $this->docUri;
    $idUri = $this->idUri;
    $rdfNs = array("rdf", "rdfs", "owl", "xsd", "dcat", "dcterms", "foaf", "prv", "gsp", "sf");
    $matches = array();
    $this->format["ogc"] = '';
    if (trim($this->format["gml"]) != '') {
      preg_match("/^<gml:([^\s]+)/", $this->format["gml"], $matches);
      $ogcGeoType = $matches[1];
      $rdfDt = array(
          "wkt" => $this->ns["gsp"] . "wktLiteral",
          "gml" => $this->ns["gsp"] . "gmlLiteral"
      );
      $this->format["ogc"]  = '  <sf:' . $ogcGeoType . ' rdf:about="' . $idUri . '#geosparql">' . "\n";
      $this->format["ogc"] .= '    <gsp:asWKT rdf:datatype="' . $rdfDt["wkt"] . '"><![CDATA[' . $this->format["wkt-geosparql"] . ']]></gsp:asWKT>' . "\n";
      $this->format["ogc"] .= '    <gsp:asGML rdf:datatype="' . $rdfDt["gml"] . '"><![CDATA[' . $this->format["gml-geosparql-with-ns"] . ']]></gsp:asGML>' . "\n";
      $this->format["ogc"] .= '  </sf:' . $ogcGeoType . '>' . "\n";
    }
    $sameas["geopoint"] = '';
    $sameas["geohash"] = '';
    $sameas["geouri"] = '';
    $this->format["wgs84"] = '';
    $sameas["wgs84"] = '';
    $this->format["schema.org"] = '';
    $sameas["schema.org"] = '';
//    if (trim($geometry) != '') {
    if (is_object($geometry) && isset($geometry->type) && isset($geometry->coordinates)) {
      switch (strtolower($geometry->type)) {
        case "point":
          if (count($geometry->coordinates) > 1 && count($geometry->coordinates) < 4) {
            foreach ($this->geouris as $k => $v) {
              if (isset($v[3])) {
                $sameas[$k] = '    <owl:sameAs rdf:resource="' . $v[3] . '"/>' . "\n";
              }
            }
            $rdfNs[] = "geo";
            $sameas["wgs84"] = '    <owl:sameAs rdf:resource="' . $idUri . '#wgs84"/>' . "\n";
            $this->format["wgs84"] = '  <geo:Point rdf:about="' . $idUri . '#wgs84">' . "\n";
            $this->format["wgs84"] .= '    <geo:lat_long rdf:datatype="' . $ns["xsd"] . 'string">' . $geometry->coordinates[1] . ',' . $geometry->coordinates[0] . '</geo:lat_long>' . "\n";
            $this->format["wgs84"] .= '    <geo:lat rdf:datatype="' . $ns["xsd"] . 'decimal">' . $geometry->coordinates[1] . '</geo:lat>' . "\n";
            $this->format["wgs84"] .= '    <geo:long rdf:datatype="' . $ns["xsd"] . 'decimal">' . $geometry->coordinates[0] . '</geo:long>' . "\n";
            if (count($geometry->coordinates) == 3) {
              $this->format["wgs84"] .= '    <geo:alt rdf:datatype="' . $ns["xsd"] . 'decimal">' . $geometry->coordinates[2] . '</geo:alt>' . "\n";
            }
            $this->format["wgs84"] .= '  </geo:Point>' . "\n";
            $rdfNs[] = "schema";
            $sameas["schema.org"] = '    <owl:sameAs rdf:resource="' . $idUri . '#schema.org"/>' . "\n";
            $this->format["schema.org"] = '  <schema:GeoCoordinates rdf:about="' . $idUri . '#schema.org">' . "\n";
            $this->format["schema.org"] .= '    <schema:latitude rdf:datatype="' . $ns["xsd"] . 'decimal">' . $geometry->coordinates[1] . '</schema:latitude>' . "\n";
            $this->format["schema.org"] .= '    <schema:longitude rdf:datatype="' . $ns["xsd"] . 'decimal">' . $geometry->coordinates[0] . '</schema:longitude>' . "\n";
            if (count($geometry->coordinates) == 3) {
              $this->format["schema.org"] .= '    <schema:elevation rdf:datatype="' . $ns["xsd"] . 'decimal">' . $geometry->coordinates[2] . '</schema:elevation>' . "\n";
            }
            $this->format["schema.org"] .= '  </schema:GeoCoordinates>' . "\n";
          }
          break;
        case "linestring":
          if (count($geometry->coordinates[0]) == 2) {
            $rdfNs[] = "schema";
            $sameas["schema.org"] = '    <owl:sameAs rdf:resource="' . $idUri . '#schema.org"/>' . "\n";
            $this->format["schema.org"] = '  <schema:GeoShape rdf:about="' . $idUri . '#schema.org">' . "\n";
            $line = array();
            foreach ($geometry->coordinates as $p) {
              $line[] = join(" ", array_reverse($p));
            }
            $this->format["schema.org"] .= '    <schema:line rdf:datatype="' . $ns["xsd"] . 'string">' . join(" ", $line) . '</schema:line>' . "\n";
            $this->format["schema.org"] .= '  </schema:GeoShape>' . "\n";
          }
          break;
        case "polygon":
          if (count($geometry->coordinates) == 1 && count($geometry->coordinates[0][0]) == 2) {
            $rdfNs[] = "schema";
            $sameas["schema.org"] = '    <owl:sameAs rdf:resource="' . $idUri . '#schema.org"/>' . "\n";
            $this->format["schema.org"] = '  <schema:GeoShape rdf:about="' . $idUri . '#schema.org">' . "\n";
            $line = array();
            foreach ($geometry->coordinates[0] as $p) {
              $line[] = join(" ", array_reverse($p));
            }
            $this->format["schema.org"] .= '    <schema:polygon rdf:datatype="' . $ns["xsd"] . 'string">' . join(" ", $line) . '</schema:polygon>' . "\n";
            $this->format["schema.org"] .= '  </schema:GeoShape>' . "\n";
          }
          break;
      }
    }
    $xmlns = array();
    foreach ($rdfNs as $prefix) {
      $xmlns[] = 'xmlns:' . $prefix . '="' . $ns[$prefix] . '"';
    }
    $this->format["rdf"] = '
<rdf:RDF ' . join(" ", $xmlns) . '>
  <rdf:Description rdf:about="' . $docUri . '">
    <rdf:type rdf:resource="' . $ns["prv"] . 'DataItem"/>
    <dcterms:issued rdf:datatype="' . $ns["xsd"] . 'dateTime">' . date("c") . '</dcterms:issued>
    <foaf:primaryTopic rdf:resource="' . $idUri . '"/>' . "\n";
    foreach ($this->availableFileFormats as $k) {
      $v = $this->fileFormats[$k];
      $this->format["rdf"] .= '    <dcterms:hasFormat rdf:resource="' . $docUri . '.' . $k . '"/>' . "\n";
    }
    $this->format["rdf"] .= '  </rdf:Description>' . "\n";
    foreach ($this->availableFileFormats as $k) {
      $v = $this->fileFormats[$k];
      $this->format["rdf"] .= '  <rdf:Description rdf:about="' . $docUri . '.' . $k . '">
    <rdf:type rdf:resource="' . $ns["foaf"] . 'Document"/>
    <rdfs:label xml:lang="en">' . $v[0] . '</rdfs:label>';
      if ($v[2] != '') {
        $this->format["rdf"] .= '
    <rdfs:comment xml:lang="en">' . $v[2] . '</rdfs:comment>';
      }
      $this->format["rdf"] .= '
    <dcterms:description xml:lang="en">' . $v[0] . ' document about the following WKT-encoded geometry: ' . $this->format["wkt"] . ' &#x2013; EPSG:' . $srs . $srsdescr . '</dcterms:description>
    <dcterms:format rdf:parseType="Resource">
      <rdf:value rdf:datatype="' . $ns["dcterms"] . 'IMT">' . $v[1] . '</rdf:value>
    </dcterms:format>
    <dcterms:format rdf:resource="' . $ns["mt"] . $v[1] . '"/>
    <dcterms:conformsTo rdf:resource="' . $v[3] . '"/>
  </rdf:Description>' . "\n";
    }
    $this->format["rdf"] .= '  <rdf:Description rdf:about="' . $idUri . '">' . "\n";
    if ($srs != $defsrs) {
      $this->format["rdf"] .= '    <owl:sameAs rdf:resource="' . dirname(dirname($idUri)) . '/' . $defsrs . '/' . str_replace(' ','_',strtolower($this->format['wktas4326'])) . '"/>' . "\n";
    }
    $this->format["rdf"] .= '    <dcat:bbox rdf:datatype="' . $ns["gsp"] . 'wktLiteral">' . strtolower($this->format['bbox']) . '</dcat:bbox>' . "\n";
//    $this->format["rdf"] .= '    <dcat:bbox rdf:resource="' . dirname(dirname($idUri)) . '/' . $defsrs . '/' . str_replace(' ','_',strtolower($this->format['bboxaspolygon'])) . '"/>' . "\n";
    $this->format["rdf"] .= '    <dcat:centroid rdf:datatype="' . $ns["gsp"] . 'wktLiteral">' . strtolower($this->format['centroid']) . '</dcat:centroid>' . "\n";
//    $this->format["rdf"] .= '    <dcat:centroid rdf:resource="' . dirname(dirname($idUri)) . '/' . $defsrs . '/' . str_replace(' ','_',strtolower($this->format['centroid'])) . '"/>' . "\n";
    $this->format["rdf"] .= '    <foaf:primaryTopicOf rdf:resource="' . $docUri . '"/>' . "\n";
    $this->format["rdf"] .= '    <rdfs:label xml:lang="en">Geometry (WKT): ' . $this->format["wkt"] . ' &#x2013; EPSG:' . $srs . $srsdescr . '</rdfs:label>' . "\n";
    if ($this->format["ogc"] != '') {
      $this->format["rdf"] .= '      <owl:sameAs rdf:resource="' . $idUri . '#geosparql"/>' . "\n";
    }
/*
    <owl:sameAs rdf:resource="' . $idUri . '#wkt-geosparql"/>
    <owl:sameAs rdf:resource="' . $idUri . '#gml-geosparql"/>' . "\n";
*/
    $this->format["rdf"] .= $sameas["wgs84"];
    $this->format["rdf"] .= $sameas["schema.org"];
//    $this->format["rdf"] .= $sameas["geopoint"];
    $this->format["rdf"] .= $sameas["geohash"];
    $this->format["rdf"] .= $sameas["geouri"];
    $this->format["rdf"] .= '  </rdf:Description>' . "\n";
//    $this->format["rdf"] .= $this->format["ogc+wkt"] . $this->format["ogc+gml"] . $this->format["wgs84"] . $this->format["schema.org"];
    $this->format["rdf"] .= $this->format["ogc"] . $this->format["wgs84"] . $this->format["schema.org"];
    $this->format["rdf"] .= '</rdf:RDF>';

// Setting namespace prefixes

    \EasyRdf\RdfNamespace::set('adms', 'http://www.w3.org/ns/adms#');
    \EasyRdf\RdfNamespace::set('cnt', 'http://www.w3.org/2011/content#');
    \EasyRdf\RdfNamespace::set('dc', 'http://purl.org/dc/elements/1.1/');
    \EasyRdf\RdfNamespace::set('dcat', 'http://www.w3.org/ns/dcat#');
    \EasyRdf\RdfNamespace::set('dcterms', 'http://purl.org/dc/terms/');
    \EasyRdf\RdfNamespace::set('dctype', 'http://purl.org/dc/dcmitype/');
    \EasyRdf\RdfNamespace::set('foaf', 'http://xmlns.com/foaf/0.1/');
    \EasyRdf\RdfNamespace::set('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
    \EasyRdf\RdfNamespace::set('gsp', 'http://www.opengis.net/ont/geosparql#');
    \EasyRdf\RdfNamespace::set('locn', 'http://www.w3.org/ns/locn#');
    \EasyRdf\RdfNamespace::set('prov', 'http://www.w3.org/ns/prov#');
    \EasyRdf\RdfNamespace::set('prv', 'http://purl.org/net/provenance/ns#');
    \EasyRdf\RdfNamespace::set('schema', 'http://schema.org/');
    \EasyRdf\RdfNamespace::set('sf', 'http://www.opengis.net/ont/sf#');

    $graph = new \EasyRdf\Graph;
    $graph->parse($this->format["rdf"], "rdfxml", $idUri);
    $this->format["nt"] = $graph->serialise("ntriples");
    $this->format["ttl"] = $graph->serialise("turtle");
    $this->format["n3"] = $graph->serialise("n3");
    $this->format["jsonld"] = $graph->serialise("jsonld");

// If using Leaflet.js
    $geojsonas4326 = $this->format["geojsonas4326"];
// If using OpenLayers
    $geojsonassmp  = $this->format["geojsonassmp"];

// HTML presentation

    $xml = new DOMDocument;
    $xml->loadXML($this->format["rdf"],LIBXML_NOENT|LIBXML_NSCLEAN);

    $xsl = new DOMDocument;
    $xsl->load($this->xsluri);

    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xsl);

    $proc->setParameter("", "wkt", $string);
    $proc->setParameter("", "srs", $srs);
    $proc->setParameter("", "srsdescr", $srsdescr);
    $proc->setParameter("", "geojsonas4326", $geojsonas4326);
    $proc->setParameter("", "geojsonassmp", $geojsonassmp);
    $this->format["html"] = $proc->transformToXML($xml);

  }

  private function createHttpLinkHeader($currentformat) {
    global $srsdescr;
    $link = array();
    $rel = null;
    foreach ($this->availableFileFormats as $v) {
      if ($v != null) {
        $rel = 'alternate';
        if ($v == $currentformat) {
          $rel = 'self';
        }
        $link[] = '<' . $this->docUri . '.' . $v . ' >; rel="' . $rel . '"; title="' .  $this->fileFormats[$v][0] . ' document about the following WKT-encoded geometry: ' . $this->format["wkt"] . ' - EPSG:' . $this->srs . $srsdescr . '" ; type="' . $this->fileFormats[$v][1] . '"';
      }
    }
    return join(', ', $link);
  }

  function save() {
    $this->classdir = realpath(dirname(__FILE__)) .'/';
      if ($this->xsluri == '') {
      $this->xsluri = $this->classdir . 'rdf2html.xsl';
    }
    $docUriWithFileExt = 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    $this->docUri = preg_replace("/\)\.(.*)$/", ")", $docUriWithFileExt);
    $docUri = $this->docUri;
    $this->idUri = str_replace("/doc/", "/id/", $docUri);
    $idUri = $this->idUri;
    $result = "";
    $this->getHttpParams();
    if ($this->georep == null) {
      if ($this->srs == null) {
        $dbconn = pg_connect($this->getConnectionString()) or die();
        $res = pg_query($dbconn, "SELECT srid, srtext FROM PUBLIC.SPATIAL_REF_SYS ORDER BY srid, srtext") or die();
        if (!$res) {
          header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
          $this->createPage404();
          exit($this->page);
        }
        else {
          $subsets = null;
          $subsetsUri = null;
          $result = '<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF
  xmlns:rdf="' . $this->ns["rdf"] . '"
  xmlns:dcterms="' . $this->ns["dcterms"] . '"
  xmlns:foaf="' . $this->ns["foaf"] . '"
  xmlns:void="' . $this->ns["void"] . '">
  <void:DatasetDescription rdf:about="' . $docUri . '">
    <foaf:primaryTopic rdf:resource="' . $idUri . '"/>
  </void:DatasetDescription>
  <void:Dataset rdf:about="' . $idUri . '">
    <dcterms:subject rdf:resource="http://dbpedia.org/resource/Location"/>
    <dcterms:title xml:lang="en">Geometries in any coordinate reference system</dcterms:title>
    <dcterms:description xml:lang="en">The dataset of all geometries in any coordinate reference system.</dcterms:description>
    <void:uriSpace rdf:datatype="' . $this->ns["xsd"] . 'anyURI">' . $idUri . '</void:uriSpace>
' . $subsetsUri . '
  </void:Dataset>
' . $subsets . '
</rdf:RDF>';
          header("Content-type: application/rdf+xml");
          echo $result;
        }
      }
      else {
        $dbconn = pg_connect($this->getConnectionString()) or die();
        $res = pg_query($dbconn, "SELECT srid, srtext FROM PUBLIC.SPATIAL_REF_SYS WHERE srid = " . $this->srs . " ORDER BY srid, srtext") or die();
        if (!$res || pg_num_rows($res) == 0) {
          header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
          $this->createPage404();
          exit($this->page);
        }
        while ($row = pg_fetch_row($res)) {
          preg_match("/^[^\[]+\[\"([^\"]+)\"/",$row[1],$matches);
          $srsdescr = null;
          if (isset($matches[1])) {
            $srsdescr = " (" . $matches[1] . ")";
          }
        }
        $geotypes = array();
        $geotypes[] = array("geometry","geometries");
        $geotypes[] = array("geometrycollection","geometry collections");
        $geotypes[] = array("point","points");
        $geotypes[] = array("multipoint","multi-points");
        $geotypes[] = array("linestring","line strings");
        $geotypes[] = array("multilinestring","multi-line strings");
        $geotypes[] = array("circularstring","circular strings");
        $geotypes[] = array("polygon","polygons");
        $geotypes[] = array("multipolygon","multi-polygons");
        $geotypes[] = array("curvepolygon","curve polygons");
        $geotypes[] = array("curve","curves");
        $geotypes[] = array("multicurve","multi-curves");
        $geotypes[] = array("surface","surfaces");
        $geotypes[] = array("polyhedralsurface","polyhedral surfaces");
        $geotypes[] = array("tin","TINs (triangulated irregular networks)");
        $geotypes[] = array("triangle","triangles");
        $subsets = "";
        foreach ($geotypes as $geotype) {
          $subsets .= '
        <void:Dataset>
          <dcterms:title xml:lang="en">' . ucfirst($geotype[1]) . ' in CRS EPSG:' . $this->srs . '</dcterms:title>
          <dcterms:description xml:lang="en">The dataset of all ' . $geotype[1] . ' in coordinate reference system EPSG:' . $this->srs . $srsdescr . '.</dcterms:description>
          <void:uriSpace>' . $idUri . $geotype[0] . '</void:uriSpace>
        </void:Dataset>';
        }
        $result = '<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF
  xmlns:rdf="' . $this->ns["rdf"] . '"
  xmlns:dcterms="' . $this->ns["dcterms"] . '"
  xmlns:foaf="' . $this->ns["foaf"] . '"
  xmlns:void="' . $this->ns["void"] . '">
  <void:DatasetDescription rdf:about="' . $docUri . '">
    <foaf:primaryTopic rdf:resource="' . $idUri . '"/>
  </void:DatasetDescription>
  <void:Dataset rdf:about="' . $idUri . '">
    <dcterms:subject rdf:resource="http://dbpedia.org/resource/Location"/>
    <dcterms:title xml:lang="en">Geometries in CRS EPSG:' . $this->srs . '</dcterms:title>
    <dcterms:description xml:lang="en">The dataset of all geometries in coordinate reference system EPSG:' . $this->srs . $srsdescr . '.</dcterms:description>
    <void:subset rdf:parseType="Collection">' . $subsets . '
    </void:subset>
  </void:Dataset>
</rdf:RDF>';
        header("Content-type: application/rdf+xml");
        echo $result;
      }
    }
    else {
    $this->getEncodings();
    $xmlDecl = '<?xml version="1.0" encoding="utf-8"?>';
    $formatToType = array();
    $typeToFormat = array();
    $appTypes = array();
    $appTypes["type"] = array();
    $appTypes["qFactorApp"] = array();
    foreach ($this->availableFileFormats as $v) {
      $formatToType[$v] = $this->fileFormats[$v][1];
      $typeToFormat[$this->fileFormats[$v][1]][] = $v;
      $appTypes["type"][] = $this->fileFormats[$v][1];
      $appTypes["qFactorApp"][] = 1;
    }
    $contentType = null;
    if (count($this->candidateFormats) == 1) {
      if (in_array($this->candidateFormats[0],$this->availableFileFormats)) {
        $contentType = $formatToType[$this->candidateFormats[0]];
      }
      else {
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        $this->createPage404();
        exit($this->page);
      }
    } else {
// If using conNeg < 3.0.0:
//      $bestContentType = conNeg::mimeBest($appTypes);
// If using conNeg = 3.0.0
      $negotiator = new Negotiate();
      $bestContentType = $negotiator->mimeBest($_SERVER['HTTP_ACCEPT'], join($appTypes["type"], ",") . ";q=1");
// If using conNeg < 3.0.0:
//      $contentType = $bestContentType;
// If using conNeg = 3.0.0
      $contentType = $bestContentType->getType();
      if ($contentType == null) {
        header($_SERVER["SERVER_PROTOCOL"] . " 406 Not Acceptable");
      }
      else {
        $this->candidateFormats = $typeToFormat[$contentType];
      }
    }
      switch (count($this->candidateFormats)) {
        case 0:
          header($_SERVER["SERVER_PROTOCOL"] . " 415 Unsupported Media Type");
          break;
        case 1:
          header("Content-type: " . $contentType);
          header("Link: " . $this->createHttpLinkHeader($this->candidateFormats[0]));
// The "if" statement is meant to specify how to deal with formats not natively supported by GeoIRI
/*
          if ($contentType == "application/ld+json") {
            header('Location: http://rdf-translator.appspot.com/convert/detect/json-ld/' . urlencode($docUri));
            exit;
          }
*/
          if (preg_match("/xml$/", $contentType)) {
            echo $xmlDecl;
          }
          return $this->format[$this->candidateFormats[0]];
        break;
        default:
          $title = $_SERVER["SERVER_PROTOCOL"] . " 300 Multiple Choices";
          $content = "<p>For media type <code>" . $this->fileFormats[$this->candidateFormats[0]][1] . "</code> the following formats are available:</p>";
          $content .= "<ul>";
          foreach ($typeToFormat[$contentType] as $k => $v) {
            $content .= '<li><a href="' . $this->docUri . '.' . $v . '">' . $this->fileFormats[$v][0] . '</a></li>';
          }
          $content .= "</ul>";
          $this->createPage300($content);
          header($title);
          exit($this->page);
      }
    }
  }

  private function createPage($lang = 'en', $pageTitle = null, $css = array(), $cssCode = null, $js = array(), $jsCode = null, $content = null) {
    $output = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" lang="' . $lang . '"><head><title>' . strip_tags($pageTitle) . '</title>';
    foreach ($css as $v) {
      $output .= '<link rel="stylesheet" href="' . $v . '" type="text/css" />';
    }
    if ($cssCode != null) {
      $output .= '<style type="text/css">' . $cssCode . '</style>';
    }
    foreach ($js as $v) {
      $output .= '<script type="text/javascript" src="' . $v . '"></script>';
    }
    if ($jsCode != null) {
      $output .= '<script type="text/javascript">' . $jsCode . '</script>';
    }
    $output .= '<body><div id="container"><div id="page"><div id="header"><h1>' . preg_replace("/^[\d]*[\s]*/","",$pageTitle) . '</h1></div><div id="content">';
    $output .= '<section>' . $content . '</section>';
    $output .= '</div></div></div></body></html>';
    $this->page = $output;
  }

  function createPage300($content) {
    $pageTitle = "300 Multiple Choices";
    $this->createPage($lang = 'en', $pageTitle, $css = array(), $cssCode = null, $js = array(), $jsCode = null, $content);
  }

  function createPage400() {
    $pageTitle = "400 Bad Request";
    $content = "Your browser (or proxy) sent a request that this server could not understand.";
    $this->createPage($lang = 'en', $pageTitle, $css = array(), $cssCode = null, $js = array(), $jsCode = null, $content);
  }

  function createPage404() {
    $pageTitle = "404 Not Found";
    $content = "The requested URL " . $_SERVER["REQUEST_URI"] . " was not found on this server.";
    $this->createPage($lang = 'en', $pageTitle, $css = array(), $cssCode = null, $js = array(), $jsCode = null, $content);
  }

}

?>
