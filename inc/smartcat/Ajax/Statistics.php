<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 14.11.2017
 * Time: 17:24
 */

namespace SmartCAT\Drupal\Ajax;


use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\DB\Repository\TaskRepository;
use SmartCAT\Drupal\Drupal\Options;
use SmartCAT\Drupal\Queue\QueueEngine;

class Statistics {

  public static function check() {
    $container = Connector::get_container();

    /** @var Options $options */
    $options = $container->get('core.options');

    drupal_json_output(['data' => ['statistic_queue_active' => $options->get('statistic_queue_active') || $options->get('new_project_queue_active')]]);

    drupal_exit();
  }

  public static function start() {
    $container = Connector::get_container();

    /** @var Options $options */
    $options = $container->get('core.options');

    if (!$options->get('statistic_queue_active')) {

      /** @var StatisticRepository $statistic_repository */
      $statistic_repository = $container->get('entity.repository.statistic');
      $statistics = $statistic_repository->get_sended();
      if (count($statistics) > 0) {
        $options->set('statistic_queue_active', TRUE);
        $queue = \DrupalQueue::get('statistic_queue');
        foreach ($statistics as $statistic) {
          if ($statistic->get_error_count() > 0) {
            $statistic->set_error_count(0);
            $statistic_repository->persist($statistic);
          }

          $queue->createItem($statistic->get_document_id());
        }
        $statistic_repository->flush();
      }
      QueueEngine::run('statistic_queue');
    }
    if (!$options->get('new_project_queue_active')) {
      /** @var TaskRepository $task_repository */
      $task_repository = $container->get('entity.repository.task');
      $tasks = $task_repository->get_new_task();
      if (count($tasks) > 0) {
        $options->set('new_project_queue_active', TRUE);
        $queue = \DrupalQueue::get('send_new_queue');
        foreach ($tasks as $task) {
          $queue->createItem($task->get_id());
        }
        QueueEngine::run('send_new_queue');
      }
    }
    drupal_json_output(['message' => 'ok']);

    drupal_exit();
  }
}