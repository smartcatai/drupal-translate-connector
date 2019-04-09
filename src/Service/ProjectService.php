<?php

namespace Drupal\smartcat_translation_manager\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\smartcat_translation_manager\Api\Api;
use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Entity\Project;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Drupal\smartcat_translation_manager\Helper\FileHelper;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Drupal\smartcat_translation_manager\Helper\LanguageCodeConverter;

/**
 *
 */
class ProjectService {
  /**
   * @var \Drupal\smartcat_translation_manager\Api\Api
   */
  protected $api;

  /**
   * @var \Drupal\smartcat_translation_manager\DB\Entity\ProjectRepository
   */
  protected $projectRepository;

  /**
   * @var \Drupal\smartcat_translation_manager\DB\Entity\DocumentRepository
   */
  protected $documentRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\smartcat_translation_manager\DB\Entity\Project
   */
  protected $project;

  /**
   * @var array
   */
  protected $documents = [];

  /**
   * @param EntityManagerInterface $entityManager
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->entityManager = $entityManager;
    $this->api = new Api();
    $this->documentRepository = new DocumentRepository();
    $this->projectRepository = new ProjectRepository();
    $this->logger = \Drupal::logger('smartcat_translation_manager_project');
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param array $translateTo
   *   string[].
   * @return int $project_id
   */
  public function createProject(EntityInterface $entity, $translateTo = NULL) {
    $this->project = (new Project())
      ->setName($entity->label())
      ->setEntityTypeId($entity->getEntityTypeId())
      ->setSourceLanguage($entity->language()->getId())
      ->setTargetLanguages($translateTo)
      ->setStatus(Project::STATUS_NEW);
  }

  /**
   * @return \Drupal\smartcat_translation_manager\DB\Entity\Project
   */
  public function sendProject() {
    if (empty($this->project)) {
      return NULL;
    }
    $project = $this->project;
    $scProject = $this->api->createProject($project);

    $project->setExternalProjectId($scProject->getId());
    $project->setName($scProject->getName());

    $project->setId($this->projectRepository->add($project));
    $this->project = NULL;
    return $project;
  }

  /**
   * Send documets to smartcat
   *
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Project $project
   * @return \Drupal\smartcat_translation_manager\DB\Entity\DocumentModel[]
   */
  public function sendDocuments($project) {
    $documents = $this->addDocuments(array_values($this->documents), $project->getExternalProjectId());
    if (empty($documents)) {
      return FALSE;
    }

    foreach ($documents as $scDocument) {
      $matches = [];
      preg_match('/-(\d+)$/', $scDocument->getName(), $matches);
      $this->documentRepository->add(
        (new Document())
          ->setName($scDocument->getName())
          ->setEntityId($matches[1])
          ->setEntityTypeId($project->getEntityTypeId())
          ->setSourceLanguage(LanguageCodeConverter::convertSmartcatToDrupal($scDocument->getSourceLanguage()))
          ->setTargetLanguage(LanguageCodeConverter::convertSmartcatToDrupal($scDocument->getTargetLanguage()))
          ->setStatus($scDocument->getStatus())
          ->setExternalProjectId($project->getExternalProjectId())
          ->setExternalDocumentId($scDocument->getId())
      );
    }
    $this->documents = [];
    return $documents;
  }

  /**
   * @return void
   */
  public function sendProjectWithDocuments() {

    $project = $this->sendProject();
    $documents = $this->sendDocuments($project);

  }

  /**
   * @param EntityInterface $entity
   * @param array $translateTo
   * @return ProjectService
   */
  public function addEntityToTranslete($entity, $translateTo) {
    if (empty($this->project)) {
      $this->createProject($entity, $translateTo);
    }
    else {
      $this->project->setName("{$this->project->getName()}, {$entity->label()}");
    }
    $this->documents[$entity->id()] = $this->createDocument($entity);
    return $this;
  }

  /**
   * @param \Drupal\smartcat_translation_manager\DB\Entity\DocumentModel[] $documents
   * @param string $externalProjectId
   * @return \Drupal\smartcat_translation_manager\DB\Entity\DocumentModel[]
   */
  protected function addDocuments($documents, $externalProjectId) {
    try {
      $documents = $this->api->getProjectManager()->projectAddDocument([
        'documentModel' => $documents,
        'projectId' => $externalProjectId,
      ]);
    }
    catch (\Exception $e) {
      $this->logger->info("{$e->getResponse()->getStatusCode()}, {$e->getMessage()}, {$e->getResponse()->getBody()->getContents()}");
      return [];
    }

    return $documents;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return \Drupal\smartcat_translation_manager\DB\Entity\DocumentModel
   */
  protected function createDocument(EntityInterface $entity) {
    $fieldDefinitions = $this->entityManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
    $translatable = [];

    foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
      if (($fieldDefinition->isComputed() || $this->is_field_translatability_configurable($entity, $fieldName))) {
        array_push($translatable, $fieldName);
      }
    }

    if (empty($translatable)) {
      $translatable = ['title', 'body', 'comment'];
    }

    $file = (new FileHelper($entity))->createFileByEntity($translatable);
    $fileName = ApiHelper::filterChars(\sprintf('%s-%d.html', $entity->label(), $entity->id()));
    return $this->api->project->createDocumentFromFile($file, $fileName);
  }

  /**
   * Check translatability configurable field
   * 
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   */
  protected function is_field_translatability_configurable($entity, $field_name) {
    $entity_type = $this->entityManager->getDefinition($entity->getEntityTypeId());
    $storage_definitions = $this->entityManager->getFieldStorageDefinitions($entity->getEntityTypeId());
    $fields = [$entity_type->getKey('langcode'), $entity_type->getKey('default_langcode'), 'revision_translation_affected'];
    // Allow to configure only fields supporting multilingual storage. We skip our
    // own fields as they are always translatable. Additionally we skip a set of
    // well-known fields implementing entity system business logic.
    return !empty($storage_definitions[$field_name]) &&
            $storage_definitions[$field_name]->isTranslatable() &&
            $storage_definitions[$field_name]->getProvider() != 'content_translation' &&
            !in_array($field_name, $fields);
  }

}
