<?php

namespace Drupal\devportal_api_reference;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

class APIRefListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Title');
    $header['updated'] = $this->t('Updated');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\devportal_api_reference\Entity\APIRef $entity */
    $row['title'] = Link::createFromRoute($entity->label(), 'entity.api_ref.canonical', ['api_ref' => $entity->id()]);
    $row['updated'] = \Drupal::service('date.formatter')->format($entity->getRevisionCreationTime());
    return $row + parent::buildRow($entity);
  }

}
