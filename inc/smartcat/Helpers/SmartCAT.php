<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 20.06.2017
 * Time: 20:13
 */

namespace SmartCAT\Drupal\Helpers;


use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\Drupal\Options;

class SmartCAT extends \SmartCAT\API\SmartCAT {

  /**
   * Проверяет можно ли использовать АПИ. Имеются ли сохраненые в настройках
   * данные для доступа к АПИ
   */
  public static function is_active() {
    $container = Connector::get_container();
    $login = $container->getParameter('smartcat.api.login');
    $password = $container->getParameter('smartcat.api.password');
    $server = $container->getParameter('smartcat.api.server');

    return !empty($login) && !empty($password) && !empty($server);
  }

  public static function filter_chars($s) {
    return str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], '_', $s);
  }

  public static function get_document_edit_path($document_id){
    $container = Connector::get_container();
    $ids = explode('_',$document_id);
    /** @var Options $options */
    $options = $container->get('core.options');
    return "https://{$options->get('smartcat_api_server')}/editor?DocumentId={$ids[0]}&LanguageId={$ids[1]}";
  }
}