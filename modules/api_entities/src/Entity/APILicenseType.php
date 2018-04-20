<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APILicenseTypeInterface;

/**
 * Defines the API License type entity.
 *
 * @ConfigEntityType(
 *   id = "api_license_type",
 *   label = @Translation("API License type"),
 *   label_collection = @Translation("API License types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APILicenseTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APILicenseTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APILicenseTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APILicenseTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api license types",
 *   config_prefix = "api_license_type",
 *   bundle_of = "api_license",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_license/add",
 *     "edit-form" = "/admin/devportal/config/api_license/manage/{api_license_type}",
 *     "delete-form" = "/admin/devportal/config/api_license/manage/{api_license_type}/delete",
 *     "collection" = "/admin/devportal/config/api_license"
 *   },
 * )
 */
class APILicenseType extends ConfigEntityBundleBase implements APILicenseTypeInterface {

  /**
   * The API License type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API License type label.
   *
   * @var string
   */
  protected $label;

}
