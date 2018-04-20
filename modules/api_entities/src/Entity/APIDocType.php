<?php

namespace Drupal\devportal_api_entities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\devportal_api_entities\APIDocTypeInterface;

/**
 * Defines the API Documentation type entity.
 *
 * @ConfigEntityType(
 *   id = "api_doc_type",
 *   label = @Translation("API Documentation type"),
 *   label_collection = @Translation("API Documentation types"),
 *   handlers = {
 *     "list_builder" = "Drupal\devportal_api_entities\APIDocTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\devportal_api_entities\APIDocTypeForm",
 *       "add" = "Drupal\devportal_api_entities\APIDocTypeForm",
 *       "edit" = "Drupal\devportal_api_entities\APIDocTypeForm",
 *       "delete" = "Drupal\devportal_api_entities\Form\DevportalEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer api doc types",
 *   config_prefix = "api_doc_type",
 *   bundle_of = "api_doc",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/devportal/config/api_doc/add",
 *     "edit-form" = "/admin/devportal/config/api_doc/manage/{api_doc_type}",
 *     "delete-form" = "/admin/devportal/config/api_doc/manage/{api_doc_type}/delete",
 *     "collection" = "/admin/devportal/config/api_doc"
 *   },
 * )
 */
class APIDocType extends ConfigEntityBundleBase implements APIDocTypeInterface {

  /**
   * The API Doc type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The API Doc type label.
   *
   * @var string
   */
  protected $label;

}
