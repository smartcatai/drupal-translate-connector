<?php

use Http\Client\Common\Exception\ClientErrorException;
use SmartCAT\Drupal\Drupal\Notice;
use SmartCAT\Drupal\Drupal\Options;
use SmartCAT\Drupal\Helpers\SmartCAT;

function translation_connectors_config_form() {
  $container = \SmartCAT\Drupal\Connector::get_container();
  /* @var Options $options */
  $options = $container->get('core.options');
  $form = [];
  $form['api_server'] = [
    '#title' => t('API server'),
    '#type' => 'select',
    '#options' => [
      SmartCAT::SC_EUROPE => t('Europe', [], ['context' => 'translation_connectors']),
      SmartCAT::SC_USA => t('USA', [], ['context' => 'translation_connectors']),
      SmartCAT::SC_ASIA => t('Asia', [], ['context' => 'translation_connectors']),
    ],
  ];

  $form['api_login'] = [
    '#title' => t('API login', [], ['context' => 'translation_connectors']),
    '#type' => 'textfield',
    '#default_value' => $options->get_and_decrypt('smartcat_api_login'),
    '#required' => TRUE,
  ];

  $form['api_password'] = [
    '#title' => t('API Password', [], ['context' => 'translation_connectors']),
    '#type' => 'password',
    '#required' => TRUE,
  ];

  $form['actions'] = ['#type' => 'actions'];
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Add'),
  ];

  $form['#submit'][] = 'translation_connectors_config_form_submit';
  $form['#validate'][] = 'translation_connectors_config_form_validate';

  return confirm_form($form, t('Required settings', [], ['context' => 'translation_connectors']), 'admin/config/regional/translation_connectors', '', t('Save'), t('Cancel'));
}

function translation_connectors_config_form_validate($form, &$form_state) {
  $login = $form_state['values']['api_login'];
  $password = $form_state['values']['api_password'];
  $server = $form_state['values']['api_server'];
  try {
    $api = new \SmartCAT\API\SmartCAT($login, $password, $server);
    $account_info = $api->getAccountManager()->accountGetAccountInfo();
    $is_ok = (bool) $account_info->getId();
    if (!$is_ok) {
      throw new Exception('Invalid username or password');
    }
  } catch (\Exception $e) {
    form_set_error('api_login', t('Invalid username or password', [], ['context' => 'translation_connectors']));
    form_set_error('api_password');
  }
}

function translation_connectors_config_form_submit($form, &$form_state) {
  $container = \SmartCAT\Drupal\Connector::get_container();
  /* @var Options $options */
  $options = $container->get('core.options');
  /* @var Notice $notice */
  $notice = $container->get('core.notice');
  /* @var SmartCAT $sc */
  $sc = $container->get('smartcat');
  $alreadyActivated = SmartCAT::is_active();
  if ($alreadyActivated) {
    $options->set('smartcat_vendor_id', NULL);
    try {
      $sc->getCallbackManager()->callbackDelete();
    } catch (\Exception $e) {
      $data['message'] = $e->getMessage();

      if ($e instanceof ClientErrorException) {
        $message = "API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}";
      }
      else {
        $message = "Message: {$e->getMessage()}. Trace: {$e->getTraceAsString()}";
      }
      watchdog("translation_connectors", "Callback delete failed, user {$options->get('smartcat_api_login')}. $message", WATCHDOG_ERROR);
      $notice->add_error(t('Problem with deleting of previous callback', [], ['context' => 'translation_connectors']));
      return FALSE;
    }
  }

  $options->set_and_encrypt('smartcat_api_login', $form_state['values']['api_login']);//1e80d715-db82-43e8-b134-f54c2b64de28
  $options->set_and_encrypt('smartcat_api_password', $form_state['values']['api_password']);//2_DDlOx2P8UejJzs2Xw60KA636s
  $options->set('smartcat_api_server', $form_state['values']['api_server']);

  try {
    //TODO: Зарегестрировать callback
    //    $callback_handler = $container->get( 'callback.handler.smartcat' );
    //    $callback_handler->register_callback();
  } catch (\Exception $e) {
    $data['message'] = $e->getMessage();

    if ($e instanceof ClientErrorException) {
      $message = "API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}";
    }
    else {
      $message = "Message: {$e->getMessage()}. Trace: {$e->getTraceAsString()}";
    }

    watchdog("translation_connectors", "Callback delete failed, user {$form_state['values']['api_login']}. $message", WATCHDOG_ERROR);

    $notice->add_error(t('Problem with setting of new callback', [], ['context' => 'translation_connectors']));
    return FALSE;
  }

  /* @var SmartCAT $sc */
  $sc = $container->get('smartcat');

  $account_info = $sc->getAccountManager()->accountGetAccountInfo();
  //сохраняем account_name
  if ($account_info && $account_info->getName()) {
    /** @var Options $options */
    $options = $container->get('core.options');
    $options->set('smartcat_account_name', $account_info->getName());
  }
  $notice->add_success(t('The configuration options have been saved.'));
  if (!$alreadyActivated) {
    drupal_flush_all_caches();
  }
  return TRUE;
}