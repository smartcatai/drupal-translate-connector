<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 24.10.2017
 * Time: 17:20
 */

namespace SmartCAT\Drupal\Drupal\Admin\Forms;


interface DrupalForm {
  public static function get_form($form, &$form_state, ...$params);

  public static function validate_form($form, &$form_state);

  public static function submit_form($form, &$form_state);

}