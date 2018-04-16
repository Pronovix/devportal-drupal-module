<?php

namespace Drupal\devportal_api_reference\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_reference\APIRefTypeInterface;

/**
 * Defines the API Reference type entity.
 *
 * @ConfigEntityType(
 *   id = "api_ref_type",
 *   label = @Translation("API Reference type"),
 *   label_collection = @Translation("API Reference types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_reference\APIRefTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_reference\APIRefTypeForm",
 *       "add" = "Drupal\devportal_api_reference\APIRefTypeForm",
 *       "edit" = "Drupal\devportal_api_reference\APIRefTypeForm",
 *       "delete" = "Drupal\devportal_api_reference\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api ref types",
 *   config_prefix = "api_ref_type",
 *   bundle_of = "api_ref",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "filtered_extensions",
 *     "common_extensions",
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/reference/api-reference/add",
 *     "edit-form" = "/admin/devportal/config/reference/api-reference/manage/{api_ref_type}",
 *     "delete-form" = "/admin/devportal/config/reference/api-reference/manage/{api_ref_type}/delete",
 *     "collection" = "/admin/devportal/config/reference/api-reference"
 *   },
 * )
 */
class APIRefType extends ConfigEntityBundleBase implements APIRefTypeInterface {

  /**
   * Machine name.
   *
   * @var string
   */
  public $id;

  /**
   * Human-readable label.
   *
   * @var string
   */
  public $label;

  /**
   * Human-readable description.
   *
   * @var string
   */
  public $description;

  /**
   * Restricted extension list.
   *
   * @var string[]
   */
  public $filtered_extensions;

  /**
   * Full extension list.
   *
   * @var string[]
   */
  public $common_extensions;

}
