<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIGlobalSchemaTypeInterface;

/**
 * Defines the API Global Schema type entity.
 *
 * @ConfigEntityType(
 *   id = "api_global_schema_type",
 *   label = @Translation("API Global Schema type"),
 *   label_collection = @Translation("API Global Schema types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIGlobalSchemaTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIGlobalSchemaTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIGlobalSchemaTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIGlobalSchemaTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer API Global Schema types",
 *   config_prefix = "api_global_schema_type",
 *   bundle_of = "api_global_schema",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/api_global_schema/add",
 *     "edit-form" = "/admin/structure/api_global_schema/manage/{api_global_schema_type}",
 *     "delete-form" = "/admin/structure/api_global_schema/manage/{api_global_schema_type}/delete",
 *     "collection" = "/admin/structure/api_global_schema"
 *   },
 * )
 */
class APIGlobalSchemaType extends ConfigEntityBundleBase implements APIGlobalSchemaTypeInterface {

  /**
   * The API Global Schema type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Global Schema type label.
   *
   * @var string
   */
  protected $label;

}
