<?php

use SmartCAT\Drupal\Drupal\Options;
use SmartCAT\Drupal\Drupal\Notice;
use SmartCAT\Drupal\Helpers\SmartCAT;

function translation_connectors_additional_form() {
  $container = \SmartCAT\Drupal\Connector::get_container();
  /* @var Options $options */
  $options = $container->get('core.options');
  $form = [];
  $form['workflow_stages'] = [
    '#title' => t('Workflow stages', [], ['context' => 'translation_connectors']),
    '#type' => 'checkboxes',
    '#required' => TRUE,
    '#default_value' => $options->get('smartcat_workflow_stages') ?: ['Translation'],
    '#options' => [
      'Translation' => t('Translation', [], ['context' => 'translation_connectors']),
      'Editing' => t('Editing', [], ['context' => 'translation_connectors']),
      'Proofreading' => t('Proofreading', [], ['context' => 'translation_connectors']),
      'Postediting' => t('Postediting', [], ['context' => 'translation_connectors']),
    ],
  ];

  $select_array = [];
  /* @var SmartCAT $sc */
  $sc = $container->get('smartcat');
  $vendors = $sc->getDirectoriesManager()->directoriesGet(['type' => 'vendor']);
  $items = $vendors->getItems();

  foreach ($items as $item) {
    $select_array[$item->getId()] = $item->getName();
  }

  if (count($select_array)) {
    array_unshift($select_array, t('Please, choose your vendor', [], ['context' => 'translation_connectors']));
  }

  $form['vendor_id'] = [
    '#title' => t('Vendor ID'),
    '#type' => 'select',
    '#options' => $select_array,
    '#default_value' => $options->get('smartcat_vendor_id'),
  ];

  $form['#submit'][] = 'translation_connectors_additional_form_submit';

  return confirm_form($form, t('Additional settings', [], ['context' => 'translation_connectors']), 'admin/config/regional/translation_connectors/additional', '', t('Save'), t('Cancel'));
}

function translation_connectors_additional_form_submit($form, &$form_state) {
  $container = \SmartCAT\Drupal\Connector::get_container();
  /* @var Options $options */
  $options = $container->get('core.options');
  /* @var Notice $notice */
  $notice = $container->get('core.notice');
  $stages = [];
  foreach ($form_state['values']['workflow_stages'] as $key => $value) {
    if ($value) {
      $stages[] = $key;
    }
  }
  $options->set('smartcat_workflow_stages', $stages);

  if($form_state['values']['vendor_id']) {
    $options->set('smartcat_vendor_id', $form_state['values']['vendor_id']);
  }

  $notice->add_success(t('The configuration options have been saved.'));
  return TRUE;
}