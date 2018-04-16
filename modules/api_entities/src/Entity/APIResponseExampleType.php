<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIResponseExampleTypeInterface;

/**
 * Defines the API Response Example type entity.
 *
 * @ConfigEntityType(
 *   id = "api_response_example_type",
 *   label = @Translation("API Response Example type"),
 *   label_collection = @Translation("API Response Example types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIResponseExampleTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIResponseExampleTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIResponseExampleTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIResponseExampleTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api response example types",
 *   config_prefix = "api_response_example_type",
 *   bundle_of = "api_response_example",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_response_example/add",
 *     "edit-form" = "/admin/structure/api_response_example/manage/{api_response_example_type}",
 *     "delete-form" = "/admin/structure/api_response_example/manage/{api_response_example_type}/delete",
 *     "collection" = "/admin/structure/api_response_example"
 *   },
 * )
 */
class APIResponseExampleType extends ConfigEntityBundleBase implements APIResponseExampleTypeInterface {

  /**
   * The API Response Example type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Response Example type label.
   *
   * @var string
   */
  protected $label;

}
