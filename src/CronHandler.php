<?php

namespace Smartcat\Drupal;

use Smartcat\Drupal\DB\Entity\Project;
use Smartcat\Drupal\DB\Repository\ProjectRepository;
use Smartcat\Drupal\Helper\FileHelper;

class CronHandler
{
    const KEY_LAST_RUN = 'smartcat_cron.last_run';
    const CRON_PERIOD = 30;

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
    }

    public function run()
    {
        if($this->buildStatistic()){
            return;
        }
        if($this->updateStatusFor(Project::STATUS_CREATED)){
            return;
        }
        if($this->updateStatusFor(Project::STATUS_INPROGRESS)){
            return;
        }
        if($this->requestDocsForExport()){
            return;
        }
        $this->downloadDocs();
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
            //var_dump($status,count($projects));
            foreach($projects as $i=>$project){
                $scProject = $this->api->getProject($project->getExternalProjectId());
                $this->changeStatus($project, $scProject);
            }
            //die;
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
            //'externalExportId'=>[NULL, 'IS NOT NULL'],
        ]);
        //var_dump($projects); die;
        if(empty($projects)){
            return false;
        }
        foreach($projects as $project){
            try{
                $response = $this->api->downloadExportDocuments($project->getExternalExportId());
            }catch(\Exception $e){
                $project->setStatus(Project::STATUS_COMPLETED);
                $this->projectRepository->update($project);
                continue;
            }
            $mimeType = $response->getHeaderLine('Content-Type');
            if($response->getStatusCode() === 204){
                continue;
            }
            if($mimeType==='text/html'){
                $sourceEntity = $this->entityTypeManager
                    ->getStorage($project->getEntityTypeId())
                    ->load($project->getEntityId());
                
                $targetEntity = (new FileHelper($sourceEntity))
                    ->markupToEntityTranslation($response->getBody()->getContents(),$project->getTargetLanguages()[0]);
            }
        }
        die;
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