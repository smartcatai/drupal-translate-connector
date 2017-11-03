<?php
function translation_connectors_send_to_translate_form($form, &$form_state, $entity_type, $entity) {
  return \SmartCAT\Drupal\Drupal\Admin\Forms\SendToTranslate::get_form($form, $form_state, $entity_type, $entity);
}

function translation_connectors_send_to_translate_form_validate($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\SendToTranslate::validate_form($form, $form_state);
}

function translation_connectors_send_to_translate_form_submit($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\SendToTranslate::submit_form($form, $form_state);
}

function translation_connectors_send_to_translate_form_ajax_callback($form, &$form_state) {
  return $form;
}