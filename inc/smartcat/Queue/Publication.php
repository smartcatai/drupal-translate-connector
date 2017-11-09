<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 08.11.2017
 * Time: 13:19
 */

namespace SmartCAT\Drupal\Queue;


use Http\Client\Common\Exception\ClientErrorException;
use Psr\Container\ContainerInterface;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\Helpers\SmartCAT;

class Publication implements QueueWorker {

  public function task($item) {
    // Actions to perform
    if (!SmartCAT::is_active()) {
      sleep(10);

      return FALSE;
    }
    /** @var ContainerInterface $container */
    $container = Connector::get_container();

    /** @var StatisticRepository $statistic_repository */
    $statistic_repository = $container->get('entity.repository.statistic');

    /** @var SmartCAT $sc */
    $sc = $container->get('smartcat');

    $statistics = $statistic_repository->get_one_by(['documentID' => $item]);
    try {
      if ($statistics && $statistics->get_status() == 'sended') {
        $task = $sc->getDocumentExportManager()
          ->documentExportRequestExport(['documentIds' => [$statistics->get_document_id()]]);
        if ($task->getId()) {
          $statistics->set_status('export')
            ->set_error_count(0);
          $statistic_repository->update($statistics);
          $queue = \DrupalQueue::get('create_post_queue');
          file_put_contents(__DIR__ . '/publication.log', print_r([
            'documentID' => $statistics->get_document_id(),
            'taskID' => $task->getId(),
          ], true) ."\n\n", FILE_APPEND);
          $queue->createItem([
            'documentID' => $statistics->get_document_id(),
            'taskID' => $task->getId(),
          ]);
        }
      }
    } catch (ClientErrorException $e) {
      $status_code = $e->getResponse()->getStatusCode();
      if ($status_code == 404) {
        $statistic_repository->delete($statistics);
      }
      else {
        if ($statistics->get_error_count() < 360) {
          $statistics->inc_error_count();
          $statistic_repository->update($statistics);
          sleep(10);

          watchdog('translation_connectors', "Document $item, start download translate API error code: {$status_code}. API error message: {$e->getResponse()->getBody()->getContents()}", [], WATCHDOG_ERROR);

          return FALSE;
        }
      }
    }

    return TRUE;
  }

  public function complete() {
    QueueEngine::run('create_post_queue');
  }
}