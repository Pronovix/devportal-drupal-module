<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

class APIDocListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\devportal_api_entities\APIDocInterface $entity */
    $row['label'] = Link::createFromRoute($entity->label(), 'entity.api_doc.canonical', ['api_doc' => $entity->id()]);
    $row['status'] = $entity->isPublished() ? $this->t('published') : $this->t('not published');
    return $row + parent::buildRow($entity);
  }

}
