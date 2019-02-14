<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Drupal\content_translation\Controller\ContentTranslationController;
use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Entity\Project;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;

class OverviewController extends ContentTranslationController
{
    public function overview(\Drupal\Core\Routing\RouteMatchInterface $route_match, $entity_type_id = NULL)
    {

        $documentRepository = new DocumentRepository();
        $build = parent::overview($route_match, $entity_type_id);
        $entity = $route_match->getParameter($entity_type_id);
        $query = ['entity_id' => $entity->id(), 'type'=> $entity->getType(), 'type_id' => $entity_type_id];

        /**
         * @var DocumentRepository
         */
        $documents = $documentRepository->getBy(['entityId'=> $entity->id(), ]);

        foreach ($build['content_translation_overview']['#rows'] as &$row){
            // take last column in row
            $operations = array_pop($row);
            // take next last column in row
            $status = array_pop($row);

            $urlDocumentList = Url::fromRoute('smartcat_translation_manager.document');

            $link = current($operations['data']['#links']);
            $params = $link['url']->getRouteParameters();

            if(isset($params['target'])){
                $query['lang'] = $params['target'];

                $operations['data']['#links'] = [];
                if(empty($documents)){
                    $url = Url::fromRoute('smartcat_translation_manager.project.add');
                    $url->setOption('query', $query);
                    $operations['data']['#links']['smartcat'] = [
                        'title' => 'Send to smartcat',
                        'url' => $url,
                    ];
                }else{
                    foreach($documents as $document){
                        if($query['lang'] !== $document->getTargetLanguage()){
                            $url = Url::fromRoute('smartcat_translation_manager.project.add');
                            $url->setOption('query', $query);
                            $operations['data']['#links']['smartcat'] = [
                                'title' => 'Send to smartcat' .$document->getTargetLanguage(),
                                'url' => $url,
                            ];
                            continue;
                        }
                        if(is_array($status)){
                            continue;
                        }

                        $operations['data']['#links']['smartcat'] = [
                            'title' => 'Go to smartcat',
                            'url' => ApiHelper::getProjectUrlBydocument($document),
                        ];

                        $urlDocumentList->setOption('query', ['document_id' => $document->getId()]);
                        $translationStatus = $status->render();
                        $link = \Drupal\Core\Link::fromTextAndUrl($document->getStatus(),$urlDocumentList)->toString();
                        $status = ['data'=>[]];
                        $status['data']['#markup'] = "$translationStatus <br><small>Translation state: $link</small>";

                        if( $document->getStatus() === Project::STATUS_NEW){
                            \Drupal::messenger()->addMessage("Project {$document->getName()} created", Messenger::TYPE_STATUS);
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