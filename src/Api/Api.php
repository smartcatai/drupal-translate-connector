<?php

namespace Smartcat\Drupal\Api;

use SmartCat\Client\SmartCat;
use Smartcat\Drupal\DB\Entity\Project as ProjectEntity;

class Api
{
    /**
     * @var SmartCat
     */
    protected $api;

    /**
     * @var Directory
     */
    protected $directory;

    public $project;

    public function __construct()
    {
        $state = \Drupal::state();

        $login = $state->get('smartcat_api_login');
        $passwd = $state->get('smartcat_api_password');
        $server = $state->get('smartcat_api_server');

        $this->api = new SmartCat($login,$passwd,$server);
        $this->directory = new Directory($this->api);
        $this->project = new Project($this->api);
    }

    public function getAccount()
    {
        return $this->api->getAccountManager()->accountGetAccountInfo();
    }

    public function getLanguages()
    {
        return $this->directory->get('language')->getItems();
    }

    public function getServiceTypes()
    {
        return $this->directory->getItemsAsArray('lspServiceType');
    }

    public function getVendor()
    {
        return $this->directory->getItemsAsArray('vendor');
    }

    public function getProjectStatus()
    {
        return $this->directory->getItemsAsArray('projectStatus');
    }

    public function getProject($externalProjectId)
    {
        return $this->api->getProjectManager()->projectGet($externalProjectId);
    }

    public function buildStatistic($externalProjectId)
    {
        $scProject = $this->getProject($externalProjectId);

        $disasemblingSuccess = true;
        foreach($scProject->getDocuments() as $document){
            if($document->getDocumentDisassemblingStatus() != 'success'){
                $disasemblingSuccess = false;
                break;
            }
        }

        if($disasemblingSuccess){
            $this->api->getProjectManager()->projectBuildStatistics($scProject->getId());
        }

        return $scProject;
    }

    public function requestExportDocuments($documentIds)
    {
        if(is_scalar($documentIds)){
            $documentIds = [$documentIds];
        }
        return $this->api
            ->getDocumentExportManager()
            ->documentExportRequestExport(['documentIds'=>$documentIds]);
    }

    public function downloadExportDocuments($exportId)
    {
        return $this->api
            ->getDocumentExportManager()
            ->documentExportDownloadExportResult($exportId);
    }

    public function createProject(ProjectEntity $project)
    {
        $scNewProject = $this->project->createProject($project);
        return $this->api
            ->getProjectManager()
            ->projectCreateProject($scNewProject);
    }

    public function __call($method, $arguments)
    {
        return $this->api->$method(...$arguments);
    }

}