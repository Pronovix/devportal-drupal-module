<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIEndpointTypeInterface;

/**
 * Defines the API Endpoint type entity.
 *
 * @ConfigEntityType(
 *   id = "api_endpoint_type",
 *   label = @Translation("API Endpoint type"),
 *   label_collection = @Translation("API Endpoint types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIEndpointTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIEndpointTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIEndpointTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIEndpointTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api endpoint types",
 *   config_prefix = "api_endpoint_type",
 *   bundle_of = "api_endpoint",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_endpoint/add",
 *     "edit-form" = "/admin/devportal/config/api_endpoint/manage/{api_endpoint_type}",
 *     "delete-form" = "/admin/devportal/config/api_endpoint/manage/{api_endpoint_type}/delete",
 *     "collection" = "/admin/devportal/config/api_endpoint"
 *   },
 * )
 */
class APIEndpointType extends ConfigEntityBundleBase implements APIEndpointTypeInterface {

  /**
   * The API Endpoint type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Endpoint type label.
   *
   * @var string
   */
  protected $label;

}
