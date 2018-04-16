<?php

namespace Drupal\devportal_api_reference;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_migrate_batch\Batch\MigrationGeneratorInterface;
use Drupal\user\EntityOwnerInterface;

interface APIRefInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, EntityOwnerInterface, MigrationGeneratorInterface {

}
