<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 24.10.2017
 * Time: 16:43
 */

namespace Drupal\smartcat_translation_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Http\Client\Common\Exception\ClientErrorException;
use SmartCat\Client\SmartCat;

class ConfigForm extends ConfigFormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartcat_config_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $form['api_server'] = [
      '#title' => t('Server', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'select',
      '#options' => [
        SmartCat::SC_EUROPE => t('Europe', [], ['context' => 'smartcat_translation_manager']),
        SmartCat::SC_USA => t('USA', [], ['context' => 'smartcat_translation_manager']),
        SmartCat::SC_ASIA => t('Asia', [], ['context' => 'smartcat_translation_manager']),
      ],
    ];

    $form['api_login'] = [
      '#title' => t('Smartcat account ID', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'textfield',
      '#default_value' => \Drupal::state()->get('smartcat_api_login', ''),
      '#required' => TRUE,
    ];

    $lenpass = strlen(\Drupal::state()->get('smartcat_api_password', ''));
    $form['api_password'] = [
      '#title' => t('API key', [], ['context' => 'smartcat_translation_manager']),
      '#type' => 'password',
      '#placeholder' => $lenpass > 0 ? str_repeat('•', $lenpass) : '',
      '#required' => TRUE,
    ];

    $accountName = \Drupal::state()->get('smartcat_account_name', '');
    if(!empty($accountName)){
      $form['info'] = [
        '#title'=>"You connected to account: $accountName",
        '#type' => 'item'];
    }

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $login = $form_state->getValues()['api_login'];
    $password = $form_state->getValues()['api_password'];
    $server = $form_state->getValues()['api_server'];
    try {
      $api = new SmartCat($login, $password, $server);
      $api->getAccountManager()->accountGetAccountInfo();
    } catch (\Exception $e) {
      \Drupal::messenger()->addError(t('Invalid Smartcat account ID or API key',[],['context'=>'smartcat_translation_manager']));
      $form_state->setError($form['api_login']);
      $form_state->setError($form['api_password']);
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $state = \Drupal::state();
    $formValues = $form_state->getValues();

    $state->set('smartcat_api_login', $formValues['api_login']);//1e80d715-db82-43e8-b134-f54c2b64de28
    $state->set('smartcat_api_password', $formValues['api_password']);//2_DDlOx2P8UejJzs2Xw60KA636s
    $state->set('smartcat_api_server', $formValues['api_server']);

    $api = new SmartCat($formValues['api_login'], $formValues['api_password'], $formValues['api_server']);
    try{
      $account_info = $api->getAccountManager()->accountGetAccountInfo();
    }catch(\Exception $e){}

    //сохраняем account_name
    if ($account_info && $account_info->getName()) {
      $state->set('smartcat_account_name', $account_info->getName());
    }
    \Drupal::messenger()->addMessage(t('The configuration options have been saved.',[],['context'=>'smartcat_translation_manager']));
    return TRUE;
  }

  public function getEditableConfigNames(){

  }

}