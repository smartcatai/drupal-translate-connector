<?php

namespace Drupal\smartcat_translation_manager\Service;

use Drupal\Core\Entity\EntityInterface;
use SmartCat\Client\Model\DocumentModel;
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
     * @var Project
     */
    protected $project;

    /**
     * @var array
     */
    protected $documents = [];

    public function __construct()
    {
        $this->api = new Api();
        $this->documentRepository = new DocumentRepository();
        $this->projectRepository = new ProjectRepository();
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
        $project = $this->project;
        $scProject = $this->api->createProject($this->project);

        $project->setExternalProjectId($scProject->getId());
        $project->setName($scProject->getName());

        $project->setId($this->projectRepository->add($project));
        $this->project = null;
        return $project;
    }

    public function sendDocuments($project){
        $documents = $this->addDocuments(array_values($this->documents),$project->getExternalProjectId());

        foreach($documents as $scDocument){
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
        $documents = $this->api->getProjectManager()->projectAddDocument([
            'documentModel' => $documents,
            'projectId' => $externalProjectId,
        ]);

        return $documents;
    }

    /**
     * @param EntityInterface $entity
     * @return DocumentModel
     */
    protected function createDocument(EntityInterface $entity)
    {
        $file = (new FileHelper($entity))->createFileByEntity(['title','body','comment']);
        $fileName = \sprintf('%s-%d.html', $entity->label(), $entity->id()); //, $entity->label()

        return $this->api->project->createDocumentFromFile($file, $fileName);
    }
}