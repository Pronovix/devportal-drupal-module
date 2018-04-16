<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIExtDocTypeInterface;

/**
 * Defines the API Ext Doc type entity.
 *
 * @ConfigEntityType(
 *   id = "api_ext_doc_type",
 *   label = @Translation("API External Documentation type"),
 *   label_collection = @Translation("API External Documentation types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIExtDocTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIExtDocTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIExtDocTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIExtDocTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api ext doc types",
 *   config_prefix = "api_ext_doc_type",
 *   bundle_of = "api_ext_doc",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_ext_doc/add",
 *     "edit-form" = "/admin/structure/api_ext_doc/manage/{api_ext_doc_type}",
 *     "delete-form" = "/admin/structure/api_ext_doc/manage/{api_ext_doc_type}/delete",
 *     "collection" = "/admin/structure/api_ext_doc"
 *   },
 * )
 */
class APIExtDocType extends ConfigEntityBundleBase implements APIExtDocTypeInterface {

  /**
   * The API Ext Doc type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Ext Doc type label.
   *
   * @var string
   */
  protected $label;

}
