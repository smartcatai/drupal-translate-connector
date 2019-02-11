<?php


namespace Drupal\smartcat_translation_manager;

use Drupal\content_translation\ContentTranslationManager;
use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for entity translation routes.
 */
class SmartcatTranslationRouteSubscriber extends RouteSubscriberBase {

    /**
     * The content translation manager.
     *
     * @var \Drupal\content_translation\ContentTranslationManagerInterface
     */
    protected $contentTranslationManager;

    /**
     * Constructs a ContentTranslationRouteSubscriber object.
     *
     * @param \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager
     *   The content translation manager.
     */
    public function __construct(ContentTranslationManagerInterface $content_translation_manager) {
        $this->contentTranslationManager = $content_translation_manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function alterRoutes(RouteCollection $collection) {
        foreach ($this->contentTranslationManager->getSupportedEntityTypes() as $entity_type_id => $entity_type) {
            $is_admin = FALSE;
            $route_name = "entity.$entity_type_id.edit_form";
            if ($edit_route = $collection->get($route_name)) {
                $is_admin = (bool) $edit_route->getOption('_admin_route');
            }

            $load_latest_revision = ContentTranslationManager::isPendingRevisionSupportEnabled($entity_type_id);

            if ($entity_type->hasLinkTemplate('drupal:content-translation-overview')) {
                $route_name = "entity.$entity_type_id.content_translation_overview";
                $route = $collection->get($route_name);
                $route->setDefaults([
                    '_controller' => '\Drupal\smartcat_translation_manager\Controller\OverviewController::overview',
                    'entity_type_id' => $entity_type_id,
                ]);
                $collection->remove($route_name);
                $collection->add($route_name, $route);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        $events = parent::getSubscribedEvents();
        // Should run after AdminRouteSubscriber so the routes can inherit admin
        // status of the edit routes on entities. Therefore priority -210.
        $events[RoutingEvents::ALTER] = ['onAlterRoutes', -220];
        return $events;
    }

}
