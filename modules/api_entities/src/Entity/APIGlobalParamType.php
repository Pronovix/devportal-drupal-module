<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIGlobalParamTypeInterface;

/**
 * Defines the API Global Parameter type entity.
 *
 * @ConfigEntityType(
 *   id = "api_global_param_type",
 *   label = @Translation("API Global Parameter type"),
 *   label_collection = @Translation("API Global Parameter types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIGlobalParamTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIGlobalParamTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIGlobalParamTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIGlobalParamTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer API Global Parameter types",
 *   config_prefix = "api_global_param_type",
 *   bundle_of = "api_global_param",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_global_param/add",
 *     "edit-form" = "/admin/devportal/config/api_global_param/manage/{api_global_param_type}",
 *     "delete-form" = "/admin/devportal/config/api_global_param/manage/{api_global_param_type}/delete",
 *     "collection" = "/admin/devportal/config/api_global_param"
 *   },
 * )
 */
class APIGlobalParamType extends ConfigEntityBundleBase implements APIGlobalParamTypeInterface {

  /**
   * The API Global Parameter type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Global Parameter type label.
   *
   * @var string
   */
  protected $label;

}
