<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\smartcat_translation_manager\Api\Api;
use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for work with documents.
 */
class DocumentController extends ControllerBase {
  /**
   * @var \Drupal\smartcat_translation_manager\Api\Api
   */
  protected $api;
  protected $documentRepository;

  /**
   * Init dependencies.
   */
  public function __construct() {
    $this->api = new Api();
    $this->documentRepository = new DocumentRepository();
    $this->entityManager = \Drupal::entityTypeManager();
  }

  /**
   * Method for display list documents(dashboard)
   * @return RedirectResponse
   */
  public function content() {
    $table = [
      '#type' => 'table',
      '#title' => 'Dashboard',
      '#header' => [
        'Item',
        'Source language',
        'Target language',
        'Status',
        'Smartcat project',
      ],
      '#rows' => [],
    ];
    $criteria = [];
    $sendButtonKey = 'smartcat';
    try {
      $account = (new Api())->getAccount();
    }
    catch (\Exception $e) {
      $sendButtonKey = 'smartcat-disabled';
      \Drupal::messenger()->addError(t('Invalid Smartcat account ID or API key. Please check <a href=":url">your credentials</a>.', [
        ':url' => Url::fromRoute('smartcat_translation_manager.settings')->toString(),
      ], ['context' => 'smartcat_translation_manager']));
    }
    $document_id = \Drupal::request()->query->get('document_id');
    if ($document_id) {
      $criteria['id'] = $document_id;
    }

    $total = $this->documentRepository->count();
    $page = pager_find_page();
    $perPage = 10;
    $offset = $perPage * $page;
    pager_default_initialize($total, $perPage);

    $documents = $this->documentRepository->getBy($criteria, (int) $offset, $perPage, ['id' => 'DESC']);

    if (!empty($documents)) {
      foreach ($documents as $i => $document) {
        $operations = [
          'data' => [
            '#type' => 'operations',
            '#links' => [],
          ],
        ];

        $language = $this->languageManager()->getLanguage(strtolower($document->getSourceLanguage()));
        $targetLanguage = $this->languageManager()->getLanguage(strtolower($document->getTargetLanguage()));
        $options = ['language' => $language];
        $entity = $this->entityManager
          ->getStorage($document->getEntityTypeId())
          ->load($document->getEntityId());

        if ($entity) {

          if ($document->getStatus() === Document::STATUS_DOWNLOADED) {
            $newestDocs = $this->documentRepository
              ->getBy([
                'entityId' => $document->getEntityId(),
                'id' => [$document->getId(), '>'],
                'targetLanguage' => $document->getTargetLanguage(),
              ]);
            if (count($newestDocs) === 0) {
              $operations['data']['#links']['smartcat_refresh_doc'] = [
                'url' => Url::fromRoute('smartcat_translation_manager.document.refresh', ['id' => $document->getId()]),
                'title' => $this->t('Check updates'),
              ];

              $query = ['entity_id' => $entity->id(), 'type_id' => $entity->getEntityTypeId(), 'lang' => $document->getTargetLanguage()];
              $url = Url::fromRoute('smartcat_translation_manager.project.add');
              $url->setOption('query', $query);
              $operations['data']['#links'][$sendButtonKey] = [
                'title' => $this->t('Send to Smartcat'),
                'url' => $url,
              ];
            }
          }

          $operations['data']['#links']['smartcat_doc'] = [
            'url' => ApiHelper::getDocumentUrl($document),
            'title' => $this->t('Go to Smartcat'),
          ];

          $edit_url = $entity->toUrl('canonical', $options);
          $table['#rows'][$i] = [
            Link::fromTextAndUrl($entity->label(), $edit_url),
            $language ? $language->getName() : $document->getSourceLanguage(),
            $targetLanguage ? $targetLanguage->getName() : $document->getTargetLanguage(),
            Document::STATUSES[$document->getStatus()],
            $operations,
          ];
        }
        else {
          $table['#rows'][$i] = [
            $this->t('Entity Not found'),
            $language ? $language->getName() : $document->getSourceLanguage(),
            $targetLanguage ? $targetLanguage->getName() : $document->getTargetLanguage(),
            Document::STATUSES[$document->getStatus()],
            $operations,
          ];
        }
      }
    }
    return [
      '#type' => 'page',
      'header' => ['#markup' => '<h1>Dashboard</h1>'],
      'content' => [
                ['#type' => 'status_messages'],
                ['#markup' => '<br>'],
        $table,
                ['#markup' => '<br>'],
        'pager' => [
          '#type' => 'pager',
        ],
      ],
    ];
  }

  /**
   * Method for refrash translation
   *
   * @param int $id
   * @return RedirectResponse
   */
  public function refresh($id) {
    $document = $this->documentRepository->getOneBy(['id' => $id]);
    if ($document->getStatus() === Document::STATUS_DOWNLOADED) {
      $document->setStatus(Document::STATUS_INPROGRESS);
      $document->setExternalExportId(NULL);
      $this->documentRepository->update($document);
      \Drupal::messenger()->addMessage(t('Your request for translation updates was successfully submitted.', [], ['context' => 'smartcat_translation_manager']));
    }
    $prev = \Drupal::request()->query->get('destination', FALSE);
    if ($prev) {
      return new RedirectResponse($prev);
    }
    return new RedirectResponse(Url::fromRoute('smartcat_translation_manager.document')->toString());
  }

  /**
   * @return RedirectResponse
   */
  public function delete($id) {
    $this->documentRepository->delete($id);
    return new RedirectResponse(Url::fromRoute('smartcat_translation_manager.document')->toString());
  }

}
