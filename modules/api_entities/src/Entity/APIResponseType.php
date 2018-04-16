<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIResponseTypeInterface;

/**
 * Defines the API Response type entity.
 *
 * @ConfigEntityType(
 *   id = "api_response_type",
 *   label = @Translation("API Response type"),
 *   label_collection = @Translation("API Response types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIResponseTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIResponseTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIResponseTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIResponseTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api response types",
 *   config_prefix = "api_response_type",
 *   bundle_of = "api_response",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_response/add",
 *     "edit-form" = "/admin/structure/api_response/manage/{api_response_type}",
 *     "delete-form" = "/admin/structure/api_response/manage/{api_response_type}/delete",
 *     "collection" = "/admin/structure/api_response"
 *   },
 * )
 */
class APIResponseType extends ConfigEntityBundleBase implements APIResponseTypeInterface {

  /**
   * The API Response type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Response type label.
   *
   * @var string
   */
  protected $label;

}
