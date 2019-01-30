<?php


namespace Smartcat\Drupal;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Smartcat\Drupal\Event\EntityEvent;

/**
 * Subscriber for entity translation routes.
 */
class SmartcatTranslationEntitySubscriber implements EventSubscriberInterface {

    /**
     * Log the creation of a new node.
     *
     * @param \Smartcat\Drupal\Event\EntityEvent $event
     */
    public function onEntityInsert(EntityEvent $event) {
        $entity = $event->getEntity();
        \Drupal::logger(EntityEvent::ENTITY_INSERT)->notice('New @type: @title. Created by: @owner',
        array(
            '@type' => $entity->getType(),
            '@title' => $entity->label(),
            '@owner' => $entity->getOwner()->getDisplayName()
            ));
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Smartcat\Drupal\Event\EntityEvent $event
     */
    public function onEntityUpdate(EntityEvent $event) {
        $entity = $event->getEntity();
        \Drupal::logger(EntityEvent::ENTITY_UPDATE)->notice('Update @type: @title. Created by: @owner',
        array(
            '@type' => $entity->getType(),
            '@title' => $entity->label(),
            '@owner' => $entity->getOwner()->getDisplayName()
            ));
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Smartcat\Drupal\Event\EntityEvent $event
     */
    public function onEntityDelete(EntityEvent $event) {
        $entity = $event->getEntity();
        \Drupal::logger(EntityEvent::ENTITY_DELETE)->notice('Delete @type: @title. Created by: @owner',
        array(
            '@type' => $entity->getType(),
            '@title' => $entity->label(),
            '@owner' => $entity->getOwner()->getDisplayName()
            ));
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
