<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 14.11.2017
 * Time: 22:02
 */

namespace SmartCAT\Drupal\Drupal\Admin\Forms;


use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;

class StatisticFilter implements DrupalForm {

  public static function get_form($form, &$form_state, ...$params) {
    $container = Connector::get_container();
    /** @var StatisticRepository $statistics_repository */
    $statistics_repository = $container->get('entity.repository.statistic');
    $languages = language_list();
    $langSelect = [NULL => NULL];
    foreach ($languages as $langcode => $language) {
      $langSelect[$langcode] = t($language->name);
    }
    $form['sourceLanguage'] = [
      '#title' => t('Source language', [], ['context' => 'translation_connectors']),
      '#type' => 'select',
      '#options' => $langSelect,
      '#required' => FALSE,
      '#prefix' => '<div class="container-inline" style="display: inline">',
      '#suffix' => '</div>',
      '#default_value' => $_GET['sourceLanguage'] ?? NULL,
    ];

    $form['targetLanguage'] = [
      '#title' => t('Target language', [], ['context' => 'translation_connectors']),
      '#type' => 'select',
      '#options' => $langSelect,
      '#required' => FALSE,
      '#prefix' => '<div class="container-inline" style="display: inline">',
      '#suffix' => '</div>',
      '#default_value' => $_GET['targetLanguage'] ?? NULL,
    ];

    $statuses = $statistics_repository->get_localized_statuses_list();
    $form['status'] = [
      '#title' => t('Status',[],['context'=>'translation_connectors']),
      '#type' => 'select',
      '#options' => $statuses,
      '#required' => FALSE,
      '#prefix' => '<div class="container-inline" style="display: inline">',
      '#suffix' => '</div>',
      '#default_value' => $_GET['status'] ?? NULL,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filter',[],['context'=>'translation_connectors']),
      '#prefix' => '<div class="container-inline" style="display: inline">',
      '#suffix' => '</div>',
    ];
    $form['#redirect'] = FALSE;
    $form['#method'] = 'get';

    return $form;
  }

  public static function validate_form($form, &$form_state) {
  }

  public static function submit_form($form, &$form_state) {
  }
}