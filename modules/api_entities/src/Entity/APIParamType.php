<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIParamTypeInterface;

/**
 * Defines the API Parameter type entity.
 *
 * @ConfigEntityType(
 *   id = "api_param_type",
 *   label = @Translation("API Parameter type"),
 *   label_collection = @Translation("API Parameter types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIParamTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIParamTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIParamTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIParamTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api param types",
 *   config_prefix = "api_param_type",
 *   bundle_of = "api_param",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_param/add",
 *     "edit-form" = "/admin/devportal/config/api_param/manage/{api_param_type}",
 *     "delete-form" = "/admin/devportal/config/api_param/manage/{api_param_type}/delete",
 *     "collection" = "/admin/devportal/config/api_param"
 *   },
 * )
 */
class APIParamType extends ConfigEntityBundleBase implements APIParamTypeInterface {

  /**
   * The API Param type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Param type label.
   *
   * @var string
   */
  protected $label;

}
