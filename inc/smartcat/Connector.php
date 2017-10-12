<?php

namespace SmartCAT\Drupal;


//use SmartCAT\Drupal\Cron\CronInterface;
//use SmartCAT\Drupal\DB\Repository\TaskRepository;
use SmartCAT\Drupal\Drupal\HookInterface;
use SmartCAT\Drupal\Drupal\InitInterface;
use SmartCAT\Drupal\Drupal\PluginInterface;
use SmartCAT\Drupal\Helpers\SmartCAT;
use Symfony\Component\DependencyInjection\ContainerInterface;

//use SmartCAT\Drupal\Queue\QueueAbstract;

class Connector {

  use DITrait;

  public static $plugin_version = NULL;

  public function __construct() {
    ignore_user_abort(TRUE);
    set_time_limit(0);
    $this->init_cron();
    $this->register_hooks();
  }

  private function init_cron() {
    //		$new_schedules = [];
    //		$services     = self::get_container()->findTaggedServiceIds( 'cron' );
    //		foreach ( $services as $service => $tags ) {
    //			$object = $this->from_container( $service );
    //			if ( $object instanceof CronInterface ) {
    //				$new_schedules = array_merge( $new_schedules, $object->get_interval() );
    //			}
    //		}
    //
    //		add_filter( 'cron_schedules', function ( $schedules ) use ( $new_schedules ) {
    //			$schedules = array_merge( $schedules, $new_schedules );
    //
    //			return $schedules;
    //		} );
  }

  private function register_hooks() {
    $hooks = self::get_container()->findTaggedServiceIds('hook');
    foreach ($hooks as $hook => $tags) {
      $object = $this->from_container($hook);
      if ($object instanceof HookInterface) {
        $object->register_hooks();
      }
    }
  }

  private function init_queue() {
    $services = self::get_container()->findTaggedServiceIds('queue');
    foreach ($services as $service => $tags) {
      $this->from_container($service);
    }
  }

  public function plugin_activate() {
    $hooks = self::get_container()->findTaggedServiceIds('installable');
    foreach ($hooks as $hook => $tags) {
      $object = $this->from_container($hook);
      if ($object instanceof PluginInterface) {
        $object->plugin_activate();
      }
    }

  }

  public function plugin_deactivate() {
    //Деактивация компонентов плагина
    $hooks = self::get_container()->findTaggedServiceIds('installable');
    foreach ($hooks as $hook => $tags) {
      $object = $this->from_container($hook);
      if ($object instanceof PluginInterface) {
        $object->plugin_deactivate();
      }
    }
    //Остановка очередей
    //		$hooks = self::get_container()->findTaggedServiceIds( 'queue' );
    //		foreach ( $hooks as $hook => $tags ) {
    //			$object = $this->from_container( $hook );
    //			if ( $object instanceof QueueAbstract ) {
    //				$object->cancel_process();
    //			}
    //		}
  }

  static public function plugin_uninstall() {
    $hooks = self::get_container()->findTaggedServiceIds('installable');
    foreach ($hooks as $hook => $tags) {
      $object = self::get_container()->get($hook);
      if ($object instanceof PluginInterface) {
        $object->plugin_uninstall();
      }
    }
  }

  public function plugin_init() {
    $this->set_core_parameters();
    if (!SmartCAT::is_active() && user_access('administer entity translation')) {
      $notice = $this->from_container('core.notice');
      $notice->add_error(t('You must <a href="/admin/config/regional/translation_connectors">enter</a> API login and password', [], ['context' => 'translation_connectors']), FALSE);
    }

    $hooks = self::get_container()->findTaggedServiceIds('initable');
    foreach ($hooks as $hook => $tags) {
      $object = self::get_container()->get($hook);
      if ($object instanceof InitInterface) {
        $object->plugin_init();
      }
    }
  }

  private function set_core_parameters() {
    $options = $this->from_container('core.options');
    /** @var  ContainerInterface */
    $container = self::get_container();
    $container->setParameter('smartcat.api.login', $options->get_and_decrypt('smartcat_api_login'));
    $container->setParameter('smartcat.api.password', $options->get_and_decrypt('smartcat_api_password'));
    $container->setParameter('smartcat.api.server', $options->get('smartcat_api_server'));
  }
}

