<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIContactTypeInterface;

/**
 * Defines the API Contact type entity.
 *
 * @ConfigEntityType(
 *   id = "api_contact_type",
 *   label = @Translation("API Contact type"),
 *   label_collection = @Translation("API Contact types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIContactTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIContactTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIContactTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIContactTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api contact types",
 *   config_prefix = "api_contact_type",
 *   bundle_of = "api_contact",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_contact/add",
 *     "edit-form" = "/admin/structure/api_contact/manage/{api_contact_type}",
 *     "delete-form" = "/admin/structure/api_contact/manage/{api_contact_type}/delete",
 *     "collection" = "/admin/structure/api_contact"
 *   },
 * )
 */
class APIContactType extends ConfigEntityBundleBase implements APIContactTypeInterface {

  /**
   * The API Contact type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Contact type label.
   *
   * @var string
   */
  protected $label;

}
