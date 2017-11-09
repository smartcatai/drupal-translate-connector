<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 02.11.2017
 * Time: 17:44
 */

namespace SmartCAT\Drupal\Core;


use Http\Client\Common\Exception\ClientErrorException;
use SmartCAT\API\Model\CreateProjectWithFilesModel;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Entity\Statistics;
use SmartCAT\Drupal\DB\Entity\Task;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\DB\Repository\TaskRepository;
use SmartCAT\Drupal\Drupal\Notice;
use SmartCAT\Drupal\Drupal\Options;
use SmartCAT\Drupal\Helpers\Language\LanguageConverter;
use SmartCAT\Drupal\Helpers\SmartCAT;

class Exchange {

  //Отправка статьи на перевод
  public static function send_to_smartcat($entity_type, $entity, $from_language, $to_languages) {
    $container = Connector::get_container();
    /** @var TaskRepository $task_repository */
    $task_repository = $container->get('entity.repository.task');
    /** @var StatisticRepository $statistics_repository */
    $statistics_repository = $container->get('entity.repository.statistic');
    /** @var Notice $notice*/
    $notice = $container->get('core.notice');

    $is_new_task_created = FALSE;
    foreach ($to_languages as $key => $value) {
      if (!$to_languages[$key]) {
        unset($to_languages[$key]);
      }
    }
    $task = new Task();
    $task->set_entity_id($entity->nid)
      ->set_entity_type($entity_type)
      ->set_source_language($from_language)
      ->set_target_languages(array_values($to_languages))
      ->set_status('new')
      ->set_project_id(NULL);

    $task_id = $task_repository->add($task);

    if ($task_id) {
      $is_new_task_created = TRUE;

      $stat = new Statistics();
      $stat->set_task_id($task_id)
        ->set_entity_id($entity->nid)
        ->set_entity_type($entity_type)
        ->set_source_language($from_language)
        ->set_progress(0)
        ->set_words_count(NULL)
        ->set_target_entity_id(NULL)
        ->set_document_id(NULL)
        ->set_status('new');

      $data['stats'] = [];

      foreach ($to_languages as $target_language) {
        $newStat = clone $stat;
        $newStat->set_target_language($target_language);
        $stat_id = $statistics_repository->add($newStat);
        if ($stat_id) {
          array_push($data['stats'], $stat_id);
        }
        else {
          array_push($data['failed-stats'], $stat_id);
        }
      }

      if (count($data['stats']) != count($to_languages)) {
        $notice->add_error(t('Not all stats was created', [], ['context' => 'translation_connectors']));
      }
    }

    if (SmartCAT::is_active()) {
      self::create_project($task);
    }

    if ($is_new_task_created) {
      $notice->add_success(t('Task was created', [], ['context' => 'translation_connectors']));
    }
    else {
      $notice->add_error(t('Task was not created', [], ['context' => 'translation_connectors']));
    }
  }

  public static function create_project(Task $task) {
    $container = Connector::get_container();

    /** @var SmartCAT $sc */
    $sc = $container->get('smartcat');

    /** @var Options $options */
    $options = $container->get('core.options');

    /** @var TaskRepository $task_repository */
    $task_repository = $container->get('entity.repository.task');

    /** @var StatisticRepository $statistic_repository */
    $statistic_repository = $container->get('entity.repository.statistic');

    /** @var LanguageConverter $converter */
    $converter = $container->get('language.converter');

    $workflow_stages = $options->get('smartcat_workflow_stages');
    $vendor_id = $options->get('smartcat_vendor_id');

    try {
      $entities = entity_load($task->get_entity_type(), [$task->get_entity_id()]);
      $entity = $entities[$task->get_entity_id()];
      $post_title = _entity_translation_label($task->get_entity_type(), $entity, $task->get_source_language());
      $post_summary = $entity->body[$task->get_source_language()][0]['safe_summary'] ?? '';
      $post_content = $entity->body[$task->get_source_language()][0]['safe_value'];
      $file_body = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /><title>{$post_title}</title></head><body><div id='summary-drupal-translation-connectors'>$post_summary</div><div id='body-drupal-translation-connectors'>{$post_content}</divdiv></body></html>";
      $file_name = "{$post_title}.html";
      $file = fopen("smartcat://id_{$task->get_entity_id()}", "r+");
      fwrite($file, $file_body);
      rewind($file);

      $task_name = $post_title;

      $project_model = new CreateProjectWithFilesModel();
      $project_model->setName($sc::filter_chars($task_name));
      $project_model->setSourceLanguage($converter->get_sc_code_by_drupal($task->get_source_language())
        ->get_sc_code());
      $project_model->setTargetLanguages(array_map(function ($wp_code) use ($converter) {
        return $converter->get_sc_code_by_drupal($wp_code)->get_sc_code();
      }, $task->get_target_languages()));
      $project_model->setWorkflowStages($workflow_stages);

      if ($vendor_id) {
        $project_model->setAssignToVendor(TRUE);
        $project_model->setVendorAccountId($vendor_id);
      }
      else {
        $project_model->setAssignToVendor(FALSE);
      }

      $project_model->attacheFile($file, $sc::filter_chars($file_name));

      $smartcat_project = $sc->getProjectManager()
        ->projectCreateProjectWithFiles($project_model);

      $task->set_status('created');
      $task->set_project_id($smartcat_project->getId());
      $task_repository->update($task);

      foreach ($smartcat_project->getDocuments() as $document) {
        $statistic_repository->link_to_smartcat_document($task, $document);
      }
    } catch (\Exception $e) {
      if ($e instanceof ClientErrorException) {
        $message = "API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}";
      }
      else {
        $message = "Message: {$e->getMessage()}. Trace: {$e->getTraceAsString()}";
      }
      watchdog('translation_connectors', $message, [], WATCHDOG_ERROR);
    }
  }

  public static function send_new_task(){
    $container = Connector::get_container();

    /** @var TaskRepository $task_repository */
    $task_repository = $container->get('entity.repository.task');

    $tasks = $task_repository->get_new_task();
    foreach ($tasks as $task) {
      self::create_project($task);
    }
  }
}