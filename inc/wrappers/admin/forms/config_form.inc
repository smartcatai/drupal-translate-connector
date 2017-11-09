<?php
function translation_connectors_config_form($form, $form_state) {
  return \SmartCAT\Drupal\Drupal\Admin\Forms\Config::get_form($form, $form_state);
}

function translation_connectors_config_form_validate($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\Config::validate_form($form, $form_state);
}

function translation_connectors_config_form_submit($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\Config::submit_form($form, $form_state);
}