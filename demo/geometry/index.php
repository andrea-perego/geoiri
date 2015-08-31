<?php

// Specify the connection parameters to the database.

// The name of the database
$dbname = 'geoiri';
// The username of the database owner
$user = 'geoiri';
// The password of the database owner
$password = '';
// The database host name specification
$host = 'localhost';
// The port used by PostgreSQL. This parameter is optional, and must be specified only if 
// different from the default port (i.e., 5432)
//$port = '5432';


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
// When using the default port (i.e., 5432):
$geoiri->setDSN($user,$password,$dbname,$host);
// If the port is different from the default one:
//$geoiri->setDSN($user,$password,$dbname,$host,$port);

// If not using the default GeoIRI XSLT to generate the HTML output.
//$geoiri->setXSLT4HTML($xsluri);
echo $geoiri->save();

?>
