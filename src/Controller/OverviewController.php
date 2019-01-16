<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Core\Url;
use Drupal\content_translation\Controller\ContentTranslationController;

class OverviewController extends ContentTranslationController
{
    public function overview(\Drupal\Core\Routing\RouteMatchInterface $route_match, $entity_type_id = NULL)
    {

        $build = parent::overview($route_match, $entity_type_id);
        $entity = $route_match->getParameter($entity_type_id);
        $url = Url::fromRoute('smartcat_translation_manager.project.add');
        $query = ['entity_id' => $entity->id(), 'type'=> $entity->getType(), 'type_id' => $entity_type_id];

        foreach ($build['content_translation_overview']['#rows'] as &$row){
            // take last column in row
            $operations = array_pop($row);

            $link = current($operations['data']['#links']);
            $params = $link['url']->getRouteParameters();

            if(isset($params['target'])){
                $query['lang'] = $params['target'];

                $url->setOption('query', $query);
                
                $operations['data']['#links']['smartcat'] = [
                    'title' => 'Send to smartcat',
                    'url' => $url,
                ];
            }

            array_push($row, $operations);
        }
        $build['content_translation_overview']['#attached']['library'][]
            = 'smartcat_translation_manager/send_to_smartcat';
        return $build; 

    }
}