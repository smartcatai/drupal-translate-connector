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

class ProjectService
{
     /**
     * @var Api
     */
    protected $api;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @var DocumentRepository
     */
    protected $documentRepository;

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var array
     */
    protected $documents = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->api = new Api();
        $this->documentRepository = new DocumentRepository();
        $this->projectRepository = new ProjectRepository();
        $this->logger = \Drupal::logger('smartcat_translation_manager_project');
    }

    /**
     * @param EntityInterface $entity
     * @param array $translateTo string[]
     * @return int $project_id
     */
    public function createProject(EntityInterface $entity, $translateTo = NULL)
    {
        $this->project = (new Project())
            ->setName($entity->label())
            ->setEntityTypeId($entity->getEntityTypeId())
            ->setSourceLanguage($entity->language()->getId())
            ->setTargetLanguages($translateTo)
            ->setStatus(Project::STATUS_NEW);
    }

    public function sendProject(){
        if(empty($this->project)){
            return null;
        }
        $project = $this->project;
        $scProject = $this->api->createProject($project);

        $project->setExternalProjectId($scProject->getId());
        $project->setName($scProject->getName());

        $project->setId($this->projectRepository->add($project));
        $this->project = null;
        return $project;
    }

    public function sendDocuments($project){
        $documents = $this->addDocuments(array_values($this->documents),$project->getExternalProjectId());
        if(empty($documents)){
            return false;
        }

        foreach($documents as $scDocument){
            $matches = [];
            preg_match('/-(\d+)$/',$scDocument->getName(), $matches );
            $this->documentRepository->add( 
                (new Document())
                    ->setName($scDocument->getName())
                    ->setEntityId($matches[1])
                    ->setEntityTypeId($project->getEntityTypeId())
                    ->setSourceLanguage($scDocument->getSourceLanguage())
                    ->setTargetLanguage($scDocument->getTargetLanguage())
                    ->setStatus($scDocument->getStatus())
                    ->setExternalProjectId($project->getExternalProjectId())
                    ->setExternalDocumentId($scDocument->getId())
            );
        }
        $this->documents = [];
        return $documents;
    }

    public function sendProjectWithDocuments(){

        $project = $this->sendProject();

        $documents = $this->sendDocuments($project);

    }

    public function addEntityToTranslete($entity, $translateTo){
        if(empty($this->project)){
            $this->createProject($entity, $translateTo);
        }else{
            $this->project->setName("{$this->project->getName()}, {$entity->label()}");
        }
        $this->documents[$entity->id()] = $this->createDocument($entity);
        return $this;
    }

    /**
     * @param DocumentModel[] $documents
     * @param string $externalProjectId
     * @return DocumentModel[]
     */
    protected function addDocuments($documents,$externalProjectId)
    {
        try{
            $documents = $this->api->getProjectManager()->projectAddDocument([
                'documentModel' => $documents,
                'projectId' => $externalProjectId,
            ]);
        }catch(\Exception $e){
            $this->logger->info("{$e->getResponse()->getStatusCode()}, {$e->getMessage()}, {$e->getResponse()->getBody()->getContents()}");
            return [];
        }

        return $documents;
    }

    /**
     * @param EntityInterface $entity
     * @return DocumentModel
     */
    protected function createDocument(EntityInterface $entity)
    {
        $fieldDefinitions = $this->entityManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
        $translatable = [];

        foreach($fieldDefinitions as $fieldName => $fieldDefinition){
            if( ($fieldDefinition->isComputed() || $this->is_field_translatability_configurable($entity, $fieldName))){
                array_push($translatable, $fieldName);
            }
        }

        if(empty($translatable)){
            $translatable = ['title','body','comment'];
        }

        $file = (new FileHelper($entity))->createFileByEntity($translatable);
        $fileName = FileHelper::sanitizeFileName(\sprintf('%s-%d.html', $entity->label(), $entity->id()));
        return $this->api->project->createDocumentFromFile($file, $fileName);
    }

    protected function is_field_translatability_configurable( $entity, $field_name) {
        $entity_type = $this->entityManager->getDefinition($entity->getEntityTypeId());
        $storage_definitions = $this->entityManager->getFieldStorageDefinitions($entity->getEntityTypeId()) ;
        $fields = [$entity_type->getKey('langcode'), $entity_type->getKey('default_langcode'), 'revision_translation_affected'];
        // Allow to configure only fields supporting multilingual storage. We skip our
        // own fields as they are always translatable. Additionally we skip a set of
        // well-known fields implementing entity system business logic.
        return
            !empty($storage_definitions[$field_name]) &&
            $storage_definitions[$field_name]->isTranslatable() &&
            $storage_definitions[$field_name]->getProvider() != 'content_translation' &&
            !in_array($field_name, $fields);
    }

}