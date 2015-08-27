# GeoIRI

GeoIRI is an experimental implementation of a URI/IRI space meant to denote arbitrary geometries, in arbitrary coordinate reference systems, by resolvable HTTP URI/IRIs.

This work builds upon the pioneering approach adopted by [Ian Davis](http://iandavis.com/) at [PlaceTime.com](http://placetime.com/) for spatial coordinates, where two-dimensional points in the [WGS84 datum](http://en.wikipedia.org/wiki/World_Geodetic_System) are encoded directly in the relevant URI, which is then resolved to different representations based on HTTP content negotiation.

For a description and installation instructions of GeoIRI see the [wiki](http://github.com/andrea-perego/geoiri/wiki).

## Content

* [`demo/`](demo/): folder containing a set of files that can be used to quickly set up a GeoIRI API.
* [`examples/`](examples/): folder containing examples of GeoIRI RDF ouput.
* [`LICENCE`](LICENCE): the GeoIRI licence.
* [`README.md`](README.md): this document.
* [`screenshots/`](screenshots/): folder containing screenshots of the GeoIRI API frontend.
* [`src/`](src/): folder containing the files implementing the GeoIRI API.
  * [`src/GeoIRI.php`](src/GeoIRI.php): file contaning the PHP class implementing GeoIRI.
  * [`src/rdf2html.xsl`](src/rdf2html.xsl): XSLT used by GeoIRI to generate the HTML presentation of a given geometry from its RDF/XML representation.
  * [`src/.htaccess`](src/.htaccess): Apache configuration file including the URL re-writing rules for the GeoIRI API.

