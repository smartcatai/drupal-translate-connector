<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 20.06.2017
 * Time: 13:54
 */

namespace SmartCAT\Drupal\Handler;

use SmartCAT\API\Model\CallbackPropertyModel;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Repository\StatisticRepository;
use SmartCAT\Drupal\Drupal\Options;
use SmartCAT\Drupal\Drupal\PluginInterface;
use SmartCAT\Drupal\Helpers\SmartCAT;
use SmartCAT\Drupal\Queue\QueueEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Обработка запросов от callback smartCAT
 * Class SmartCATCallbackHandler
 *
 * @package SmartCAT\Drupal\Handler
 */
class SmartCATCallbackHandler implements PluginInterface {

  const ROUTE_PREFIX = 'translation_connectors/callback';

  /** @var  ContainerInterface */
  private $container;

  public function __construct() {
    $this->container = Connector::get_container();
  }

  /**
   * Обрабатываем запрос пришедшие от smartCAT
   */
  static public function handle($type, $method) {
    if ($type == 'document' && $method == 'status') {
      /** @var Options $options */
      $container = Connector::get_container();
      $options = $container->get('core.options');
      if (($_SERVER['HTTP_AUTHORIZATION'] ?? '') == $options->get_and_decrypt('callback_authorisation_token')) {
        $body = file_get_contents('php://input');
        $documents = json_decode($body);
        if (is_array($documents) && count($documents) > 0) {
          /** @var StatisticRepository $statistic_repository */
          $statistic_repository = $container->get('entity.repository.statistic');
          $resulting_documents = $statistic_repository->get_sended($documents);
          $queue = \DrupalQueue::get('callback_queue');
          foreach ($resulting_documents as $document) {
            if ( $document->get_error_count() > 0 ) {
              $document->set_error_count( 0 );
              $statistic_repository->persist( $document );
            }
            $queue->createItem($document->get_document_id());
          }

          // Запускаем выполнение очереди в фоне
          QueueEngine::run('callback_queue');

          $statistic_repository->flush();
        }
      }
      else {
        drupal_add_http_header('Status', '403 Forbidden');
        drupal_set_title('Access denied');
        drupal_exit();
      }
    }
    drupal_json_output(['message' => 'ok']);

    drupal_exit();
  }

  public function plugin_activate() {
    $authorisation_token = "Bearer " . base64_encode(openssl_random_pseudo_bytes(32));
    /** @var Options $options */
    $options = $this->container->get('core.options');
    $options->set_and_encrypt('callback_authorisation_token', $authorisation_token);
    $this->register_callback();
  }

  public function register_callback() {
    if (SmartCAT::is_active()) {
      /** @var Options $options */
      $options = $this->container->get('core.options');

      /** @var SmartCAT $sc */
      $sc = $this->container->get('smartcat');
      $callback_model = new CallbackPropertyModel();
      $callback_model->setUrl(url(self::ROUTE_PREFIX,['absolute' => true]));
      $callback_model->setAdditionalHeaders([
        [
          'name' => 'Authorization',
          'value' => $options->get_and_decrypt('callback_authorisation_token'),
        ],
      ]);
      $sc->getCallbackManager()->callbackUpdate($callback_model);
    }
  }

  public function plugin_deactivate() {
    if (SmartCAT::is_active()) {
      /** @var SmartCAT $sc */
      $sc = $this->container->get('smartcat');
      $sc->getCallbackManager()->callbackDelete();
    }
  }

  public function plugin_uninstall() {

  }
}