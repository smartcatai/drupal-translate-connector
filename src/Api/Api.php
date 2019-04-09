<?php

namespace Drupal\smartcat_translation_manager\Api;

use SmartCat\Client\SmartCat;
use Drupal\smartcat_translation_manager\DB\Entity\Project as ProjectEntity;

/**
 * Facade class for work with Smartcat API .
 */
class Api {
  /**
   * @var \SmartCat\Client\SmartCat
   */
  protected $api;

  /**
   * @var Directory
   */
  protected $directory;

  /**
   * @var Project
   */
  public $project;

  /**
   * Init API connection .
   */
  public function __construct() {
    $state = \Drupal::state();

    $login = $state->get('smartcat_api_login');
    $passwd = $state->get('smartcat_api_password');
    $server = $state->get('smartcat_api_server');

    $this->api = new SmartCat($login, $passwd, $server);
    $this->directory = new Directory($this->api);
    $this->project = new Project($this->api);
  }

  /**
   * Method getting account info.
   *
   * @return \Psr\Http\Message\ResponseInterface|\SmartCat\Client\Model\AccountModel
   */
  public function getAccount() {
    return $this->api->getAccountManager()->accountGetAccountInfo();
  }

  /**
   * Method.
   *
   * @return \SmartCat\Client\Model\DirectoryItemModel[]
   */
  public function getLanguages() {
    return $this->directory->get('language')->getItems();
  }

  /**
   * @return array
   */
  public function getServiceTypes() {
    return $this->directory->getItemsAsArray('lspServiceType');
  }

  /**
   * @return array
   */
  public function getVendor() {
    return $this->directory->getItemsAsArray('vendor');
  }

  /**
   * @return array
   */
  public function getProjectStatus() {
    return $this->directory->getItemsAsArray('projectStatus');
  }

  /**
   * @return \SmartCat\Client\Model\DocumentModel
   */
  public function getDocument($externalDocumentId) {
    return $this->api->getDocumentManager()->documentGet(['documentId' => $externalDocumentId]);
  }

  /**
   * @return \SmartCat\Client\Model\ProjectModel
   */
  public function getProject($externalProjectId) {
    return $this->api->getProjectManager()->projectGet($externalProjectId);
  }

  /**
   * @return \SmartCat\Client\Model\ProjectModel
   */
  public function buildStatistic($externalProjectId) {
    $scProject = $this->getProject($externalProjectId);

    $disasemblingSuccess = TRUE;
    foreach ($scProject->getDocuments() as $document) {
      if ($document->getDocumentDisassemblingStatus() != 'success') {
        $disasemblingSuccess = FALSE;
        break;
      }
    }

    if ($disasemblingSuccess) {
      $this->api->getProjectManager()->projectBuildStatistics($scProject->getId());
    }

    return $scProject;
  }

  /**
   * @return \Psr\Http\Message\ResponseInterface|\SmartCat\Client\Model\ExportDocumentTaskModel
   */
  public function requestExportDocuments($documentIds) {
    if (is_scalar($documentIds)) {
      $documentIds = [$documentIds];
    }
    return $this->api
      ->getDocumentExportManager()
      ->documentExportRequestExport(['documentIds' => $documentIds]);
  }

  /**
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function downloadExportDocuments($exportId) {
    return $this->api
      ->getDocumentExportManager()
      ->documentExportDownloadExportResult($exportId);
  }

  /**
   * @return \Psr\Http\Message\ResponseInterface|\SmartCat\Client\Model\ProjectModel
   */
  public function createProject(ProjectEntity $project) {
    $scNewProject = $this->project->createProject($project);
    return $this->api
      ->getProjectManager()
      ->projectCreateProject($scNewProject);
  }

  /**
   * Proxy for API SDK methods.
   *
   * @return mixed
   */
  public function __call($method, $arguments) {
    return $this->api->$method(...$arguments);
  }

}
