<?php

namespace Drupal\smartcat_translation_manager\Helper;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 *
 */
class ApiHelper {

  /**
   * Filter special chars
   */
  public static function filterChars($s) {
    return mb_substr(str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], '_', $s), 0, 94);
  }

  /**
   * Generate project name a link
   *
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Project $project
   * @return Link
   */
  public static function getProjectName($project) {
    $name = $project->getName();
    if ($project->getExternalProjectId()) {
      $projectUrl = self::getProjectUrl($project);
      $name = Link::fromTextAndUrl($project->getName(), $projectUrl)->toString();
    }
    return $name;
  }

  /**
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Project $project
   * @return Url
   */
  public static function getProjectUrl($project) {
    $state = \Drupal::state();
    return Url::fromUri("https://{$state->get('smartcat_api_server')}/projects/{$project->getExternalProjectId()}");
  }

  /**
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Document $document
   * @return Url
   */
  public static function getProjectUrlBydocument($document) {
    $state = \Drupal::state();
    return Url::fromUri("https://{$state->get('smartcat_api_server')}/projects/{$document->getExternalProjectId()}");
  }

  /**
   * @param \Drupal\smartcat_translation_manager\DB\Entity\Document $document
   * @return Url
   */
  public static function getDocumentUrl($document) {
    return Url::fromUri(self::getDocumentLink($document->getExternalDocumentId()));
  }

  /**
   * @param string $document_id
   * @return string
   */
  public static function getDocumentLink($document_id) {
    $ids = explode('_', $document_id);
    $state = \Drupal::state();
    return "https://{$state->get('smartcat_api_server')}/editor?DocumentId={$ids[0]}&LanguageId={$ids[1]}";
  }

}
