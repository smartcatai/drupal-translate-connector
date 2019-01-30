<?php

namespace Smartcat\Drupal\Api;

use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\CreateProjectWithFilesModel;
use SmartCat\Client\Model\ProjectChangesModel;
use SmartCat\Client\SmartCat;
use Smartcat\Drupal\DB\Entity\Project as ProjectEntity;
use Smartcat\Drupal\Helper\ApiHelper;

class Project extends ApiBaseAbstract
{
    public function createProject(ProjectEntity $project)
    {
        $newScProject = (new CreateProjectModel())
            ->setUseMT(false)
            ->setPretranslate(false)
            ->setAssignToVendor(false);

        $vendorId = \Drupal::state()->get('smartcat_api_vendor', '0');
        if($vendorId !=='0'){
            $newScProject
                ->setAssignToVendor(true)
                ->setVendorAccountIds($vendorId);
        }

        $params = $this->prepareProjectParams($project);

        return $newScProject
            ->setName($params['name'])
            ->setDescription($params['desc'])
            ->setDeadline($params['deadline'])
            ->setSourceLanguage($params['source_lang'])
            ->setTargetLanguages($params['target_langs'])
            ->setWorkflowStages($params['stages'])
            ->setExternalTag($params['external_tag'])
            ->setIsForTesting($params['test']);
    }

    protected function prepareProjectParams(ProjectEntity $project)
    {
        return Array(
            'name' => ApiHelper::filterChars($project->getName()),
            'desc' => 'Content from drupal module',
            'source_lang' => $project->getSourceLanguage(),
            'target_langs' => $project->getTargetLanguages(),
            'stages' => explode(',', \Drupal::state()->get('smartcat_api_workflow_stages', ['Translation'])),
            'test' => false,
            'deadline' => (new \DateTime('now'))->modify(' +1 day'), 
            'external_tag' => 'source:Drupal',
        );
    }

    public function getFileImportSettings()
    {
        return (new BilingualFileImportSettingsModel())
            ->setConfirmMode('none')
            ->setLockMode('none')
            ->setTargetSubstitutionMode('all');
    }

    public function createDocumentFromFile($filePath, $fileName)
    {
        $documentModel = new CreateDocumentPropertyWithFilesModel();
        $documentModel->setBilingualFileImportSettings($this->getFileImportSettings());
        $documentModel->attachFile($filePath, $fileName);
        return $documentModel;
    }

    public function createVendorChange($vendor)
    {
        $vendorId = strstr($vendor, '|', true);
        return (new ProjectChangesModel())
            ->setVendorAccountIds([$vendorId]);
    }
}