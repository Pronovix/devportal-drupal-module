<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIMetaParamTypeInterface;

/**
 * Defines the API Meta Parameter type entity.
 *
 * @ConfigEntityType(
 *   id = "api_meta_param_type",
 *   label = @Translation("API Meta Parameter type"),
 *   label_collection = @Translation("API Meta Parameter types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIMetaParamTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIMetaParamTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIMetaParamTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIMetaParamTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer API Meta Parameter types",
 *   config_prefix = "api_meta_param_type",
 *   bundle_of = "api_meta_param",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_meta_param/add",
 *     "edit-form" = "/admin/structure/api_meta_param/manage/{api_meta_param_type}",
 *     "delete-form" = "/admin/structure/api_meta_param/manage/{api_meta_param_type}/delete",
 *     "collection" = "/admin/structure/api_meta_param"
 *   },
 * )
 */
class APIMetaParamType extends ConfigEntityBundleBase implements APIMetaParamTypeInterface {

  /**
   * The API Meta Parameter type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Meta Parameter type label.
   *
   * @var string
   */
  protected $label;

}
