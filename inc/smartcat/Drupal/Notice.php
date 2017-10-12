<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 22.06.2017
 * Time: 12:00
 */

namespace SmartCAT\Drupal\Drupal;

class Notice {

  /**
   * Добавить уведомление об успешной операции
   *
   * @param string $message - сообщение
   */
  public function add_success($message) {
    drupal_set_message($message, 'status', false);
  }

  /**
   * Добавить уведомление-предупрежение
   *
   * @param string $message - сообщение
   */
  public function add_warning($message) {
    drupal_set_message($message, 'warning', false);
  }

  /**
   * Добавить уведомление об ошибки
   *
   * @param string $message - сообщение
   */
  public function add_error($message) {
    drupal_set_message($message, 'error', false);
  }
}