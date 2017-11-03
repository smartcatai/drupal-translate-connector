<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 19:20
 */

namespace SmartCAT\Drupal\DB\Repository;


abstract class RepositoryAbstract implements RepositoryInterface {

  protected $prefix = '';

  public function __construct($prefix) {
    $this->prefix = $prefix;
  }

  public function get_count() {
    $table_name = $this->get_table_name();
    $count = $this->get_wp_db()->get_var("SELECT COUNT(*) FROM $table_name");

    return $count;
  }

  private $persists = [];

  public function persist($o) {
    $this->persists[] = $o;
  }

  protected abstract function do_flush(array $persists);

  public function flush() {
    $this->do_flush($this->persists);
    $this->persists = [];
  }

  protected abstract function to_entity($row);

  protected function prepare_result($rows) {
    $result = [];
    foreach ($rows as $row) {
      $result[] = $this->to_entity($row);
    }

    return $result;
  }

}