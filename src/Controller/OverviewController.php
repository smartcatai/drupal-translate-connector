<?php

namespace Smartcat\Drupal\Controller;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Drupal\content_translation\Controller\ContentTranslationController;
use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Smartcat\Drupal\Helper\ApiHelper;

class OverviewController extends ContentTranslationController
{
    public function overview(\Drupal\Core\Routing\RouteMatchInterface $route_match, $entity_type_id = NULL)
    {

        $projectRepository = new ProjectRepository();
        $build = parent::overview($route_match, $entity_type_id);
        $entity = $route_match->getParameter($entity_type_id);
        $url = Url::fromRoute('smartcat_translation_manager.project.add');
        $urlProjectList = Url::fromRoute('smartcat_translation_manager.project');
        $query = ['entity_id' => $entity->id(), 'type'=> $entity->getType(), 'type_id' => $entity_type_id];

        /**
         * @var ProjectRepository
         */
        $project = $projectRepository->getBy(['entityId'=> $entity->id() ]);

        if(!empty($project) && $project[0]->getStatus() === Project::STATUS_NEW){
            $messenger = \Drupal::messenger();
            $messenger->addMessage("Project {$project[0]->getName()} created", Messenger::TYPE_STATUS);
        }

        foreach ($build['content_translation_overview']['#rows'] as &$row){
            // take last column in row
            $operations = array_pop($row);
            $status = array_pop($row);

            $link = current($operations['data']['#links']);
            $params = $link['url']->getRouteParameters();

            if(isset($params['target'])){
                $query['lang'] = $params['target'];

                $url->setOption('query', $query);
                $operations['data']['#links'] = [];
                $operations['data']['#links']['smartcat'] = [
                    'title' => 'Send to smartcat',
                    'url' => $url,
                ];

                if(!empty($project) && in_array($query['lang'],$project[0]->getTargetLanguages())){
                    $urlProjectList->setOption('query', ['project_id' => $project[0]->getId()]);
                    $translationStatus = $status->render();
                    $link = \Drupal\Core\Link::fromTextAndUrl($project[0]->getStatus(),$urlProjectList)->toString();
                    $status = ['data'=>[]];
                    $status['data']['#markup'] = "$translationStatus <br><small>Project state: $link</small>";
                }
            }

            array_push($row, $status);
            array_push($row, $operations);
        }
        $build['content_translation_overview']['#attached']['library'][]
            = 'smartcat_translation_manager/send_to_smartcat';
        return $build; 

    }
}