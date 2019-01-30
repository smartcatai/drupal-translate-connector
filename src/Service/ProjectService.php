<?php

namespace Smartcat\Drupal\Service;

use Drupal\Core\Entity\EntityInterface;
use SmartCat\Client\Model\DocumentModel;
use Smartcat\Drupal\Api\Api;
use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Smartcat\Drupal\Helper\FileHelper;

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

    public function __construct()
    {
        $this->api = new Api();
        $this->projectRepository = new ProjectRepository();
    }

    /**
     * @param EntityInterface $entity
     * @param array $translateTo string[]
     * @return int $project_id
     */
    public function createProject(EntityInterface $entity, $translateTo = NULL)
    {
        $project = (new Project())
            ->setName($entity->label())
            ->setEntityId($entity->id())
            ->setEntityTypeId($entity->getEntityTypeId())
            ->setSourceLanguage($entity->language()->getId())
            ->setTargetLanguages($translateTo)
            ->setStatus(Project::STATUS_NEW);

        $scProject = $this->api->createProject($project);

        $project->setExternalProjectId($scProject->getId());
        $project->setName($scProject->getName());

        $document = $this->createDocument($entity);
        $documents = $this->addDocuments([$document], $scProject->getId());

        return $this->projectRepository->add($project);
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
        $fileName = \sprintf('Tranclate-%s.%s.%d.html', $entity->getEntityTypeId(), $entity->bundle(), $entity->id());

        return $this->api->project->createDocumentFromFile($file, $fileName);
    }
}