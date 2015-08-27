# Content

This folder includes the [Composer](https://getcomposer.org/)'s setup file ([`composer.json`](./composer.json)) to be used for the installation of the PHP libraries (and all the relevant dependencies) used by GeoIRI to manage content negotiation ([conNeg](https://github.com/ptlis/conneg)) and to generate the supported RDF serialisations ([EasyRDF](http://www.easyrdf.org/) + [ML/JSON-LD](https://github.com/lanthaler/JsonLD)).

To install these libraries:

* Move to this folder
* Download Composer. E.g.: `curl -s https://getcomposer.org/installer | php`
* Run `php composer.phar install`

The current version of GeoIRI uses the following versions of these libraries:

* conNeg: 3.0.0
* EasyRDF: 0.9.1
* ML/JSON-LD: ~1.0
