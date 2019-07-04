CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The Devportal module allows you to expose API reference documentation by
publishing Swagger/OpenAPI files - as well as create, edit, import and publish
conceptual documentation on your Drupal site.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/devportal
   
 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/devportal
   
 * The customization capabilites of the Devportal module can be expanded by the 'Page builder' module. With it, the user can create highly customisable pages with reusable building blocks.
   
 * You can add the 'FAQ' custom module to your Devportal. Questions can be grouped by topics and these topics can be used as filters on the FAQ listing page. 
  
 * If you are a content editor, we recommend you to enable the 'Guides' module, which describes the custom functionalities and collects the links for the related core drupal functionalities.


REQUIREMENTS
------------

This module requires only Drupal core, but optional modules can be added, such as:

* [Apigee Edge](https://www.drupal.org/project/apigee_edge)

* [Contact Emails](https://www.drupal.org/project/contact_emails)

* [EU Cookie Compilance](https://www.drupal.org/project/eu_cookie_compliance)

* [Google TagManager](https://www.drupal.org/project/google_tag)

* [Honeypot](https://www.drupal.org/project/honeypot)

* [Metatag](https://www.drupal.org/project/metatag)

* [Node view permissions](https://www.drupal.org/project/node_view_permissions)

* [Pathauto](https://www.drupal.org/project/pathauto)

* [Redirect](https://www.drupal.org/project/redirect)

* [Swagger UI Field Formatter](https://www.drupal.org/project/swagger_ui_formatter)

* [Views Accordion](https://www.drupal.org/project/views_accordion)


INSTALLATION
------------

 * Install the Developer portal module as you would normally install a
   contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
   further information.
 * Make sure the dependencies for all the (sub)modules you want to enable are
   met (recursively). An example: as the devportal_api_reference submodule
   depends on the swagger_ui_formatter module, you should also check the
   README of the latter, as it might contain some useful Composer-related
   information about pulling in its JS dependencies.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Devportal > Configuration to manage
       Devportal configurations.


MAINTAINERS
-----------

 * tamasd - https://www.drupal.org/u/tamasd
 * Balazs Wittmann (balazswmann) - https://www.drupal.org/u/balazswmann
 * lekob - https://www.drupal.org/u/lekob

Supporting organizations:

 * Pronovix - https://www.drupal.org/pronovix
