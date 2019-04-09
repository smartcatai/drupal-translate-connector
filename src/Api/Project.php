<?php

namespace Drupal\smartcat_translation_manager\Api;

use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\ProjectChangesModel;
use Drupal\smartcat_translation_manager\DB\Entity\Project as ProjectEntity;
use Drupal\smartcat_translation_manager\Helper\ApiHelper;
use Drupal\smartcat_translation_manager\Helper\LanguageCodeConverter;

/**
 * Part facade API for work with projects.
 */
class Project extends ApiBaseAbstract {

  /**
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Project $project
   * @return \SmartCat\Client\Model\CreateProjectModel
   */
  public function createProject(ProjectEntity $project) {
    $newScProject = (new CreateProjectModel())
      ->setUseMT(FALSE)
      ->setPretranslate(FALSE)
      ->setAssignToVendor(FALSE);

    $vendorId = \Drupal::state()->get('smartcat_api_vendor', '0');
    if ($vendorId !== '0') {
      $newScProject
        ->setAssignToVendor(TRUE)
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

  /**
   * Undocumented function.
   *
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Project $project
   *
   * @return array
   */
  protected function prepareProjectParams(ProjectEntity $project) {
    return [
      'name' => ApiHelper::filterChars($project->getName()),
      'desc' => 'Content from drupal module',
      'source_lang' => LanguageCodeConverter::convertDrupalToSmartcat($project->getSourceLanguage()),
      'target_langs' => array_map([LanguageCodeConverter::class, 'convertDrupalToSmartcat'], $project->getTargetLanguages()),
      'stages' => array_filter(
            \Drupal::state()->get('smartcat_api_workflow_stages', ['Translation']),
            function ($val) {
              return $val !== 0;
            }
        ),
            'external_tag' => 'source:Drupal',
    ];
  }

  /**
   * @return \SmartCat\Client\Model\BilingualFileImportSettingsModel
   */
  public function getFileImportSettings() {
    return (new BilingualFileImportSettingsModel())
      ->setConfirmMode('none')
      ->setLockMode('none')
      ->setTargetSubstitutionMode('all');
  }

  /**
   * Create and return document property with file model.
   *
   * @param mixed $filePath
   * @param string $fileName
   *
   * @return \SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel
   */
  public function createDocumentFromFile($filePath, $fileName) {
    $documentModel = new CreateDocumentPropertyWithFilesModel();
    $documentModel->setBilingualFileImportSettings($this->getFileImportSettings());
    $documentModel->attachFile($filePath, $fileName);
    return $documentModel;
  }

  /**
   * Create and return project change model where vendor is changed.
   *
   * @param string $vendor
   *
   * @return \SmartCat\Client\Model\ProjectChangesModel
   */
  public function createVendorChange($vendor) {
    $vendorId = strstr($vendor, '|', TRUE);
    return (new ProjectChangesModel())
      ->setVendorAccountIds([$vendorId]);
  }

}
