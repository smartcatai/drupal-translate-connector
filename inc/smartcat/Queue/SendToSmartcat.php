<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 19.06.2017
 * Time: 21:45
 */

namespace SmartCAT\Drupal\Queue;

use Http\Client\Common\Exception\ClientErrorException;
use Psr\Container\ContainerInterface;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\Core\Exchange;
use SmartCAT\Drupal\DB\Entity\Task;
use SmartCAT\Drupal\DB\Repository\TaskRepository;
use SmartCAT\Drupal\Drupal\Options;
use SmartCAT\Drupal\Helpers\SmartCAT;


/** Обработка очереди "Обновление статистики" */
class SendToSmartcat implements QueueWorker {

  public function task($item) {
    if (SmartCAT::is_active()) {
      try {
        /** @var ContainerInterface $container */
        $container = Connector::get_container();
        /** @var Exchange $exchange */
        $exchange = $container->get('smartcat.exchange');
        /** @var TaskRepository $repository */
        $repository = $container->get('entity.repository.task');
        /** @var Task $task */
        $task = $repository->get_one_by(['id' => $item]);

        $exchange::create_project($task);

      } catch (ClientErrorException $e) {
        watchdog('translation_connectors', "Document $item, update statistic. API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}", [], WATCHDOG_ERROR);
      }
    }

    return TRUE;
  }

  public function complete() {
    /** @var ContainerInterface $container */
    $container = Connector::get_container();
    /** @var Options $options */
    $options = $container->get('core.options');
    $options->set('new_project_queue_active', FALSE);
  }
}