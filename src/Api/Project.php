<?php

namespace Drupal\smartcat_translation_manager\Api;

use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\ProjectChangesModel;
use Drupal\smartcat_translation_manager\DB\Entity\Project as ProjectEntity;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Drupal\smartcat_translation_manager\Helper\LanguageCodeConverter;

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
                ->setVendorAccountIds([$vendorId]);
        }

        $params = $this->prepareProjectParams($project);

        return $newScProject
            ->setName($params['name'])
            ->setDescription($params['desc'])
            ->setSourceLanguage($params['source_lang'])
            ->setTargetLanguages($params['target_langs'])
            ->setWorkflowStages($params['stages'])
            ->setExternalTag($params['external_tag']);
    }

    protected function prepareProjectParams(ProjectEntity $project)
    {
        return [
            'name' => ApiHelper::filterChars($project->getName()),
            'desc' => 'Content from drupal module',
            'source_lang' => LanguageCodeConverter::convertDrupalToSmartcat($project->getSourceLanguage()),
            'target_langs' => array_map([LanguageCodeConverter::class,'convertDrupalToSmartcat'],$project->getTargetLanguages()),
            'stages' => array_filter(
                \Drupal::state()->get('smartcat_api_workflow_stages', ['Translation']),
                function($val){return $val !== 0;}
            ),
            'external_tag' => 'source:Drupal',
        ];
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