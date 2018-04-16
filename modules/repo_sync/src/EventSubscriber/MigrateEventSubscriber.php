<?php

namespace Drupal\devportal_repo_sync\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MigrateEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[MigrateEvents::PRE_IMPORT] = ['onPreImport', 800];

    return $events;
  }

  public function onPreImport(MigrateImportEvent $event) {
    $event->getMigration()->setTrackLastImported(TRUE);
  }

}
