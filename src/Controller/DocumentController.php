<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Drupal\smartcat_translation_manager\Helper\FileHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Cache\Cache;
class DocumentController extends ControllerBase
{
    /**
     * @var \Drupal\smartcat_translation_manager\Api\Api
     */
    protected $api;
    protected $documentRepository;

    public function __construct()
    {
        $this->api = new \Drupal\smartcat_translation_manager\Api\Api();
        $this->documentRepository = new DocumentRepository();
    }

    public function content() {
        $table = [
            '#type' => 'table',
            '#title' => 'Documents',
            '#header' =>[
                'Document name',
             //   'Content',
                'Translate to',
                'Status',
                'Operations',
            ],
            '#rows' => [
            ]
        ];
        $criteria = [];
        $document_id = \Drupal::request()->query->get('document_id');
        if($document_id){
            $criteria['id'] = $document_id;
        }
        $documents = $this->documentRepository->getBy($criteria);
        $entityManager = \Drupal::entityTypeManager();

        if(!empty($documents)){
            foreach($documents as $i=>$document){
                $entity = $entityManager
                    ->getStorage($document->getEntityTypeId())
                    ->load($document->getEntityId());

                if($entity){
                    $table['#rows'][$i] = [
                        ApiHelper::getProjectName($document),
                        //$entity->label(),
                        $document->getTargetLanguage(),
                        Document::STATUSES[$document->getStatus()],
                        [
                            'data' => [
                                '#type' => 'form',
                                '#action' => Url::fromRoute('smartcat_translation_manager.project.delete',['id'=>$document->getId()])->toString(),
                                'submit' => [
                                    '#type'=>'submit',
                                    '#value'=>'Delete',
                                ],
                            ],
                        ],
                    ];
                }else{
                    $table['#rows'][$i] = [
                        $document->getName(),
                        //'Not exists',
                        $document->getTargetLanguage(),
                        Document::STATUSES[$document->getStatus()],
                        [
                            'data' => [
                                '#type' => 'form',
                                '#action' => Url::fromRoute('smartcat_translation_manager.project.delete',['id'=>$document->getId()])->toString(),
                                'submit' => [
                                    '#type'=>'submit',
                                    '#value'=>'Delete',
                                ],
                            ],
                        ],
                    ];
                }
            }
        }

        return [
            '#type' => 'page',
            'header' => ['#markup'=>'<h1>Document list</h1>'],
            'content' => [
                ['#markup'=>'<br>'],
                $table,
            ],
        ];
    }

    public function delete($id)
    {
        $this->documentRepository->delete($id);
        return new RedirectResponse(Url::fromRoute('smartcat_translation_manager.project')->toString());
    }

}