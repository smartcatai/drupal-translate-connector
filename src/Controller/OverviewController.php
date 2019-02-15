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
        $lastColumn = array_pop($build['content_translation_overview']['#header']);
        array_push($build['content_translation_overview']['#header'], $this->t('Translation process'));
        array_push($build['content_translation_overview']['#header'], $lastColumn);

        foreach ($build['content_translation_overview']['#rows'] as &$row){
            // take last column in row
            $operations = array_pop($row);
            $translationStatus = ['data'=>['#markup' => '&mdash;']];

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
                                'title' => 'Send to smartcat',
                                'url' => $url,
                            ];
                            continue;
                        }

                        $operations['data']['#links']['smartcat'] = [
                            'title' => 'Go to smartcat',
                            'url' => ApiHelper::getProjectUrlBydocument($document),
                        ];

                        $urlDocumentList->setOption('query', ['document_id' => $document->getId()]);

                        $translationStatusName = Document::STATUSES[$document->getStatus()];
                        $link = \Drupal\Core\Link::fromTextAndUrl($this->t($translationStatusName),$urlDocumentList)->toString();
                        $translationStatus['data']['#markup'] = $link;

                        if( $document->getStatus() === Project::STATUS_NEW){
                            \Drupal::messenger()->addMessage("Project {$document->getName()} created", Messenger::TYPE_STATUS);
                        }
                    }
                }
            }

            array_push($row, $translationStatus);
            array_push($row, $operations);
        }
        $build['content_translation_overview']['#attached']['library'][]
            = 'smartcat_translation_manager/send_to_smartcat';
        return $build; 

    }
}