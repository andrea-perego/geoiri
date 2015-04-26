<?php

// Specify the connection parameters to the database.

// The name of the database
$database = 'geoiri';
// The username of the database owner
$username = 'geoiri';
// The password of the database owner
$password = '';
// The database host specification: hostname[:port]
$hostspec = 'localhost';

// Specify the relative path/URL of the required libraries.

$includepaths = array(
  "../lib/",
  "../lib/GeoIRI/",
  "../lib/easyrdf-0.6.2/lib/",
  "../lib/conNeg_2.0.2/PHP5.x/"
);

set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $includepaths));

// Specify the relative path/URL of the XSLT to be used to generate the HTML output from RDF/XML.

$xsluri = "../lib/GeoIRI/rdf2html.xsl";

require_once("GeoIRI.php");

$geoiri = new GeoIRI;
$geoiri->setDSN($username,$password,$database,$hostspec);
$geoiri->setXSLT4HTML($xsluri);
echo $geoiri->save();

?>