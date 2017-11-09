<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.10.2017
 * Time: 13:58
 */

namespace SmartCAT\Drupal\Queue;

use Http\Client\Common\Exception\ClientErrorException;
use Psr\Container\ContainerInterface;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Entity\Statistics;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\Helpers\SmartCAT;

class Callback implements QueueWorker {

  public function update_statistic($item) {
    /** @var ContainerInterface $container */
    $container = Connector::get_container();

    /** @var StatisticRepository $statistic_repository */
    $statistic_repository = $container->get('entity.repository.statistic');

    /** @var SmartCAT $sc */
    $sc = $container->get('smartcat');

    $statistics = $statistic_repository->get_one_by(['documentID' => $item]);

    try {
      if ($statistics) {
        $document = $sc->getDocumentManager()
          ->documentGet(['documentId' => $statistics->get_document_id()]);
        $stages = $document->getWorkflowStages();
        $progress = 0;
        foreach ($stages as $stage) {
          $progress += $stage->getProgress();
        }
        $progress = round($progress / count($stages), 2);
        $statistics->set_progress($progress)
          ->set_words_count($document->getWordsCount())
          ->set_error_count(0);
        $statistic_repository->update($statistics);
        if ($document->getStatus() == 'completed') {
          $queue = \DrupalQueue::get('publication_queue');
          $queue->createItem($item);
        }
      }
    } catch (ClientErrorException $e) {
      if ($e->getResponse()->getStatusCode() == 404) {
        $statistic_repository->delete($statistics);
      }
      else {
        throw $e;
      }
    }

  }

  /**
   * @param Statistics $item
   */
  public function task($item) {
    if (!SmartCAT::is_active()) {
      sleep(10);

      return FALSE;
    }
    try {
      $this->update_statistic($item);
    } catch (ClientErrorException $e) {
      /** @var ContainerInterface $container */
      $container = Connector::get_container();

      /** @var StatisticRepository $statistic_repository */
      $statistic_repository = $container->get('entity.repository.statistic');

      $statistics = $statistic_repository->get_one_by(['documentID' => $item]);
      if ($statistics && $statistics->get_error_count() < 360) {
        $statistics->inc_error_count();
        $statistic_repository->update($statistics);
        sleep(10);

        return FALSE;
      }
      watchdog('translation_connectors', "Document $item, update statistic API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}", [], WATCHDOG_ERROR);
    }

    return TRUE;
  }

  public function complete() {
    // Запускаем выполнение очереди в фоне
    QueueEngine::run('publication_queue');
  }
}