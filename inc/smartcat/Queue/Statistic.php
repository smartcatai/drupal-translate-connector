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
use SmartCAT\Drupal\Helpers\SmartCAT;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\Drupal\Options;


/** Обработка очереди "Обновление статистики" */
class Statistic implements QueueWorker {

  public function task( $item ) {
		if ( SmartCAT::is_active() ) {
			try {
				/** @var ContainerInterface $container */
				$container = Connector::get_container();

				/** @var \SmartCAT\Drupal\Queue\Callback $queue */
				$queue = $container->get( 'core.queue.callback' );
				$queue->update_statistic( $item );

			} catch ( ClientErrorException $e ) {
        watchdog('translation_connectors',  "Document $item, update statistic. API error code: {$e->getResponse()->getStatusCode()}. API error message: {$e->getResponse()->getBody()->getContents()}", [], WATCHDOG_ERROR );
			}
		}

		return TRUE;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
  public function complete() {
		/** @var ContainerInterface $container */
		$container = Connector::get_container();
    QueueEngine::run('publication_queue');
		/** @var Options $options */
		$options = $container->get( 'core.options' );
		$options->set( 'statistic_queue_active', false );

		// Show notice to user or perform some other arbitrary task...
	}
}