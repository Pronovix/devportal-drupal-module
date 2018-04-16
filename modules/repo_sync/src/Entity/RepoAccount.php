<?php

namespace Drupal\devportal_repo_sync\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\ConfigEntityType;

/**
 * Defines the RepoAccount entity.
 *
 * @ConfigEntityType(
 *   id = "repo_account",
 *   label = @Translation("Repository account"),
 *   handlers = {
 *     "view_builder" = "Drupal\devportal_repo_sync\Controller\RepoAccountViewBuilder",
 *     "list_builder" = "Drupal\devportal_repo_sync\Controller\RepoAccountListBuilder",
 *     "form" = {
 *       "add" = "Drupal\devportal_repo_sync\Form\RepoAccountForm",
 *       "edit" = "Drupal\devportal_repo_sync\Form\RepoAccountForm",
 *       "delete" = "Drupal\devportal_repo_sync\Form\RepoAccountDeleteForm",
 *     },
 *     "access" = "Drupal\devportal_repo_sync\RepoAccountAccessControlHandler",
 *   },
 *   config_prefix = "repo_account",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "uuid",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/system/repo_account/{repo_account}",
 *     "edit-form" = "/admin/config/system/repo_account/{repo_account}/edit",
 *     "delete-form" = "/admin/config/system/repo_account/{repo_account}/delete",
 *   },
 * )
 */
class RepoAccount extends ConfigEntityBase {

  /**
   * Administration name of the repository account.
   *
   * @var string
   */
  public $label;

  /**
   * Account provider.
   *
   * @var string
   */
  protected $provider;

  /**
   * An optional authentication method for the provider.
   *
   * @var string
   */
  protected $method;

  /**
   * Account identifier, e.g. username.
   *
   * @var string
   */
  protected $identifier;

  /**
   * Account secret, e.g. password.
   *
   * @var string
   */
  protected $secret;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->uuid();
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @param string $label
   * @return $this
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @return string
   */
  public function getProvider() {
    return $this->provider;
  }

  /**
   * @param string $provider
   * @return $this
   */
  public function setProvider($provider) {
    $this->provider = $provider;
    return $this;
  }

  /**
   * @return string
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * @param string $method
   * @return $this
   */
  public function setMethod($method) {
    $this->method = $method;
    return $this;
  }

  /**
   * @return string
   */
  public function getIdentifier() {
    return $this->identifier;
  }

  /**
   * @param string $identifier
   * @return $this
   */
  public function setIdentifier($identifier) {
    $this->identifier = $identifier;
    return $this;
  }

  /**
   * @return string
   */
  public function getSecret() {
    return $this->secret;
  }

  /**
   * @param string $secret
   * @return $this
   */
  public function setSecret($secret) {
    $this->secret = $secret;
    return $this;
  }

}
