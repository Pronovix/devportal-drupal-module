<?php

namespace Drupal\devportal_api_bundle\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_bundle\APIBundleTypeInterface;

/**
 * Defines the API Bundle type entity.
 *
 * @ConfigEntityType(
 *   id = "api_bundle_type",
 *   label = @Translation("API Bundle type"),
 *   label_collection = @Translation("API Bundle types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_bundle\APIBundleTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_bundle\APIBundleTypeForm",
 *       "add" = "Drupal\devportal_api_bundle\APIBundleTypeForm",
 *       "edit" = "Drupal\devportal_api_bundle\APIBundleTypeForm",
 *       "delete" = "Drupal\devportal_api_bundle\Form\DevportalAPIBundleEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api bundle types",
 *   config_prefix = "api_bundle_type",
 *   bundle_of = "api_bundle",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_bundle/add",
 *     "edit-form" = "/admin/structure/api_bundle/manage/{api_bundle_type}",
 *     "delete-form" = "/admin/structure/api_bundle/manage/{api_bundle_type}/delete",
 *     "collection" = "/admin/structure/api_bundle"
 *   },
 * )
 */
class APIBundleType extends ConfigEntityBundleBase implements APIBundleTypeInterface {

  /**
   * The API Bundle type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Bundle type label.
   *
   * @var string
   */
  protected $label;

}
