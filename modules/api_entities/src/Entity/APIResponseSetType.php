<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIResponseSetTypeInterface;

/**
 * Defines the API Response Set type entity.
 *
 * @ConfigEntityType(
 *   id = "api_response_set_type",
 *   label = @Translation("API Response Set type"),
 *   label_collection = @Translation("API Response Set types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIResponseSetTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIResponseSetTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIResponseSetTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIResponseSetTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api response set types",
 *   config_prefix = "api_response_set_type",
 *   bundle_of = "api_response_set",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_response_set/add",
 *     "edit-form" = "/admin/devportal/config/api_response_set/manage/{api_response_set_type}",
 *     "delete-form" = "/admin/devportal/config/api_response_set/manage/{api_response_set_type}/delete",
 *     "collection" = "/admin/devportal/config/api_response_set"
 *   },
 * )
 */
class APIResponseSetType extends ConfigEntityBundleBase implements APIResponseSetTypeInterface {

  /**
   * The API Response Set type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Response Set type label.
   *
   * @var string
   */
  protected $label;

}
