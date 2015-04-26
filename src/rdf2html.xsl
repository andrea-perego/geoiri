<?xml version="1.0" encoding="utf-8" ?>
<!--

  # Copyright (c) 2015, Andrea Perego <http://about.me/andrea.perego>
  # Licence: http://opensource.org/licenses/MIT
  
-->
<xsl:transform
    xmlns:rec     = "http://www.w3.org/2001/02pd/rec54#"
    xmlns:xsl     = "http://www.w3.org/1999/XSL/Transform"
    xmlns:rdf     = "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:rdfs    = "http://www.w3.org/2000/01/rdf-schema#"
    xmlns:owl     = "http://www.w3.org/2002/07/owl#"
    xmlns:dcterms = "http://purl.org/dc/terms/"
    xmlns:foaf    = "http://xmlns.com/foaf/0.1/"
    xmlns:schema  = "http://schema.org/"
    xmlns:cc      = "http://creativecommons.org/ns#"
    xmlns:dcat    = "http://www.w3.org/ns/dcat#"
    xmlns:wdrs    = "http://www.w3.org/2007/05/powder-s#"
    xmlns:sioc    = "http://rdfs.org/sioc/ns#"
    xmlns:vs      = "http://www.w3.org/2003/06/sw-vocab-status/ns#"
    xmlns:vann    = "http://purl.org/vocab/vann/"
    xmlns:voaf    = "http://purl.org/vocommons/voaf#"
    xmlns:locn    = "http://www.w3.org/ns/locn#"
    version="1.0">

  <xsl:output method="html"
              doctype-system="about:legacy-compact"
              media-type="text/html"
              omit-xml-declaration="yes"
              encoding="UTF-8"
              indent="yes" />

<!-- Parameter for the code of the language used. -->              
              
  <xsl:param name="l">
    <xsl:text>en</xsl:text>
  </xsl:param>

<!-- Parameters passed to the XSLT by GeoIRI. -->  
  
  <xsl:param name="wkt"/>
  <xsl:param name="srs"/>
  <xsl:param name="srsdescr"/>
  <xsl:param name="geojson"/>

<!-- Main template -->  
  
  <xsl:template match="/">
  
    <xsl:param name="title" select="rdf:RDF/rdf:Description[foaf:primaryTopicOf]/rdfs:label"/>
    <xsl:param name="alternate">
      <xsl:for-each select="rdf:RDF/rdf:Description[dcterms:format]">
        <link rel="alternate" title="{rdfs:comment}" href="{@rdf:about}" type="{dcterms:format/rdf:value}" />
      </xsl:for-each>
    </xsl:param>
    <xsl:param name="formatList">
      <xsl:for-each select="rdf:RDF/rdf:Description[dcterms:format]">
        <xsl:if test="dcterms:format/rdf:value != 'text/html'">
          <a href="{@rdf:about}" title="{rdfs:comment}"><xsl:value-of select="rdfs:label"/></a><xsl:text> </xsl:text>
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
    <title><xsl:value-of select="$title"/></title>
    <xsl:copy-of select="$alternate"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>            
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />    
    <link rel="stylesheet" href="../../../css/style.css" type="text/css" />
    <link rel="stylesheet" href="../../../css/map.css" type="text/css" />
    <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
    <script type="text/javascript" src="../../../js/script.js">
    </script>
  </head>
  <body>
    <header><h1>GeoIRI</h1></header>
    <nav>
    </nav>
    <section>
      <form id="geoiri" action="../../.." method="get" onsubmit="getGeoIRI();return false;">
        <h1>
          <label for="geometry-wkt">Geometry (WKT)</label>
          <span style="float:right;"><label for="srid">EPSG : </label>
          <input type="text" title="Coordinate Reference System" id="srid" value="{$srs}" maxlength="6" size="6"/>
          <input type="submit" style="margin-left:5px;" id="getgeoiri" value="Get GeoIRI"/></span>
         </h1>
        <textarea id="geometry-wkt" title="Type or copy &amp; paste a WKT-encoded geometry"><xsl:value-of select="$wkt"/></textarea>
      </form>
    </section>
    <section>
      <div>
        <p>Representations of this geometry are also available in the following encodings:</p>
        <div id="format-list">
          <xsl:copy-of select="$formatList"/>
        </div>
      </div>
      <div id="map" class="smallmap"></div>
    </section>
    <aside>
    </aside>
    <footer><p>GeoIRI @ GitHub: <a href="https://github.com/andrea-perego/geoiri">https://github.com/andrea-perego/geoiri</a></p></footer>

    <script type="text/javascript">
      function onEachFeature(feature, layer) {
        if (feature.properties &amp;&amp; feature.properties.popupContent) {
          layer.bindPopup(feature.properties.popupContent);
        }
      }    
      var featurecollection = {
        "title": "Geometry (WKT): <xsl:value-of select="$wkt"/> - EPSG: <xsl:value-of select="$srs"/> <xsl:value-of select="$srsdescr"/>",
        "type": "FeatureCollection",
        "features": [
          {"geometry": {
            "type": "GeometryCollection",
            "geometries": [
              <xsl:value-of select="$geojson"/>
            ]
          },
          "type": "Feature",
          "properties": {
            "popupContent": '<p>Geometry (WKT):</p>' + '<p><code><xsl:value-of select="$wkt"/></code></p>' + '<p>EPSG: <xsl:value-of select="$srs"/> <xsl:value-of select="$srsdescr"/></p>'
          }}
        ]
      };
      var geometry = L.geoJson(featurecollection, { onEachFeature : onEachFeature });
      var map = L.map('map').setView([0,0],1);
      L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 18
      }).addTo(map);
      geometry.addTo(map);
      map.fitBounds(geometry.getBounds());
    </script>
   </body>
</html>
    
  </xsl:template>

</xsl:transform>
