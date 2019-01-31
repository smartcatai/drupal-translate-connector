<?php

namespace Smartcat\Drupal;

use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Smartcat\Drupal\Helper\FileHelper;

class CronHandler
{
    const KEY_LAST_RUN = 'smartcat_cron.last_run';
    const CRON_PERIOD = 10;

    /**
     * @var \Smartcat\Drupal\Api\Api
     */
    protected $api;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

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
        $this->api = new \Smartcat\Drupal\Api\Api();
        $this->projectRepository = new ProjectRepository();
        $this->entityTypeManager = \Drupal::entityTypeManager();
        $this->logger = \Drupal::logger('smartcat_translation_manager_cron');
    }

    public function run()
    {
        if($this->buildStatistic()){
            $this->logger->info('Method buildStatistic completed');
            return;
        }
        if($this->updateStatusFor(Project::STATUS_CREATED)){
            $this->logger->info('Method updateStatusFor completed with status: '. Project::STATUS_CREATED);
            return;
        }
        if($this->updateStatusFor(Project::STATUS_INPROGRESS)){
            $this->logger->info('Method updateStatusFor completed with status: '. Project::STATUS_INPROGRESS);
            return;
        }
        if($this->requestDocsForExport()){
            $this->logger->info('Method requestDocsForExport completed');
            return;
        }
        if($this->downloadDocs()){
            $this->logger->info('Method downloadDocs completed');
        }
        return;
    }

    public function buildStatistic()
    {
        $projects = $this->projectRepository->getBy(['status'=>Project::STATUS_NEW]);
        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $scProject = $this->api->buildStatistic($project->getExternalProjectId());
                $this->changeStatus($project, $scProject);
            }
            return true;
        }
        return false;
    }

    public function updateStatusFor($status){
        $projects = $this->projectRepository->getBy(['status'=>$status]);
        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $scProject = $this->api->getProject($project->getExternalProjectId());
                $this->changeStatus($project, $scProject);
            }
            return true;
        }
        return false;
    }

    public function requestDocsForExport()
    {
        $projects = $this->projectRepository->getBy([
            'status'=>Project::STATUS_COMPLETED,
        ]);
        if(!empty($projects)){
            foreach($projects as $i=>$project){
                $scProject = $this->api->getProject($project->getExternalProjectId());
                $documentIds = [];
                foreach($scProject->getDocuments() as $document){
                    $documentIds[] = $document->getId();
                }
                $export = $this->api->requestExportDocuments($documentIds);
                $project->setExternalExportId($export->getId());
                $project->setStatus(Project::STATUS_DOWNLOAD);
                $this->projectRepository->update($project);
            }
            return true;
        }
        return false;
    }

    public function downloadDocs()
    {
        $projects = $this->projectRepository->getBy([
            'status'=>Project::STATUS_DOWNLOAD,
        ]);

        if(empty($projects)){
            return false;
        }
        foreach($projects as $project){
            try{
                $response = $this->api->downloadExportDocuments($project->getExternalExportId());
            }catch(\Http\Client\Common\Exception\ClientErrorException $e){
                $project->setStatus(Project::STATUS_COMPLETED);
                $project->setExternalExportId(NULL);
                $this->projectRepository->update($project);
                $this->logger->info($e->getResponse()->getBody()->getContents());
                continue;
            }
            $mimeType = $response->getHeaderLine('Content-Type');
            if($response->getStatusCode() === 204){
                $this->logger->info($response->getStatusCode());
                continue;
            }
            if($mimeType==='text/html'){
                $sourceEntity = $this->entityTypeManager
                    ->getStorage($project->getEntityTypeId())
                    ->load($project->getEntityId());

                if(!$sourceEntity){
                    $this->logger->info('Entity not exist');
                    continue;
                }
                
                $targetEntity = (new FileHelper($sourceEntity))
                    ->markupToEntityTranslation($response->getBody()->getContents(),$project->getTargetLanguages()[0]);
                $targetEntity->save();

                $project->setStatus(Project::STATUS_FINISHED);
                $project->setExternalExportId(NULL);
                $this->projectRepository->update($project);
            }
        }
        return true;
    }

    protected function changeStatus($project, $scProject)
    {
        if($project->getStatus()!==$scProject->getStatus()){
            $project->setStatus(strtolower($scProject->getStatus()));
            return $this->projectRepository->update($project);
        }
        return false;
    }
}