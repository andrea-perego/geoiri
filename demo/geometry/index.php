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

// Load the required libraries.

require('../lib/composer/vendor/autoload.php');

// Specify the relative path/URL of the GeoIRI library.

$includepaths = array(
  "../lib/GeoIRI/"
);

set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $includepaths));

// Specify the relative path/URL of the XSLT to be used to generate the HTML output from RDF/XML.
// This is needed only if not using the default GeoIRI XSLT.
//$xsluri = "../lib/GeoIRI/rdf2html.xsl";

require_once("GeoIRI.php");

$geoiri = new GeoIRI;
$geoiri->setDSN($username,$password,$database,$hostspec);
// If not using the default GeoIRI XSLT to generate the HTML output.
//$geoiri->setXSLT4HTML($xsluri);
echo $geoiri->save();

?>
