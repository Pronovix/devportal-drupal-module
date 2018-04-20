<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APISchemaTypeInterface;

/**
 * Defines the API Schema type entity.
 *
 * @ConfigEntityType(
 *   id = "api_schema_type",
 *   label = @Translation("API Schema type"),
 *   label_collection = @Translation("API Schema types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APISchemaTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APISchemaTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APISchemaTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APISchemaTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api schema types",
 *   config_prefix = "api_schema_type",
 *   bundle_of = "api_schema",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_schema/add",
 *     "edit-form" = "/admin/devportal/config/api_schema/manage/{api_schema_type}",
 *     "delete-form" = "/admin/devportal/config/api_schema/manage/{api_schema_type}/delete",
 *     "collection" = "/admin/devportal/config/api_schema"
 *   },
 * )
 */
class APISchemaType extends ConfigEntityBundleBase implements APISchemaTypeInterface {

  /**
   * The API Schema type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Schema type label.
   *
   * @var string
   */
  protected $label;

}
