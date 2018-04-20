<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIParamItemTypeInterface;

/**
 * Defines the API Param Item type entity.
 *
 * @ConfigEntityType(
 *   id = "api_param_item_type",
 *   label = @Translation("API HTTP Method Parameter Item type"),
 *   label_collection = @Translation("API HTTP Method Parameter Item types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIParamItemTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIParamItemTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIParamItemTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIParamItemTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api param item types",
 *   config_prefix = "api_param_item_type",
 *   bundle_of = "api_param_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_param_item/add",
 *     "edit-form" = "/admin/devportal/config/api_param_item/manage/{api_param_item_type}",
 *     "delete-form" = "/admin/devportal/config/api_param_item/manage/{api_param_item_type}/delete",
 *     "collection" = "/admin/devportal/config/api_param_item"
 *   },
 * )
 */
class APIParamItemType extends ConfigEntityBundleBase implements APIParamItemTypeInterface {

  /**
   * The API Param Item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Param Item type label.
   *
   * @var string
   */
  protected $label;

}
