<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 13.11.2017
 * Time: 14:37
 */

namespace SmartCAT\Drupal\Drupal\Admin\Pages;

use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\Helpers\SmartCAT;

require_once DRUPAL_ROOT . '/sites/all/modules/entity_translation/entity_translation.admin.inc';

class Statistics {

  private static function get_enity_paths() {
    $edit_paths = [];
    foreach (entity_get_info() as $entity_type => $info) {
      if (entity_translation_enabled($entity_type)) {
        $schemes = $info['translation']['entity_translation']['path schemes'];
        foreach ($schemes as $scheme) {
          $edit_paths[$entity_type] = [];
          if (isset($scheme['edit path'])) {
            $edit_paths[$entity_type]['edit'] = $scheme['edit path'];
          }
          if (isset($scheme['translate path'])) {
            $edit_paths[$entity_type]['translate'] = $scheme['translate path'];
          }
          if (isset($scheme['path wildcard'])) {
            $edit_paths[$entity_type]['wildcard'] = $scheme['path wildcard'];
          }
        }
      }
    }
    return $edit_paths;
  }

  /**
   * @param $entity_paths
   * @param $entity_type
   * @param $entity
   *
   * @return string|null
   */
  private static function get_edit_path($entity_paths, $entity_type, $entity, $path_type) {
    if (isset($entity_paths[$entity_type][$path_type]) && isset($entity_paths[$entity_type]['wildcard'])) {
      return str_replace($entity_paths[$entity_type]['wildcard'], $entity->nid, $entity_paths[$entity_type][$path_type]);
    }
    else {
      return NULL;
    }
  }

  public static function get() {
    $header = [
      [
        'data' => t('Title', [], ['context' => 'translation_connectors']),
        'field' => 'entityID',
      ],
      [
        'data' => t('Source language', [], ['context' => 'translation_connectors']),
        'field' => 'sourceLanguage',
      ],
      [
        'data' => t('Target language', [], ['context' => 'translation_connectors']),
        'field' => 'targetLanguage',
      ],
      t('Words count', [], ['context' => 'translation_connectors']),
      [
        'data' => t('Status', [], ['context' => 'translation_connectors']),
        'field' => 'status',
      ],
      t('Edit post', [], ['context' => 'translation_connectors']),
    ];

    $enity_paths = self::get_enity_paths();
    $rows = [];
    $languages = entity_translation_languages();

    $container = Connector::get_container();
    /** @var StatisticRepository $repo */
    $repo = $container->get('entity.repository.statistic');
    $filters = [];
    if (!empty($_GET['sourceLanguage'])) {
      $filters['sourceLanguage'] = (string)$_GET['sourceLanguage'];
    }
    if (!empty($_GET['targetLanguage'])) {
      $filters['targetLanguage'] = (string)$_GET['targetLanguage'];
    }
    if (!empty($_GET['status'])) {
      $filters['status'] = (string)$_GET['status'];
    }
    $statistics = $repo->get_statistics($header, $filters);

    foreach ($statistics as $statistic) {
      $entity = entity_load($statistic->get_entity_type(), [$statistic->get_entity_id()])[$statistic->get_entity_id()] ?? NULL;
      if (!empty($entity)) {
        $post_title = _entity_translation_label($statistic->get_entity_type(), $entity, $statistic->get_source_language());
        $source_language_name = t(($languages[$statistic->get_source_language()]->name ?? $statistic->get_source_language()));
        $target_language_name = t(($languages[$statistic->get_target_language()]->name ?? $statistic->get_target_language()));
        if (!empty($statistic->get_document_id())) {
          $document_url = l($statistic->get_localized_status_name(), SmartCAT::get_document_edit_path($statistic->get_document_id()), [
            'absolute' => TRUE,
            'attributes' => ['target' => '_blank'],
          ]);
        }
        else {
          $document_url = $statistic->get_localized_status_name();
        }
        if ($statistic->get_status() == 'completed') {
          $edit_translate = l(t('edit'), self::get_edit_path($enity_paths, $statistic->get_entity_type(), $entity, 'edit') . "/{$statistic->get_target_language()}");
        }
        else {
          $edit_translate = '';
        }
        $rows[] = [
          l($post_title, self::get_edit_path($enity_paths, $statistic->get_entity_type(), $entity, 'edit')),
          $source_language_name,
          $target_language_name,
          $statistic->get_words_count(),
          $document_url,
          $edit_translate,
        ];
      }
    }

    drupal_add_js(drupal_get_path('module', 'translation_connectors') . '/js/statistics.js');
    $form = drupal_get_form('translation_connectors_statistic_filter_form');
    $html = '<input type="button" value="' . t('Refresh statistics', [], ['context' => 'translation_connectors']) . '" class="smartcat-connector-refresh-statistics form-submit">';
    return $html . drupal_render($form) . theme('table', [
        'header' => $header,
        'rows' => $rows,
      ]) . $html . theme('pager');
  }
}