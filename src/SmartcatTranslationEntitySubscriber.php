<?php

namespace Drupal\smartcat_translation_manager;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Drupal\smartcat_translation_manager\Event\EntityEvent;
use Drupal\smartcat_translation_manager\Service\ProjectService;


/**
 * Subscriber for entity translation routes.
 */
class SmartcatTranslationEntitySubscriber implements EventSubscriberInterface {

    /**
     * The content translation manager.
     *
     * @var \Drupal\content_translation\ContentTranslationManagerInterface
     */
    protected $contentTranslationManager;

    /**
     * The languages manager.
     *
     * @var \Drupal\Core\Language\LangugeManagerInterface
     */
    protected $langugeManager;

    /**
     * The projects service.
     *
     * @var Drupal\smartcat_translation_manager\Service\ProjectService
     */
    protected $projectService;

    /**
     * @var \Drupal\Core\Language\Language
     */
    protected $defaultLanguage;

    /**
     * @param ContentTranslationManagerInterface $content_translation_manager
     * @param LanguageManager $language_manager
     * @param ProjectService $projectService
     */
    public function __construct(
        ContentTranslationManagerInterface $content_translation_manager,
        LanguageManager $language_manager,
        ProjectService $projectService
    ) {
        $this->contentTranslationManager = $content_translation_manager;
        $this->langugeManager = $language_manager;
        $this->projectService = $projectService;
        $this->projectRepository = new ProjectRepository();
        $this->defaultLanguage = $this->langugeManager->getDefaultLanguage();
    }

    protected function getLanguages($sourceLanguage)
    {
        $langs = [];
        foreach($this->langugeManager->getLanguages() as $language){
            if($language->getId() !== $sourceLanguage){
                $langs[] = $language->getId();
            }
        }
        return $langs;
    }

    protected function sendToTranslate($entity)
    {
        if($this->defaultLanguage->getId() !== $entity->language()->getId()){
            \Drupal::logger('smartcat_translation_manager')->info($this->defaultLanguage->getId() .'/'. $entity->language()->getId());
            return;
        }
        if($this->contentTranslationManager->isEnabled($entity->getEntityTypeId(), $entity->bundle())){
            $sourceLanguage = $entity->language()->getId();
            $langs = $this->getLanguages($sourceLanguage);
            $this->projectService->createProject($entity, $langs);
        }
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Drupal\smartcat_translation_manager\Event\EntityEvent $event
     */
    public function onEntityInsert(EntityEvent $event) {
        // $this->sendToTranslate($event->getEntity());
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Drupal\smartcat_translation_manager\Event\EntityEvent $event
     */
    public function onEntityUpdate(EntityEvent $event) {
        //$this->sendToTranslate($event->getEntity());
    }

    /**
     * Log the creation of a new node.
     *
     * @param \Drupal\smartcat_translation_manager\Event\EntityEvent $event
     */
    public function onEntityDelete(EntityEvent $event) {
        $this->projectRepository->delete($event->getEntity()->id());
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
