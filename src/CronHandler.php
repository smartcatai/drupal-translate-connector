<?php

namespace Drupal\smartcat_translation_manager;

use Drupal\smartcat_translation_manager\DB\Entity\Document;
use Drupal\smartcat_translation_manager\DB\Entity\Project;
use Drupal\smartcat_translation_manager\DB\Repository\DocumentRepository;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Drupal\smartcat_translation_manager\Helper\FileHelper;

class CronHandler
{
    const KEY_LAST_RUN = 'smartcat_cron.last_run';
    const CRON_PERIOD = 10;

    /**
     * @var \Drupal\smartcat_translation_manager\Api\Api
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

    public static function create()
    {
        $last_run = \Drupal::state()->get(self::KEY_LAST_RUN, 0);

        if ((REQUEST_TIME - $last_run) > self::CRON_PERIOD) {
            \Drupal::state()->set(self::KEY_LAST_RUN, REQUEST_TIME);
            return new static();
        }
        return;
    }

    public function __construct(){
        $this->api = new \Drupal\smartcat_translation_manager\Api\Api();
        $this->projectRepository = new ProjectRepository();
        $this->documentRepository = new DocumentRepository();
        $this->entityTypeManager = \Drupal::entityTypeManager();
        $this->logger = \Drupal::logger('smartcat_translation_manager_cron');
    }

    public function run()
    {
        $this->logger->info('Start cron');
        $this->logger->info('Method buildStatistic start');
        if($this->buildStatistic()){
            $this->logger->info('Method buildStatistic completed');
        }
        $this->logger->info('Method updateStatusFor start with status: '. Project::STATUS_CREATED);
        if($this->updateStatusForProject(Project::STATUS_CREATED)){
            $this->logger->info('Method updateStatusFor completed with status: '. Project::STATUS_CREATED);
        }
        $this->logger->info('Method updateStatusFor start with status: '. Project::STATUS_INPROGRESS);
        if($this->updateStatusForProject(Project::STATUS_INPROGRESS)){
            $this->logger->info('Method updateStatusFor completed with status: '. Project::STATUS_INPROGRESS);
        }else{
            $this->updateStatusForInprogressDocument();
        }
        $this->logger->info('Method updateStatusFor start with status: '. Project::STATUS_COMPLETED);
        if($this->updateStatusForProject(Project::STATUS_COMPLETED)){
            $this->logger->info('Method updateStatusFor completed with status: '. Project::STATUS_COMPLETED);
        }
        $this->logger->info('Method requestDocsForExport start');
        if($this->requestDocsForExport()){
            $this->logger->info('Method requestDocsForExport completed');
        }
        $this->logger->info('Method downloadDocs start');
        if($this->downloadDocs()){
            $this->logger->info('Method downloadDocs completed');
        }
        $this->finishedProject();
        $this->logger->info('End cron');
        return;
    }

    public function buildStatistic()
    {
        $projects = $this->projectRepository->getBy(['status'=>Project::STATUS_NEW],0,100);

        if(!empty($projects)){
            foreach($projects as $i=>$project){
                try{
                    $scProject = $this->api->buildStatistic($project->getExternalProjectId());
                }catch(\Http\Client\Common\Exception\ClientErrorException $e){
                    $this->logger->info($e->getResponse()->getBody()->getContents());
                    $this->logger->info($project->getName());
                    $this->logger->info($project->getStatus());
                    continue;
                }
                $this->changeStatus($project, $scProject);

            }
            return true;
        }
        return false;
    }

    public function updateStatusForProject($status){
        $projects = $this->projectRepository->getBy(['status'=>$status],0,100);
        if(!empty($projects)){
            foreach($projects as $i=>$project){
                try{
                    $scProject = $this->api->getProject($project->getExternalProjectId());
                }catch(\Http\Client\Common\Exception\ClientErrorException $e){
                    $this->logger->info($e->getResponse()->getBody()->getContents());
                    $this->logger->info($project->getName());
                    $this->logger->info($project->getStatus());
                    continue;
                }
                $this->changeStatus($project, $scProject);
                $this->updateStatusForDocument($scProject);
            }
            return true;
        }
        return false;
    }

    protected function updateStatusForDocument($scProject){
        $documents = $this->documentRepository->getBy(['externalProjectId'=>$scProject->getId()],0,100);
        if(!empty($documents)){
            foreach($documents as $i=>$document){
                foreach($scProject->getDocuments() as $scDocument){
                    if($scDocument->getId()!== $document->getExternalDocumentId()){
                        continue;
                    }

                    $this->changeStatus($document, $scDocument, $this->documentRepository);
                }
            }
        }
    }

    public function updateStatusForInprogressDocument()
    {
        $documents = $this->documentRepository->getBy([
            'status'=>Document::STATUS_INPROGRESS,
        ],0,100);

        if(!empty($documents)){
            foreach($documents as $i=>$document){
                $scDocument = $this->api->getDocument($document->getExternalDocumentId());
                $this->changeStatus($document, $scDocument, $this->documentRepository);
            }
        }
    }

    public function requestDocsForExport()
    {
        $documents = $this->documentRepository->getBy([
            'status'=>Document::STATUS_COMPLETED,
            'externalExportId'=>[null,'IS NULL'],
        ],0,100);
        $tempProjects = [];
        if(!empty($documents)){
            foreach($documents as $i=>$document){
                if(!array_key_exists($document->getExternalProjectId(),$tempProjects)){
                    try{
                        $tempProjects[$document->getExternalProjectId()] = $this->api->getProject($document->getExternalProjectId());
                    }catch(\Http\Client\Common\Exception\ClientErrorException $e){
                        $this->logger->info($e->getResponse()->getBody()->getContents());
                        $this->logger->info($document->getName());
                        $this->logger->info($document->getStatus());
                        continue;
                    }
                }
                $scProject = $tempProjects[$document->getExternalProjectId()];

                $documentIds = [];
                foreach($scProject->getDocuments() as $scDocument){
                    if($scDocument->getId() !== $document->getExternalDocumentId()){
                        continue;
                    }
                    $documentIds[] = $scDocument->getId();
                }
                try{
                    $export = $this->api->requestExportDocuments($documentIds);
                }catch(\Http\Client\Common\Exception\ClientErrorException $e){
                    $this->logger->info($e->getResponse()->getBody()->getContents());
                    $this->logger->info($document->getName());
                    $this->logger->info($document->getStatus());
                    continue;
                }
                $document->setExternalExportId($export->getId());
                $this->documentRepository->update($document);
            }
            return true;
        }
        return false;
    }

    public function downloadDocs()
    {
        $documents = $this->documentRepository->getBy([
            'status'=>Document::STATUS_COMPLETED,
        ],0,100);

        if(empty($documents)){
            return false;
        }
        foreach($documents as $document){
            if(empty($document->getExternalExportId())){
                continue;
            }
            try{
                $response = $this->api->downloadExportDocuments($document->getExternalExportId());
            }catch(\Http\Client\Common\Exception\ClientErrorException $e){
                $document->setStatus(Document::STATUS_FAILED);
                $this->documentRepository->update($document);
                $this->logger->info($e->getResponse()->getBody()->getContents());
                $this->logger->info($document->getName());
                $this->logger->info($document->getStatus());
                continue;
            }

            $mimeType = $response->getHeaderLine('Content-Type');
            if($response->getStatusCode() === 204){
                $this->logger->info($response->getStatusCode() .'|>'. $response->getBody()->getContents());
                continue;
            }
            if($mimeType==='text/html'){
                $sourceEntity = $this->entityTypeManager
                    ->getStorage($document->getEntityTypeId())
                    ->load($document->getEntityId());

                if(!$sourceEntity){
                    $this->logger->info('Entity not exist');
                    continue;
                }
                
                try{
                    $targetEntity = (new FileHelper($sourceEntity))
                        ->markupToEntityTranslation($response->getBody()->getContents(),$document->getTargetLanguage());
                }catch(\Exception $e){
                    $document->setStatus(Document::STATUS_FAILED);
                    $this->documentRepository->update($document);
                    $this->logger->info('Parse file error: '. $e->getMessage());
                    continue;
                }
                $targetEntity->save();
                $document->setExternalExportId(NULL);
                $document->setStatus(Document::STATUS_DOWNLOADED);
                $this->documentRepository->update($document);
            }
        }
        return true;
    }

    public function finishedProject(){
        $projects = $this->projectRepository->getBy(['status'=>Project::STATUS_COMPLETED],0,100);
        if(empty($projects)){
            return false;
        }
        foreach($projects as $i=>$project){
            $documents = $this->documentRepository->getBy([
                'externalProjectId'=> $project->getExternalProjectId(),
            ],0,100);

            $continue = false;
            foreach($documents as $document){
                if($document->getStatus()=== Document::STATUS_INPROGRESS ||$document->getStatus()=== Document::STATUS_CREATED ){
                    $continue = true;
                    break;
                }
            }

            if($continue){
                continue;
            }

            $project->setStatus(Project::STATUS_FINISHED);
            $this->projectRepository->update($project);
        }
    }

    protected function changeStatus($project, $scProject, $repo = null)
    {
        $repo = $repo ?? $this->projectRepository;
        if($project->getStatus()!==$scProject->getStatus()){
            $project->setStatus(strtolower($scProject->getStatus()));
            return $repo->update($project);
        }
        return false;
    }
}