<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 24.10.2017
 * Time: 16:43
 */

namespace Smartcat\Drupal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Http\Client\Common\Exception\ClientErrorException;
use SmartCat\Client\SmartCat;
use Smartcat\Drupal\DB\Entity\Profile;
use Smartcat\Drupal\DB\Repository\ProfileRepository;

class ProfileForm extends ConfigFormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartcat_profile_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $profileId = \Drupal::request()->query->get('profile_id');
    $profile = (new ProfileRepository())->getOneBy(['id' => $profileId]);
    //var_dump($profile);
    $form = [];

    $form['name'] = [
      '#title' => t('Name', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'textfield',
      '#default_value' => $profile ? $profile->getName() : '',
      '#required' => TRUE,
    ];

    $entityTypes = [];
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $entityDefinitions = $entityTypeManager->getDefinitions();
    // Generate an options list of Entity types.
    foreach( $entityDefinitions as $entityName => $entityDefinition) {
      $entityTypes[$entityName] = $entityDefinition->getLabel();
    }

    $form['entity_type'] = [
      '#title' => t('Entity', [], ['context' => 'smartcat_translation_manager']),
      '#required' => TRUE,
      '#type' => 'select',
      '#default_value' => $profile ? [$profile->getEntityType()] : [],
      '#options' => $entityTypes,
    ];

    $languageManager = \Drupal::service('language_manager');
    $targetLangs = [];
    foreach( $languageManager->getLanguages() as $id => $lang) {
      $targetLangs[$id] = $lang->getName();
    }
    $form['target_langs'] = [
      '#title' => t('Langs', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'checkboxes',
      '#default_value' => $profile ? $profile->getTargetLanguages() : ['en'],
      '#required' => TRUE,
      '#options' => $targetLangs,
    ];

    $api = new \Smartcat\Drupal\Api\Api();

    $defaultVendor = [0=>'Без вендора'];
    $vendors = $api->getVendor();
    $vendors = array_merge($defaultVendor, $vendors);
    $form['vendor'] = [
      '#title' => t('Vendor', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'select',
      '#required' => TRUE,
      '#default_value' => $profile ? [$profile->getVendor()] : $defaultVendor,
      '#options' => $vendors,
    ];

    $form['workflow_stages'] = [
      '#title' => t('Workflow stages', [], ['context' => 'translation_connectors']),
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#default_value' => $profile ? $profile->getWorkflowStages() : ['Translation'],
      '#options' => [
        'Translation' => t('Translation', [], ['context' => 'translation_connectors']),
        'Editing' => t('Editing', [], ['context' => 'translation_connectors']),
        'Proofreading' => t('Proofreading', [], ['context' => 'translation_connectors']),
        'Postediting' => t('Postediting', [], ['context' => 'translation_connectors']),
      ],
    ];

    $form['auto_publish'] = [
      '#title' => t('Publish auto', [], ['context' => 'translation_connectors']),
      '#type' => 'checkbox',
      '#default_value' => $profile ? $profile->getAutoPublish() : false,
    ];

    $form['auto_translate'] = [
      '#title' => t('Translate auto', [], ['context' => 'translation_connectors']),
      '#type' => 'checkbox',
      '#default_value' => $profile ? $profile->getAutoTranslate() : false,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('smartcat_translation_manager.profile');
    $values = $form_state->getValues();

    $profile = (new Profile())
      ->setName($values['name'])
      ->setEntityType($values['entity_type'])
      ->setTargetLanguages($values['target_langs'])
      ->setSourceLanguage(\Drupal::languageManager()->getCurrentLanguage()->getId())
      ->setVendor($values['vendor'])
      ->setWorkflowStages($values['workflow_stages'])
      ->setAutoPublish($values['auto_publish'])
      ->setAutoTranslate($values['auto_translate']);

    $profileId = \Drupal::request()->query->get('profile_id');
    if($profileId){
      $profile->setId($profileId);
      (new ProfileRepository())->update($profile);
    }else{
      $profile_id = (new ProfileRepository())->add($profile);
    }
  }

  public function getEditableConfigNames(){

  }

}