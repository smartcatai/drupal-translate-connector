<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 14.11.2017
 * Time: 22:04
 */

function translation_connectors_statistic_filter_form($form, $form_state) {
  return \SmartCAT\Drupal\Drupal\Admin\Forms\StatisticFilter::get_form($form, $form_state);
}

function translation_connectors_statistic_filter_validate($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\StatisticFilter::validate_form($form, $form_state);
}

function translation_connectors_statistic_filter_submit($form, &$form_state) {
  \SmartCAT\Drupal\Drupal\Admin\Forms\StatisticFilter::submit_form($form, $form_state);
}