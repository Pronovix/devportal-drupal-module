<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APITagTypeInterface;

/**
 * Defines the API Tag type entity.
 *
 * @ConfigEntityType(
 *   id = "api_tag_type",
 *   label = @Translation("API Tag type"),
 *   label_collection = @Translation("API Tag types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APITagTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APITagTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APITagTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APITagTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api tag types",
 *   config_prefix = "api_tag_type",
 *   bundle_of = "api_tag",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_tag/add",
 *     "edit-form" = "/admin/devportal/config/api_tag/manage/{api_tag_type}",
 *     "delete-form" = "/admin/devportal/config/api_tag/manage/{api_tag_type}/delete",
 *     "collection" = "/admin/devportal/config/api_tag"
 *   },
 * )
 */
class APITagType extends ConfigEntityBundleBase implements APITagTypeInterface {

  /**
   * The API Tag type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Tag type label.
   *
   * @var string
   */
  protected $label;

}
