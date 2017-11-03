<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.10.2017
 * Time: 13:58
 */

namespace SmartCAT\Drupal\Queue;
use SmartCAT\Drupal\DB\Entity\Statistics;

class Callback implements QueueWorker {

  /**
   * @param Statistics $item
   */
  public function task($item) {
    file_put_contents(__DIR__ . '/queue.log', "{$item->get_id()}\n", FILE_APPEND);
    sleep(1);
  }

  public function complete() {
  }
}