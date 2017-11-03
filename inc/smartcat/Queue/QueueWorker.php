<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.10.2017
 * Time: 13:26
 */

namespace SmartCAT\Drupal\Queue;


interface QueueWorker {

  public function task($item);

  public function complete();
}