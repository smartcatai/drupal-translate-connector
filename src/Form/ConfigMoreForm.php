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

class ConfigMoreForm extends ConfigFormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartcat_config_more_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $api = new \Smartcat\Drupal\Api\Api();
    $account_info = $api->getAccount();

    //сохраняем account_name
    if (!$account_info) {
      \Drupal::messenger()->addMessage(t('The configuration options have been saved.',[],['context'=>'smartcat_translation_manager']));
      return parent::buildForm($form, $form_state);
    }

    $defaultVendor = [0=>'Без вендора'];
    $vendors = $api->getVendor();
    $vendors = array_merge($defaultVendor, $vendors);
    $form['vendor'] = [
      '#title' => t('Vendor', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'select',
      '#required' => TRUE,
      '#default_value' => \Drupal::state()->get('smartcat_api_vendor', $defaultVendor),
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
        'Postediting' => t('Postediting', [], ['context' => 'translation_connectors']),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    $formValues = $form_state->getValues();

    $state->set('smartcat_api_vendor', $formValues['vendor']);//1e80d715-db82-43e8-b134-f54c2b64de28
    $state->set('smartcat_api_workflow_stages', $formValues['workflow_stages']);//2_DDlOx2P8UejJzs2Xw60KA636s

    \Drupal::messenger()->addMessage(t('The configuration options have been saved.',[],['context'=>'smartcat_translation_manager']));
    return TRUE;
  }

  public function getEditableConfigNames(){

  }

}