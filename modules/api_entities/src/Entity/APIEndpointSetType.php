<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIEndpointSetTypeInterface;

/**
 * Defines the API Endpoint Set type entity.
 *
 * @ConfigEntityType(
 *   id = "api_endpoint_set_type",
 *   label = @Translation("API Endpoint Set type"),
 *   label_collection = @Translation("API Endpoint Set types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIEndpointSetTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIEndpointSetTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIEndpointSetTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIEndpointSetTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api endpoint set types",
 *   config_prefix = "api_endpoint_set_type",
 *   bundle_of = "api_endpoint_set",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_endpoint_set/add",
 *     "edit-form" = "/admin/devportal/config/api_endpoint_set/manage/{api_endpoint_set_type}",
 *     "delete-form" = "/admin/devportal/config/api_endpoint_set/manage/{api_endpoint_set_type}/delete",
 *     "collection" = "/admin/devportal/config/api_endpoint_set"
 *   },
 * )
 */
class APIEndpointSetType extends ConfigEntityBundleBase implements APIEndpointSetTypeInterface {

  /**
   * The API Endpoint Set type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Endpoint Set type label.
   *
   * @var string
   */
  protected $label;

}
