<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIFormDataParamTypeInterface;

/**
 * Defines the API Form Data Param type entity.
 *
 * @ConfigEntityType(
 *   id = "api_form_data_param_type",
 *   label = @Translation("API HTTP Method Form Data Parameter type"),
 *   label_collection = @Translation("API HTTP Method Form Data Parameter types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIFormDataParamTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIFormDataParamTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIFormDataParamTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIFormDataParamTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api form data param types",
 *   config_prefix = "api_form_data_param_type",
 *   bundle_of = "api_form_data_param",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_form_data_param/add",
 *     "edit-form" = "/admin/devportal/config/api_form_data_param/manage/{api_form_data_param_type}",
 *     "delete-form" = "/admin/devportal/config/api_form_data_param/manage/{api_form_data_param_type}/delete",
 *     "collection" = "/admin/devportal/config/api_form_data_param"
 *   },
 * )
 */
class APIFormDataParamType extends ConfigEntityBundleBase implements APIFormDataParamTypeInterface {

  /**
   * The API Form Data Param type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Form Data Param type label.
   *
   * @var string
   */
  protected $label;

}
