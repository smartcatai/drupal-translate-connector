<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 25.10.2017
 * Time: 16:27
 */

namespace SmartCAT\Drupal\Drupal\Admin\Forms;

use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\Core\Exchange;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\Helpers\Language\Exceptions\LanguageNotFoundException;
use SmartCAT\Drupal\Helpers\Language\LanguageConverter;

require_once DRUPAL_ROOT . '/sites/all/modules/entity_translation/entity_translation.admin.inc';


class SendToTranslate implements DrupalForm {

  private static function get_target_table($entity_type, $entity) {
    $handler = entity_translation_get_handler($entity_type, $entity);
    // Ensure $entity holds an entity object and not an id.
    $entity = $handler->getEntity();
    $handler->initPathScheme();

    // Initialize translations if they are empty.
    $translations = $handler->getTranslations();
    if (empty($translations->original)) {
      $handler->initTranslations();
      $handler->saveTranslations();
    }

    $header = [
      t('Language'),
      t('Source language'),
      t('Status'),
    ];
    $languages = entity_translation_languages();
    $source = $translations->original;
    $rows = [];
    $disabled = [];

    if (drupal_multilingual()) {
      // If we have a view path defined for the current entity get the switch
      // links based on it.
      $container = Connector::get_container();
      /** @var LanguageConverter $converter */
      $converter = $container->get('language.converter');
      /** @var StatisticRepository $statistic_repository */
      $statistic_repository = $container->get('entity.repository.statistic');
      $statistics = $statistic_repository->get_by(['entityID' => $entity->nid]);
      $statuses =[];
      if (is_array($statistics)) {
        foreach ($statistics as $statistic) {
          $statuses[$statistic->get_target_language()] = $statistic->get_status();
        }
      }
      foreach ($languages as $language) {
        $language_name = t($language->name);
        $langcode = $language->language;
        $is_original = $langcode == $translations->original;
        $translation = $translations->data[$langcode] ?? NULL;
        $source_name = t('n/a');
        $status = ($translation['status'] ?? NULL) ? t('Published') : t('Not published');
        //Проверям поддерживает ли SC выбранный язык
        try {
          $sc_langcode = $converter->get_sc_code_by_drupal($langcode);
        } catch (LanguageNotFoundException $e) {
          $disabled[$langcode] = ['#disabled' => TRUE];
          if (!($translation['status'] ?? NULL)) {
            $status = t('Language are not supported', [], ['context' => 'translation_connectors']);
          }
        }
        if ($translation) {
          $disabled[$langcode] = ['#disabled' => TRUE];
          if ($is_original) {
            $language_name = t('<strong>@language_name</strong>', ['@language_name' => $language_name]);
            $source_name = t('(original content)');
          }
          else {
            $source_name = $languages[$translation['source']]->name;
          }
        }
        if (isset($statuses[$langcode])) {
          $disabled[$langcode] = ['#disabled' => TRUE];
          switch ($statuses[$langcode]){
            case 'new':
              $status = t('Submitted', [], ['context' => 'translation_connectors']);
              break;
            case 'sended':
            case 'export':
              $status = t('In progress', [], ['context' => 'translation_connectors']);
              break;
            case 'completed':
              $status = t('Completed', [], ['context' => 'translation_connectors']);
              break;
            default:
              return $statuses[$langcode];
          }
        }

        $rows[$langcode] = [
          $language_name,
          $source_name,
          $status,
        ];
      }
    }

    drupal_set_title(t('Translations of %label', ['%label' => $handler->getLabel()]), PASS_THROUGH);

    return [
      'header' => $header,
      'rows' => $rows,
      'disabled' => $disabled,
    ];
  }

  private static function get_source_table($entity_type, $entity) {
    $handler = entity_translation_get_handler($entity_type, $entity);
    // Ensure $entity holds an entity object and not an id.
    $entity = $handler->getEntity();
    $handler->initPathScheme();

    // Initialize translations if they are empty.
    $translations = $handler->getTranslations();
    if (empty($translations->original)) {
      $handler->initTranslations();
      $handler->saveTranslations();
    }

    $header = [
      t('Language'),
    ];
    $languages = entity_translation_languages();
    $rows = [];
    $disabled = [];

    if (drupal_multilingual()) {
      // If we have a view path defined for the current entity get the switch
      // links based on it.
      /** @var LanguageConverter $converter */
      $converter = Connector::get_container()->get('language.converter');
      foreach ($languages as $language) {
        $language_name = $language->name;
        $langcode = $language->language;

        if ($translation = $translations->data[$langcode] ?? NULL) {
          try {
            $sc_langcode = $converter->get_sc_code_by_drupal($langcode);
            $rows[$langcode] = t($language_name);
          } catch (LanguageNotFoundException $e) {
          }
        }

        $status = ($translation['status'] ?? NULL) ? t('Published') : t('Not published');;
        // Show fallback information if required.
      }
    }

    return [
      'header' => $header,
      'rows' => $rows,
      'disabled' => $disabled,
    ];
  }


  public static function get_form($form, &$form_state, ...$params) {
    list($entity_type, $entity) = $params;
    $form['#prefix'] = '<div id="translation-connectors-send-to-translate-form-wrapper">';
    $form['#tree'] = TRUE;
    $form['#suffix'] = '</div>';

    $step = empty($form_state['storage']['step']) ? 1 : $form_state['storage']['step'];
    $form_state['storage']['step'] = $step;

    $disableNext = FALSE;
    switch ($step) {
      case 1:
        $table = self::get_target_table($entity_type, $entity);
        $form['step1'] = [
          '#type' => 'fieldset',
          '#title' => t('Target language', [], ['context' => 'translation_connectors']),
        ];
        $form['step1']['target'] = [
          '#type' => 'tableselect',
          '#header' => $table['header'],
          '#options' => $table['rows'],
          '#multiple' => TRUE,
          '#js_select' => TRUE,
          '#required' => TRUE,
        ];
        $form['step1']['entity'] =[
          '#type' => 'value',
          '#value' => $entity,
        ];
        $form['step1']['entity_type'] =[
          '#type' => 'value',
          '#value' => $entity_type,
        ];
        $form['step1']['target'] = array_merge($form['step1']['target'], $table['disabled']);
        if (count($table['disabled']) == count($table['rows'])) {
          $disableNext = TRUE;
        }
        break;
      case 2:
        $table = self::get_source_table($entity_type, $entity);
        $form['step2'] = [
          '#type' => 'fieldset',
          '#title' => t('	Source language', [], ['context' => 'translation_connectors']),
        ];
        $form['step2']['source'] = [
          '#type' => 'radios',
          '#options' => $table['rows'],
          '#required' => TRUE,
        ];
        $form['step2']['source'] = array_merge($form['step2']['source'], $table['disabled']);
        break;
    }

    $form['actions'] = ['#type' => 'actions'];

    if ($step < 2 && !$disableNext) {
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => t('Next', [], ['context' => 'translation_connectors']),
        '#ajax' => [
          'wrapper' => 'translation-connectors-send-to-translate-form-wrapper',
          'callback' => 'translation_connectors_send_to_translate_form_ajax_callback',
          'file' => 'inc/wrappers/admin/forms/send_to_translate_form.inc',
          'module' => 'translation_connectors',
        ],
      ];
    }

    if ($step > 1) {
      $form['actions']['prev'] = [
        '#type' => 'submit',
        '#value' => t('Previous', [], ['context' => 'translation_connectors']),
        '#limit_validation_errors' => [],
        '#submit' => ['translation_connectors_send_to_translate_form_submit'],
        '#ajax' => [
          'wrapper' => 'translation-connectors-send-to-translate-form-wrapper',
          'callback' => 'translation_connectors_send_to_translate_form_ajax_callback',
          'file' => 'inc/wrappers/admin/forms/send_to_translate_form.inc',
          'module' => 'translation_connectors',
        ],
      ];
    }

    if ($step == 2) {
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t('Submit for translation', [], ['context' => 'translation_connectors']),
      ];
    }

    $form['#validate'][] = 'translation_connectors_send_to_translate_form_validate';
    return $form;

  }

  public static function validate_form($form, &$form_state) {
    // TODO: Implement validate_form() method.
    switch ($form_state['storage']['step']) {
      case 1:
        if (empty($form_state['values']['step1']['target'])) {
          form_set_error('target', t('Target languages are empty', [], ['context' => 'translation_connectors']));
        }
        break;
    }
  }

  public static function submit_form($form, &$form_state) {
    $current_step = 'step' . $form_state['storage']['step'];
    if (!empty($form_state['values'][$current_step])) {
      $form_state['storage']['values'][$current_step] = $form_state['values'][$current_step];
    }

    // Если перешли на следующий шаг - то увеличиваем счётчик шагов.
    if (isset($form['actions']['next']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['next']['#value']) {
      $form_state['storage']['step']++;

      // Если данные для следующего шага были уже введены пользователем,
      // то восстанавливаем их и передаём в форму.
      $step_name = 'step' . $form_state['storage']['step'];
      if (!empty($form_state['storage']['values'][$step_name])) {
        $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
      }
    }

    // Если вернулись на шаг назад - уменьшаем счётчик шагов.
    if (isset($form['actions']['prev']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['prev']['#value']) {
      $form_state['storage']['step']--;

      // Забираем из хранилища данные по предыдущему шагу и возвращаем их в форму.
      $step_name = 'step' . $form_state['storage']['step'];
      $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
    }

    // Если пользователь прошёл все шаги и нажал на кнопку "Хватит",
    // то обрабатываем полученные данные со всех шагов.
    if (isset($form['actions']['submit']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['submit']['#value']) {
      //Exchange::send_to_smartcat()
      //drupal_set_message($message);
      Exchange::send_to_smartcat($form_state['storage']['values']['step1']['entity_type'], $form_state['storage']['values']['step1']['entity'], $form_state['storage']['values']['step2']['source'], $form_state['storage']['values']['step1']['target']);
      $form_state['rebuild'] = FALSE;
      return;
    }

    // Указываем, что форма должна быть построена заново.
    $form_state['rebuild'] = TRUE;
  }
}