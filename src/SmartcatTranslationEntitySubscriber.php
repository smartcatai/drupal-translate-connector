<?php


namespace Smartcat\Drupal;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Smartcat\Drupal\Event\EntityEvent;


/**
 * Subscriber for entity translation routes.
 */
class SmartcatTranslationEntitySubscriber implements EventSubscriberInterface {

    public function __construct()
    {
        $this->projectRepository = new ProjectRepository();
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Smartcat\Drupal\Event\EntityEvent $event
     */
    public function onEntityInsert(EntityEvent $event) {
        $entity = $event->getEntity();
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Smartcat\Drupal\Event\EntityEvent $event
     */
    public function onEntityUpdate(EntityEvent $event) {
        $entity = $event->getEntity();
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Smartcat\Drupal\Event\EntityEvent $event
     */
    public function onEntityDelete(EntityEvent $event) {
        $entity = $event->getEntity();
        $this->projectRepository->delete($entity->id());
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        $events = [];
        $events[EntityEvent::ENTITY_INSERT] = ['onEntityInsert'];
        $events[EntityEvent::ENTITY_UPDATE] = ['onEntityUpdate'];
        $events[EntityEvent::ENTITY_DELETE] = ['onEntityDelete'];
        return $events;
    }

}
