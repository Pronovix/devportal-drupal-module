<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIQueryParamTypeInterface;

/**
 * Defines the API Query Param type entity.
 *
 * @ConfigEntityType(
 *   id = "api_query_param_type",
 *   label = @Translation("API HTTP Method Query Parameter type"),
 *   label_collection = @Translation("API HTTP Method Query Parameter types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIQueryParamTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIQueryParamTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIQueryParamTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIQueryParamTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api query param types",
 *   config_prefix = "api_query_param_type",
 *   bundle_of = "api_query_param",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_query_param/add",
 *     "edit-form" = "/admin/devportal/config/api_query_param/manage/{api_query_param_type}",
 *     "delete-form" = "/admin/devportal/config/api_query_param/manage/{api_query_param_type}/delete",
 *     "collection" = "/admin/devportal/config/api_query_param"
 *   },
 * )
 */
class APIQueryParamType extends ConfigEntityBundleBase implements APIQueryParamTypeInterface {

  /**
   * The API Query Param type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Query Param type label.
   *
   * @var string
   */
  protected $label;

}
