<?xml version="1.0" encoding="utf-8" ?>
<!--

  # Copyright (c) 2015-2016, Andrea Perego <http://about.me/andrea.perego>
  # Licence: http://opensource.org/licenses/MIT

-->
<xsl:transform
    xmlns:cc      = "http://creativecommons.org/ns#"
    xmlns:dcat    = "http://www.w3.org/ns/dcat#"
    xmlns:dcterms = "http://purl.org/dc/terms/"
    xmlns:foaf    = "http://xmlns.com/foaf/0.1/"
    xmlns:locn    = "http://www.w3.org/ns/locn#"
    xmlns:owl     = "http://www.w3.org/2002/07/owl#"
    xmlns:rdf     = "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:rdfs    = "http://www.w3.org/2000/01/rdf-schema#"
    xmlns:rec     = "http://www.w3.org/2001/02pd/rec54#"
    xmlns:schema  = "http://schema.org/"
    xmlns:sioc    = "http://rdfs.org/sioc/ns#"
    xmlns:vann    = "http://purl.org/vocab/vann/"
    xmlns:voaf    = "http://purl.org/vocommons/voaf#"
    xmlns:vs      = "http://www.w3.org/2003/06/sw-vocab-status/ns#"
    xmlns:wdrs    = "http://www.w3.org/2007/05/powder-s#"
    xmlns:xsl     = "http://www.w3.org/1999/XSL/Transform"
    version="1.0">

  <xsl:output method="html"
              doctype-system="about:legacy-compact"
              media-type="text/html"
              omit-xml-declaration="yes"
              encoding="UTF-8"
              indent="yes"
              exclude-result-prefixes = "rec xsl rdf rdfs owl dcterms foaf schema cc dcat wdrs sioc vs vann voaf locn" />

<!-- Parameter for the code of the language used. -->

  <xsl:param name="l">
    <xsl:text>en</xsl:text>
  </xsl:param>

<!-- Parameters passed to the XSLT by GeoIRI. -->

  <xsl:param name="aboutUrl">https://github.com/andrea-perego/geoiri/wiki</xsl:param>
  <xsl:param name="wkt"/>
  <xsl:param name="srs"/>
  <xsl:param name="srsdescr"/>
  <xsl:param name="geojsonas4326"/>
  <xsl:param name="geojsonassmp"/>

<!-- Main template -->

  <xsl:template match="/">

    <xsl:param name="title" select="rdf:RDF/rdf:Description[foaf:primaryTopicOf]/rdfs:label"/>
    <xsl:param name="alternate">
      <xsl:for-each select="rdf:RDF/rdf:Description[dcterms:format]">
        <link rel="alternate" title="{rdfs:label}" href="{@rdf:about}" type="{dcterms:format/rdf:value}" />
      </xsl:for-each>
    </xsl:param>
    <xsl:param name="formatList">
      <xsl:for-each select="rdf:RDF/rdf:Description[dcterms:format]">
        <xsl:if test="dcterms:format/rdf:value != 'text/html'">
          <a href="{@rdf:about}" title="{rdfs:comment}"><xsl:value-of select="rdfs:label"/></a>
        </xsl:if>
      </xsl:for-each>
    </xsl:param>
    <xsl:param name="formatListRDF">
      <xsl:for-each select="rdf:RDF/rdf:Description[dcterms:format]">
        <xsl:variable name="format" select="normalize-space(dcterms:format/rdf:value)"/>
        <xsl:if test="$format = 'application/rdf+xml' or $format = 'application/n-triples' or $format = 'text/turtle' or $format = 'text/n3' or $format = 'application/ld+json'">
          <xsl:if test="dcterms:format/rdf:value != 'text/html'">
            <a href="{@rdf:about}" title="{rdfs:comment}"><xsl:value-of select="rdfs:label"/></a>
          </xsl:if>
        </xsl:if>
      </xsl:for-each>
    </xsl:param>
    <xsl:param name="formatListOthers">
      <xsl:for-each select="rdf:RDF/rdf:Description[dcterms:format]">
        <xsl:variable name="format" select="normalize-space(dcterms:format/rdf:value)"/>
        <xsl:if test="not($format = 'application/rdf+xml' or $format = 'application/n-triples' or $format = 'text/turtle' or $format = 'text/n3' or $format = 'application/ld+json')">
          <xsl:if test="dcterms:format/rdf:value != 'text/html'">
            <a href="{@rdf:about}" title="{rdfs:comment}"><xsl:value-of select="rdfs:label"/></a>
          </xsl:if>
        </xsl:if>
      </xsl:for-each>
    </xsl:param>
    <xsl:param name="googleMapsLink">
      <xsl:if test="rdf:RDF/rdf:Description[dcterms:format/rdf:value = 'application/vnd.google-earth.kml+xml']">
        <div><a href="http://maps.google.com/?q={rdf:RDF/rdf:Description/@rdf:about}">View on Google Maps</a></div>
      </xsl:if>
    </xsl:param>

<html xml:lang="{$l}" lang="{$l}">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><xsl:value-of select="$title"/></title>
    <xsl:copy-of select="$alternate"/>
    <link type="text/css" rel="stylesheet" href="https://bootswatch.com/readable/bootstrap.css" media="screen"/>
    <link type="text/css" rel="stylesheet" href="https://bootswatch.com/assets/css/custom.min.css" media="screen"/>
    <link type="text/css" rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="https://bootswatch.com/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://bootswatch.com/assets/js/custom.js"></script>
<!--
    <link rel="stylesheet" href="../../../css/style.css" type="text/css" />
    <link rel="stylesheet" href="../../../css/map.css" type="text/css" />
-->
    <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
    <script type="text/javascript">
      $("html").css("visibility","hidden");
    </script>
    <script type="text/javascript" src="../../../js/style.js"></script>
    <script type="text/javascript" src="../../../js/map.js"></script>
    <script type="text/javascript" src="../../../js/script.js"></script>
    <script type="text/javascript">
      var featurecollection = {
        "title": "Geometry (WKT): <xsl:value-of select="$wkt"/> - EPSG: <xsl:value-of select="$srs"/> <xsl:value-of select="$srsdescr"/>",
        "type": "FeatureCollection",
        "features": [ { 
          "geometry": { "type": "GeometryCollection", "geometries": [ <xsl:value-of select="$geojsonas4326"/> ] },
          "type": "Feature",
          "properties": { "popupContent": '<p>Geometry (WKT):</p>' + '<p><code><xsl:value-of select="$wkt"/></code></p>' + '<p>EPSG: <xsl:value-of select="$srs"/> <xsl:value-of select="$srsdescr"/></p>' }
        } ]
      };
    </script>
  </head>
  <body>
    <header><h1>GeoIRI</h1></header>
    <nav>
    </nav>
    <section id="map-box">
      <div id="map"></div>
    </section>
    <section id="geoiri-section">
      <form id="geoiri" action="../../.." method="get" onsubmit="getGeoIRI();return false;">
        <h4>
          <label for="geometry-wkt">Geometry (as <a href="https://en.wikipedia.org/wiki/Well-known_text" target="_blank" title="Well-Known Text (Wikipedia)">WKT</a>)</label>
        </h4>
        <div id="geometry-wkt-box">
          <p id="geometry-wkt-help" class="help">Type or copy &amp; paste a WKT-encoded geometry</p>
          <textarea id="geometry-wkt" rows="6" style="resize:vertical;" placeholder="Type or copy &amp; paste a WKT-encoded geometry" title="Type or copy &amp; paste a WKT-encoded geometry"><xsl:value-of select="$wkt"/></textarea>
        </div>
        <div id="srid-box">
          <label for="srid"><a class="info" href="http://www.epsg-registry.org/" target="_blank" title="Click here for the list of EPSG coordinate reference systems."></a> EPSG</label>
          <input type="text" title="Coordinate Reference System" id="srid" value="{$srs}" minLength="4" maxLength="8"/>
        </div>
        <input type="submit" id="getgeoiri" value="Get GeoIRI"/>
      </form>
    </section>
    <section id="format-list-section">
      <h4>Available formats</h4>
      <div>
        <p>Representations of this geometry are also available in the following encodings:</p>
        <div id="format-list-rdf">
          <xsl:copy-of select="$formatListRDF"/>
        </div>
        <div id="format-list-others">
          <xsl:copy-of select="$formatListOthers"/>
        </div>
      </div>
    </section>
    <section id="about-section">
      <h4>About GeoIRI</h4>
      <div>
        <p>GeoIRI is an experimental implementation of a URI/IRI space meant to denote arbitrary geometries, in arbitrary coordinate reference systems, by dereferenceable HTTP URI/IRIs, resolving to multiple representations (HTML, RDF) and encodings (WKT, GML, GeoJSON, KML).</p>
        <p>For more information: <a target="_blank" href="https://github.com/andrea-perego/geoiri">GeoIRI @ GitHub</a></p>
      </div>
    </section>
    <aside>
    </aside>
    <footer>
    </footer>
   </body>
</html>

  </xsl:template>

</xsl:transform>
