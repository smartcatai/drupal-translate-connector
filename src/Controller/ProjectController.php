<?php

namespace Drupal\smartcat_translation_manager\Controller;

use Drupal\smartcat_translation_manager\Api\Api;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for work with project.
 */
class ProjectController extends ControllerBase {
  /**
   * @var \Drupal\smartcat_translation_manager\Api\Api
   */
  protected $api;
  /**
   * @var \Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository
   */
  protected $projectRepository;
  protected $tempStore;

  /**
   * Init dependencies.
   */
  public function __construct() {
    $this->api = new Api();
    $this->projectRepository = new ProjectRepository();
    $this->tempStore = \Drupal::service('tempstore.private')->get('entity_translate_multiple_confirm');
  }

  /**
   * Delete translation project.
   *
   * @param int $id
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function delete($id) {
    $this->projectRepository->delete($id);
    return new RedirectResponse(Url::fromRoute('smartcat_translation_manager.project')->toString());
  }

  /**
   * Method add translation project.
   */
  public function add() {
    $entityManager = \Drupal::entityTypeManager();

    $type_id = \Drupal::request()->query->get('type_id');
    $entity_id = \Drupal::request()->query->get('entity_id');
    $lang = \Drupal::request()->query->get('lang');
    $lang = !is_array($lang) ? [$lang] : $lang;

    \Drupal::state()->set('smartcat_api_languages', $lang);

    $entity = $entityManager
      ->getStorage($type_id)
      ->load($entity_id);

    if (!$entity) {
      throw new NotFoundHttpException("Entity $type_id $entity_id not found");
    }

    $selection = [];

    $langcode = $entity->language()->getId();
    $selection[$entity->id()][$langcode] = $langcode;

    $this->tempStore->set(\Drupal::service('current_user')->id() . ':' . $type_id, $selection);
    $previousUrl = \Drupal::request()->server->get('HTTP_REFERER');
    $base_url = Request::createFromGlobals()->getSchemeAndHttpHost();
    $destination = substr($previousUrl, strlen($base_url));

    return new RedirectResponse(
        Url::fromRoute('smartcat_translation_manager.settings_more',
            ['entity_type_id' => $type_id],
            ['query' => ['destination' => $destination]]
        )->toString()
    );
  }

}
