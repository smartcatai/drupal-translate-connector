<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 20.06.2017
 * Time: 0:15
 */

namespace SmartCAT\Drupal\Drupal;

use SmartCAT\Drupal\Connector;

class Options implements PluginInterface {

  static private $options_list = NULL;

  private $prefix;

  /** @var  \SmartCAT\Drupal\Helpers\Cryptographer */
  private $cryptographer;

  public function __construct($prefix) {
    $this->prefix = $prefix;
    if (self::$options_list === NULL) {
      $options = Connector::get_container()->getParameter('plugin.options');
      $this->cryptographer = Connector::get_container()->get('cryptographer');
      self::$options_list = [];
      foreach ($options as $option) {
        self::$options_list[$this->prefix . $option] = $this->prefix . $option;
      }
    }
  }

  public function plugin_activate() {

  }

  public function plugin_deactivate() {

  }

  public function plugin_uninstall() {
    foreach (self::$options_list as $option) {
      variable_del($option);
    }
  }

  /**
   * Получает значение опции
   *
   * @param string $name
   *
   * @return mixed|bool
   */
  public function get($name) {
    $systemName = "{$this->prefix}{$name}";
    //TODO: избавиться от ассертов
    assert(isset(self::$options_list[$systemName]), "Неизвестная опция $name. Добавьте ее в plugin.options");

    return variable_get($systemName);
  }

  /**
   * Сохраняет значение опции
   *
   * @param string $name
   * @param mixed $value
   *
   * @return bool
   */
  public function set($name, $value) {
    $systemName = "{$this->prefix}{$name}";
    assert(isset(self::$options_list[$systemName]), "Неизвестная опция $name. Добавьте ее в plugin.options");

    return variable_set($systemName, $value);
  }

  public function encrypt_AES($txt) {
    $cryptographer = $this->cryptographer;

    return $cryptographer::encrypt($txt);
  }

  public function decrypt_AES($txt) {
    $cryptographer = $this->cryptographer;

    return $cryptographer::decrypt($txt);
  }

  /**
   * Получение значение зашифрованной текстовой опции
   *
   * @param $name
   *
   * @return string
   */
  public function get_and_decrypt($name) {
    return $this->decrypt_AES($this->get($name));
  }

  /**
   * Зашифровывает и сохраняет значение текстовой опции
   *
   * @param string $name
   * @param string $value
   *
   * @return bool
   */
  public function set_and_encrypt($name, $value) {
    return $this->set($name, $this->encrypt_AES($value));
  }
}