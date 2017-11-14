<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.10.2017
 * Time: 10:12
 */

namespace SmartCAT\Drupal\Queue;


use SmartCAT\Drupal\Connector;

class QueueEngine {

  /**
   * Обработываем элемент из очереди
   *
   * @param $queue_name
   */
  public static function process_callback($queue_name) {
    //    $queues = module_invoke_all('queue_engine_info');
    //    drupal_alter('queue_engine_info', $queues);
    $container = Connector::get_container();
    $queues = $container->findTaggedServiceIds($queue_name);
    if (count($queues) == 0) {
      return;
    }
    $keys = array_keys($queues);
    $firstKey = $keys[0];
    $queue = \DrupalQueue::get($queue_name);
    $queue_end = time() + 15;

    /* @var QueueWorker $worker */
    $worker = $container->get($firstKey);

    while (time() < $queue_end && ($item = $queue->claimItem())) {
      try {
        if ($worker->task($item->data)) {
          $queue->deleteItem($item);
        }
        else {
          $queue->releaseItem($item);
        }
      } catch (\Exception $e) {
        $queue->deleteItem($item);
        watchdog_exception('queue_engine', $e);
      }
    }

    if ($queue->numberOfItems() > 0) {
      sleep(1);
      self::run($queue_name);
    }
    else {
      $worker->complete();
    }
  }

  /**
   * Генерация запросов.
   *
   * @param $url
   */
  public static function async_http_request($url) {
    $url_info = parse_url($url);

    $is_https = ($url_info['scheme'] == 'https');
    $scheme = $is_https ? 'ssl://' : '';
    $port = isset($url_info['port']) ? $url_info['port'] : ($is_https ? 443 : 80);
    $query = isset($url_info['query']) ? '?' . $url_info['query'] : '';

    if (!$fp = fsockopen($scheme . $url_info['host'], $port)) {
      watchdog('background_queue', 'Socket open error.', [], WATCHDOG_ERROR);
      return;
    }

    fwrite($fp, "GET {$url_info['path']}{$query} HTTP/1.1\r\n");
    fwrite($fp, "Host: {$url_info['host']}\r\n");
    fwrite($fp, "Connection: Close\r\n\r\n");
    fclose($fp);
  }


  /**
   * Запуск обработки очереди в фоновом режиме
   *
   * @param $queue_name
   */
  public static function run($queue_name) {
    $url = url('translation_connectors/queue_engine/' . $queue_name, [
      'absolute' => TRUE,
      'query' => [
        'key' => variable_get('cron_key'),
      ],
    ]);
    self::async_http_request($url);
  }

  /**
   * Запуск обработки очереди в фоновом режиме из hook_cron().
   *
   * @param $queue_name
   */
  public static function run_from_cron($queue_name) {
    drupal_register_shutdown_function('\SmartCAT\Drupal\Drupal\QueueEngine::run', $queue_name);
  }

}