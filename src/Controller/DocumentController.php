<?php

namespace Drupal\smartcat_translation_manager\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
            '#title' => 'Dashboard',
            '#header' =>[
                'Item',
                'Source language',
                'Target language',
                'Status',
                'Smartcat project',
            ],
            '#rows' => [
            ]
        ];
        $criteria = [];
        $document_id = \Drupal::request()->query->get('document_id');
        if($document_id){
            $criteria['id'] = $document_id;
        }

        $total = $this->documentRepository->count();
        $page = pager_find_page();
        $perPage = 10;
        $offset = $perPage * $page;
        pager_default_initialize($total, $perPage);

        $documents = $this->documentRepository->getBy($criteria,(int)$offset, $perPage);
        $entityManager = \Drupal::entityTypeManager();

        if(!empty($documents)){
            foreach($documents as $i=>$document){
                $language = $this->languageManager()->getLanguage($document->getSourceLanguage());
                $options = ['language' => $language];
                $entity = $entityManager
                    ->getStorage($document->getEntityTypeId())
                    ->load($document->getEntityId());
                
                $edit_url = $entity->toUrl('canonical', $options);

                if($entity){
                    $table['#rows'][$i] = [
                        Link::fromTextAndUrl($entity->label(), $edit_url),
                        $document->getSourceLanguage(),
                        $document->getTargetLanguage(),
                        Document::STATUSES[$document->getStatus()],
                        [
                            'data' => [
                                '#type' => 'operations',
                                '#links' => [
                                    'smartcat_doc'=>[
                                        'url' => ApiHelper::getDocumentUrl($document),
                                        'title'=>$this->t('Go to Smartcat'),
                                    ]
                                ],
                            ]
                        ],
                    ];
                }else{
                    $table['#rows'][$i] = [
                        $document->getName(),
                        $document->getExternalExportId(),
                        $document->getTargetLanguage(),
                        Document::STATUSES[$document->getStatus()],
                        [
                            'data' => [
                                '#type' => 'form',
                                '#action' => Url::fromRoute('smartcat_translation_manager.document.delete',['id'=>$document->getId()])->toString(),
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
                ['#markup'=>'<br>'],
                'pager'=> [
                    '#type' => 'pager',
                ],
            ],
        ];
    }

    public function delete($id)
    {
        $this->documentRepository->delete($id);
        return new RedirectResponse(Url::fromRoute('smartcat_translation_manager.document')->toString());
    }

}