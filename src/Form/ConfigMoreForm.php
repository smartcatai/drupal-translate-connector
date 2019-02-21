<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 24.10.2017
 * Time: 16:43
 */

namespace Drupal\smartcat_translation_manager\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Http\Client\Common\Exception\ClientErrorException;
use SmartCat\Client\SmartCat;
use Drupal\smartcat_translation_manager\DB\Entity\Profile;
use Drupal\smartcat_translation_manager\DB\Repository\ProfileRepository;
use Drupal\smartcat_translation_manager\Service\ProjectService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfigMoreForm extends ConfirmFormBase
{
  const DEFAULT_VENDOR = [0=>'Без вендора'];
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The tempstore.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The selection, in the entity_id => langcodes format.
   *
   * @var array
   */
  protected $selection = [];

  /**
   * The entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entity type definition.
   *
   * @var ProjectService
   */
  protected $projectService;

  public function __construct() { //ConfigFactoryInterface $config_factory
    //parent::__construct($config_factory);

    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->tempStore = \Drupal::service('tempstore.private')->get('entity_translate_multiple_confirm');
    $this->projectService = \Drupal::service('smartcat_translation_manager.service.project');
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartcat_config_more_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(
      count($this->selection), 'Are you sure you want to Submit for translation this item?', 'Are you sure you want to Submit for translation these items?', [
      '@item' => $this->entityType->getSingularLabel(),
      '@items' => $this->entityType->getPluralLabel(),
    ]);
  }

  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULl) {
    $form = [];

    $api = new \Drupal\smartcat_translation_manager\Api\Api();
    $account_info = $api->getAccount();

    //сохраняем account_name
    if (!$account_info) {
      \Drupal::messenger()->addMessage(t('Error with connection to Smartcat.',[],['context'=>'smartcat_translation_manager']));
      return parent::buildForm($form, $form_state);
    }

    $form['entity_type_id'] = array(
      '#type' => 'hidden',
      '#value' => $entity_type_id,
    );

    $this->entityTypeId = $entity_type_id;
    $this->entityType = $this->entityTypeManager->getDefinition($this->entityTypeId);
    $this->selection = $this->tempStore->get(\Drupal::service('current_user')->id() . ':' . $this->entityTypeId);

    if (empty($this->entityTypeId) || empty($this->selection)) {
      //var_dump($this->entityTypeId,$this->selection); die;
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

    $items = [];
    $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadMultiple(array_keys($this->selection));
    $sourceLangs = [];
    //var_dump(count($entities));
    foreach ($this->selection as $id => $selected_langcodes) {
      $entity = $entities[$id];
      foreach ($selected_langcodes as $langcode) {
        $sourceLangs[] = $langcode;
        $key = $id . ':' . $langcode;
        if ($entity instanceof TranslatableInterface) {
          $entity = $entity->getTranslation($langcode);
          $default_key = $id . ':' . $entity->getUntranslated()->language()->getId();

          // Build a nested list of translations that will be deleted if the
          // entity has multiple translations.
          $entity_languages = $entity->getTranslationLanguages();
          if (count($entity_languages) > 1) { // && $entity->isDefaultTranslation()) {
            $names = [];
            foreach ($entity_languages as $translation_langcode => $language) {
              $names[] = $language->getName();
              unset($items[$id . ':' . $translation_langcode]);
            }
            $items[$default_key] = [
              'label' => [
                '#markup' => $this->t('@label (Original translation) - <em>The following @entity_type translations will be deleted:</em>',
                  [
                    '@label' => $entity->label(),
                    '@entity_type' => $this->entityType->getSingularLabel(),
                  ]),
              ],
              'deleted_translations' => [
                '#theme' => 'item_list',
                '#items' => $names,
              ],
            ];
          }
          elseif (!isset($items[$default_key])) {
            $items[$key] = $entity->label();
          }
        }
        elseif (!isset($items[$key])) {
          $items[$key] = $entity->label();
        }
      }
    }

    $form['entities'] = [
      '#title' => t('Items for translation', [], ['context' => 'smartcat_translation_manager']),
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    $langs = [];
    foreach(\Drupal::languageManager()->getLanguages() as $language){
      if(in_array($language->getId(),$sourceLangs) ){
        continue;
      }
      if( !in_array($language->getId(),$langs)){
        $langs[$language->getId()] = $language->getName();
      }
    }

    $form['langs'] = [
      '#title' => t('Language', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#default_value' => \Drupal::state()->get('smartcat_api_languages', []),
      '#options' => $langs,
    ];

    $vendors = $api->getVendor();
    $vendors = array_merge(self::DEFAULT_VENDOR, $vendors);
    $form['vendor'] = [
      '#title' => t('Vendor', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'select',
      '#required' => TRUE,
      '#default_value' => \Drupal::state()->get('smartcat_api_vendor', self::DEFAULT_VENDOR),
      '#options' => $vendors,
    ];

    $form['workflow_stages'] = [
      '#title' => t('Workflow stages', [], ['context' => 'translation_connectors']),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#default_value' => \Drupal::state()->get('smartcat_api_workflow_stages', ['Translation']),
      '#options' => [
        'Translation' => t('Translation', [], ['context' => 'translation_connectors']),
        'Editing' => t('Editing', [], ['context' => 'translation_connectors']),
        'Proofreading' => t('Proofreading', [], ['context' => 'translation_connectors']),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    $formValues = $form_state->getValues();

    $state->set('smartcat_api_languages', $formValues['langs']);
    $state->set('smartcat_api_vendor', $formValues['vendor']);
    $state->set('smartcat_api_workflow_stages', $formValues['workflow_stages']);

    $this->entityTypeId = $formValues['entity_type_id'];
    $this->entityType = $this->entityTypeManager->getDefinition($this->entityTypeId);
    $this->selection = $this->tempStore->get(\Drupal::service('current_user')->id() . ':' . $this->entityTypeId);

    $entities = $this->entityTypeManager->getStorage($this->entityTypeId)->loadMultiple(array_keys($this->selection));
    //var_dump(array_values($formValues['langs'])); die;
    foreach ($this->selection as $id => $selected_langcodes) {
      $entity = $entities[$id];
      $this->projectService->addEntityToTranslete($entity, array_filter($formValues['langs'],function($val){return $val !== 0;}));
    }

    $this->projectService->sendProjectWithDocuments();

    $this->tempStore->delete(\Drupal::service('current_user')->id());
    \Drupal::messenger()->addMessage(t('The selected items have been sended to Smartcat.',[],['context'=>'smartcat_translation_manager']));
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    if ($this->entityType->hasLinkTemplate('collection')) {
      return new Url('entity.' . $this->entityTypeId . '.collection');
    }
    else {
      if($prev = \Drupal::request()->query->get('destination',false)){
        return Url::fromUri($prev);
      }
      return new Url('<front>');
    }
  }

  public function getEditableConfigNames(){

  }

}