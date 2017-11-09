<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 08.11.2017
 * Time: 14:58
 */

namespace SmartCAT\Drupal\Queue;


use Http\Client\Common\Exception\ClientErrorException;
use Psr\Container\ContainerInterface;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\Helpers\SmartCAT;

class CreatePost implements QueueWorker {

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

    $statistics = $statistic_repository->get_one_by(['documentID' => $item['documentID']]);
    if (is_null($statistics)) {
      throw new \Exception("CreatePost - Statistics object is empty, documentID - {$item['documentID']}. Can't get statistics by documentID - {$item['documentID']}");
    }

    try {

      $result = $sc->getDocumentExportManager()
        ->documentExportDownloadExportResult($item['taskID']);
      if (204 == $result->getStatusCode()) {
        sleep(1);

        return FALSE;
      }
      elseif (200 == $result->getStatusCode()) {
        $response_body = $result->getBody()->getContents();
        $html = new \DOMDocument();
        $html->loadHTML($response_body);
        $title = $html->getElementsByTagName('title')->item(0)->nodeValue;
        $body = '';
        $summary = '';
        $summaryElement = $html->getElementById('summary-drupal-translation-connectors');
        if ($summaryElement) {
          foreach ($summaryElement->childNodes as $child) {
            $summary .= $child->ownerDocument->saveXML($child);
          }
        }
        $bodyElement = $html->getElementById('body-drupal-translation-connectors');
        if ($bodyElement) {
          foreach ($bodyElement->childNodes as $child) {
            $body .= $child->ownerDocument->saveXML($child);
          }
        }

        file_put_contents(__DIR__ . '/create.log', "$title\n\n$summary\n\n$body\n\n\n\n");

        $statistics->set_status('completed');
        $statistic_repository->update($statistics);
      }
    } catch (ClientErrorException $e) {
      if (404 == $e->getResponse()->getStatusCode()) {
        $statistics->set_status('sended');
        $statistic_repository->update($statistics);
        $queue = \DrupalQueue::get('publication_queue');
        $queue->createItem($item['documentID']);
        QueueEngine::run('publication_queue');
      }
      else {
        if ($statistics->get_error_count() < 360) {
          $statistics->inc_error_count();
          $statistic_repository->update($statistics);
          sleep(10);

          return FALSE;
        }
      }
      watchdog('translation_connectors', "Document {$item['documentID']}, download translate. API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}", [], WATCHDOG_ERROR);
    } catch (\Exception $e) {
      if ($e->getMessage() == 'Export not completed') {
        return FALSE;
      }
      else {
        watchdog('translation_connectors', "Document {$item['documentID']}, download translate. Message: {$e->getMessage()}", [], WATCHDOG_ERROR);
      }
    }

    return TRUE;
  }

  public function complete() {
    // TODO: Implement complete() method.
  }
}