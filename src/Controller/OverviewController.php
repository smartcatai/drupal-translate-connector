<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\Core\Url;
use Drupal\content_translation\Controller\ContentTranslationController;
use Drupal\smartcat_translation_manager\Api\Api;
use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;

class OverviewController extends ContentTranslationController
{
    public function overview(\Drupal\Core\Routing\RouteMatchInterface $route_match, $entity_type_id = NULL)
    {

        $sendButtonKey = 'smartcat';
        try{
            (new Api())->getAccount();
        }catch(\Exception $e){
            $sendButtonKey = 'smartcat-disabled';
            \Drupal::messenger()->addError(t('Invalid Smartcat account ID or API key. Please check <a href=":url">your credentials</a>.',[
                ':url' => Url::fromRoute('smartcat_translation_manager.settings')->toString(),
            ],['context'=>'smartcat_translation_manager']));
        }
        $documentRepository = new DocumentRepository();
        $build = parent::overview($route_match, $entity_type_id);
        $entity = $route_match->getParameter($entity_type_id);
        $query = ['entity_id' => $entity->id(), 'type'=> $entity->getEntityTypeId(), 'type_id' => $entity_type_id];

        /**
         * @var DocumentRepository
         */
        $documents = $documentRepository->getBy(['entityId'=> $entity->id(), ], 0, 100, ['id'=>'DESC']);
        $lastColumn = array_pop($build['content_translation_overview']['#header']);
        array_push($build['content_translation_overview']['#header'], $this->t('Translation process'));
        array_push($build['content_translation_overview']['#header'], $lastColumn);

        foreach ($build['content_translation_overview']['#rows'] as &$row){
            // take last column in row
            $operations = array_pop($row);
            $translationStatus = ['data'=>['#markup' => '&mdash;']];

            foreach($operations['data']['#links'] as $link){
                if(isset($link['language'])){
                    $lang = $link['language']->getId();
                }else{
                    $params = $link['url']->getRouteParameters();
                    if(isset($params['target'])){
                        $lang = $params['target'];
                    }
                }

                if(isset($lang) && $entity->language()->getId() !==$lang){
                    $query['lang'] = $lang;

                    $operations['data']['#links'] = [];
                    $foundDoc = null; 
                    if(!empty($documents)){
                        foreach($documents as $document){
                            if($query['lang'] !== strtolower($document->getTargetLanguage())){
                                continue;
                            }
                            if($foundDoc !== null && $document->getStatus() === Document::STATUS_DOWNLOADED ){
                                continue;
                            }
                            $foundDoc = $document;
                        }
                        if($foundDoc !== null){
                            $translationStatusName = Document::STATUSES[$foundDoc->getStatus()];
                            $translationStatus['data']['#markup'] = $this->t($translationStatusName);

                            if($foundDoc->getStatus() === Document::STATUS_DOWNLOADED){
                                $operations['data']['#links']['smartcat_refresh_doc'] = [
                                    'url' => Url::fromRoute('smartcat_translation_manager.document.refresh', 
                                        ['id'=>$foundDoc->getId()],
                                        ['query'=>['destination'=>\Drupal::request()->getRequestUri()]]
                                    ),
                                    'title'=>$this->t('Check updates'),
                                ];
                            }

                            $operations['data']['#links']['smartcat-doc'] = [
                                'title' => $this->t('Go to Smartcat'),
                                'url' => ApiHelper::getDocumentUrl($foundDoc),
                            ];
                        }
                    }
                    if(empty($operations['data']['#links']['smartcat-doc']) || (!empty($foundDoc) && $foundDoc->getStatus() === Document::STATUS_DOWNLOADED)){
                        $url = Url::fromRoute('smartcat_translation_manager.project.add');
                        $url->setOption('query', $query);
                        $operations['data']['#links'][$sendButtonKey] = [
                            'title' => $this->t('Send to Smartcat'),
                            'url' => $url,
                        ];
                    }
                    break;
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