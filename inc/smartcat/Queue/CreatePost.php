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

        $entities = entity_load($statistics->get_entity_type(), [$statistics->get_entity_id()]);
        $entity = $entities[$statistics->get_entity_id()];

        //file_put_contents(__DIR__ . '/create.log', "$title\n\n$summary\n\n$body\n\n\n\n");
        /** @var \EntityTranslationHandlerInterface $handler */
        $handler = entity_translation_get_handler($statistics->get_entity_type(), $entity);
        // Ensure $entity holds an entity object and not an id.
        $entity = $handler->getEntity();
        $handler->initPathScheme();
        $translations = $handler->getTranslations();
        if (!isset($translations->data[$statistics->get_target_language()])) {
          // If we have a new translation the language is the original entity
          // language.
          $translation = [
            'language' => $statistics->get_target_language(),
            'source' => $statistics->get_source_language(),
            'translate' => 0,
          ];
        }
        else {
          $translation = $translations->data[$statistics->get_target_language()];
        }
        $translation['status'] = 1;
        $translation['uid'] = (empty($entity->uid)) ? 0 : $entity->uid;
        $translation['created'] = REQUEST_TIME;
        $values = [];
        if (isset($entity->title_field)) {
          $values['title_field'] = [
            $statistics->get_target_language() => [
              $translation['translate'] => [
                'value' => strip_tags($title),
                'format' => $entity->title_field[$statistics->get_source_language()][0]['format'] ?? NULL,
                'safe_value' => $title,
              ],
            ],
          ];
        }
        $values['body'] = [
          $statistics->get_target_language() => [
            $translation['translate'] => [
              'value' => strip_tags($body),
              'summary' => strip_tags($summary),
              'format' => $entity->body[$statistics->get_source_language()][0]['format'] ?? NULL,
              'safe_value' => $body,
              'safe_summary' => $summary,
            ],
          ],
        ];
        $handler->setTranslation($translation, $values);
        $handler->saveTranslations();
        entity_translation_entity_save($statistics->get_entity_type(), $entity);

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