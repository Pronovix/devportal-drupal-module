<?php

namespace Drupal\devportal_api_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

class APIBundleListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = Link::createFromRoute($entity->label(), 'entity.api_bundle.canonical', ['api_bundle' => $entity->id()]);
    return $row + parent::buildRow($entity);
  }

}
