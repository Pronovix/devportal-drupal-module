<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIBodyParamTypeInterface;

/**
 * Defines the API Body Param type entity.
 *
 * @ConfigEntityType(
 *   id = "api_body_param_type",
 *   label = @Translation("API HTTP Method Body Parameter type"),
 *   label_collection = @Translation("API HTTP Method Body Parameter types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIBodyParamTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIBodyParamTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIBodyParamTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIBodyParamTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api body param types",
 *   config_prefix = "api_body_param_type",
 *   bundle_of = "api_body_param",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_body_param/add",
 *     "edit-form" = "/admin/devportal/config/api_body_param/manage/{api_body_param_type}",
 *     "delete-form" = "/admin/devportal/config/api_body_param/manage/{api_body_param_type}/delete",
 *     "collection" = "/admin/devportal/config/api_body_param"
 *   },
 * )
 */
class APIBodyParamType extends ConfigEntityBundleBase implements APIBodyParamTypeInterface {

  /**
   * The API Body Param type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Body Param type label.
   *
   * @var string
   */
  protected $label;

}
