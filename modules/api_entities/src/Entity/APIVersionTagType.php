<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIVersionTagTypeInterface;

/**
 * Defines the API Version Tag type entity.
 *
 * @ConfigEntityType(
 *   id = "api_version_tag_type",
 *   label = @Translation("API Version Tag type"),
 *   label_collection = @Translation("API Version Tag types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIVersionTagTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIVersionTagTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIVersionTagTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIVersionTagTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api version tag types",
 *   config_prefix = "api_version_tag_type",
 *   bundle_of = "api_version_tag",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_version_tag/add",
 *     "edit-form" = "/admin/structure/api_version_tag/manage/{api_version_tag_type}",
 *     "delete-form" = "/admin/structure/api_version_tag/manage/{api_version_tag_type}/delete",
 *     "collection" = "/admin/structure/api_version_tag"
 *   },
 * )
 */
class APIVersionTagType extends ConfigEntityBundleBase implements APIVersionTagTypeInterface {

  /**
   * The API Version Tag type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Version Tag type label.
   *
   * @var string
   */
  protected $label;

}
