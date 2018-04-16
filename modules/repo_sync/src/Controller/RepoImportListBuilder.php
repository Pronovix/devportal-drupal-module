<?php

namespace Drupal\devportal_repo_sync\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\devportal_repo_sync\Entity\RepoImport;

/**
 * Lists the repository imports.
 */
class RepoImportListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];

    $header['label'] = $this->t('Label');
    $header['repository'] = $this->t('Repository');
    $header['version'] = $this->t('Version');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];

    /** @var RepoImport $entity */
    $row['label'] = $entity->toLink();
    $row['reposiory'] = $entity->repository;
    $row['version'] = $entity->version;

    return $row + parent::buildRow($entity);
  }

}
