<?php

namespace Drupal\smartcat_translation_manager\Plugin\Action;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smartcat_translation_manager\Handler\TranslateWithSmartcatHandler;

/**
 * Action description.
 *
 * @Action(
 *   id = "send_to_translate_to_smartcat_action",
 *   label = @Translation("Translate with smartcat"),
 *   type = "node",
 *   confirm_form_route_name = "smartcat_translation_manager.settings_more",
 * )
 */
class SendToTranslateToSmartcatAction extends ActionBase
{
  protected $sections;
  public function __construct(array $configuration, $plugin_id, $plugin_definition)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tempStore = \Drupal::service('tempstore.private')->get('entity_translate_multiple_confirm');
    $this->currentUser = \Drupal::service('current_user');
    $this->projectService = \Drupal::service('smartcat_translation_manager.service.project');
    $this->logger = \Drupal::logger('smartcat_translation_manager_action');
  }

    /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    /** @var \Drupal\Core\Entity\EntityInterface[] $entities */
    $this->tempStore->delete($this->currentUser->id());
    $selection = [];
    foreach ($entities as $entity) {
      $langcode = $entity->language()->getId();
      $selection[$entity->id()][$langcode] = $langcode;
    }
    $this->tempStore->set($this->currentUser->id() . ':' . $this->getPluginDefinition()['type'], $selection);
    // var_dump($this->currentUser->id() . ':' . $this->getPluginDefinition()['type']);
    // die;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  // /**
  //  * {@inheritdoc}
  //  */
  // public function execute($entity = NULL)
  // {
  //   if($entity === NULL){
  //     return;
  //   }

  //   $defaultLang = \Drupal::languageManager()->getDefaultLanguage()->getId();
  //   $sourceLanguage = $entity->language()->getId();
  //   if($defaultLang !== $sourceLanguage){
  //     return;
  //   }

  //   $langs = [];
  //   foreach(\Drupal::languageManager()->getLanguages() as $language){
  //     if($language->getId() !== $sourceLanguage && !in_array($language->getId(),$langs)){
  //       array_push($langs, $language->getId());
  //     }
  //   }

  //   if(empty($langs)){
  //     return $this->t('Not lengs for send to translate');
  //   }

  //   try{
  //     $this->projectService->addEntityToTranslete($entity, $langs);
  //   }catch(\Exception $e){
  //     $this->logger->info($e->getResponse()->getBody()->getContents());
  //   }
    
  //   return $this->t('Entities successful sended');
  // }

  // /**
  //  * {@inheritdoc}
  //  */
  // public function executeMultiple(array $entities) {
  //   parent::executeMultiple($entities);
  //   $this->tempStore->set($this->currentUser->id() . ':' . $this->getPluginDefinition()['type'], $selection);
  //   $this->projectService->sendProjectWithDocuments();
  // }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('translate', $account, TRUE)
        ->andIf($object->status->access('update', $account, TRUE))
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }
}