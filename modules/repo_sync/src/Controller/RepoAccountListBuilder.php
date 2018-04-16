<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\devportal_repo_sync\Entity\RepoAccount;

/**
 * Lists the configured repository accounts.
 */
class RepoAccountListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];

    $header['label'] = $this->t('Label');
    $header['provider'] = $this->t('Provider');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var RepoAccount $entity */

    $row = [];
    $row['label'] = $entity->label();
    $row['provider'] = $entity->getProvider();

    return $row + parent::buildRow($entity);
  }

}
