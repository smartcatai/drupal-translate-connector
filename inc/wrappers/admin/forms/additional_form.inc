<?php

function translation_connectors_additional_form() {
  return \SmartCAT\Drupal\Drupal\Admin\Forms\Additional::get_form();
}

function translation_connectors_additional_form_submit($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\Additional::submit_form($form, $form_state);
}