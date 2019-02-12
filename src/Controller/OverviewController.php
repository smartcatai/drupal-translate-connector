<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Drupal\content_translation\Controller\ContentTranslationController;
use Drupal\smartcat_translation_manager\DB\Entity\Project;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;

class OverviewController extends ContentTranslationController
{
    public function overview(\Drupal\Core\Routing\RouteMatchInterface $route_match, $entity_type_id = NULL)
    {

        $projectRepository = new ProjectRepository();
        $build = parent::overview($route_match, $entity_type_id);
        $entity = $route_match->getParameter($entity_type_id);
        $query = ['entity_id' => $entity->id(), 'type'=> $entity->getType(), 'type_id' => $entity_type_id];

        /**
         * @var ProjectRepository
         */
        $projects = $projectRepository->getBy(['entityId'=> $entity->id(), 'status' =>[Project::STATUS_FINISHED,'<>'] ]);

        foreach ($build['content_translation_overview']['#rows'] as &$row){
            // take last column in row
            $operations = array_pop($row);
            // take next last column in row
            $status = array_pop($row);

            $urlProjectList = Url::fromRoute('smartcat_translation_manager.project');

            $link = current($operations['data']['#links']);
            $params = $link['url']->getRouteParameters();

            if(isset($params['target'])){
                $query['lang'] = $params['target'];

                $operations['data']['#links'] = [];
                if(empty($projects)){
                    $url = Url::fromRoute('smartcat_translation_manager.project.add');
                    $url->setOption('query', $query);
                    $operations['data']['#links']['smartcat'] = [
                        'title' => 'Send to smartcat',
                        'url' => $url,
                    ];
                }else{
                    foreach($projects as $project){
                        if(!in_array($query['lang'],$project->getTargetLanguages())){
                            continue;
                        }
                        if(is_array($status)){
                            continue;
                        }

                        $operations['data']['#links']['smartcat'] = [
                            'title' => 'Go to smartcat',
                            'url' => ApiHelper::getProjectUrl($project),
                        ];

                        $urlProjectList->setOption('query', ['project_id' => $project->getId()]);
                        $translationStatus = $status->render();
                        $link = \Drupal\Core\Link::fromTextAndUrl($project->getStatus(),$urlProjectList)->toString();
                        $status = ['data'=>[]];
                        $status['data']['#markup'] = "$translationStatus <br><small>Project state: $link</small>";

                        if( $project->getStatus() === Project::STATUS_NEW){
                            \Drupal::messenger()->addMessage("Project {$project->getName()} created", Messenger::TYPE_STATUS);
                        }
                    }
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