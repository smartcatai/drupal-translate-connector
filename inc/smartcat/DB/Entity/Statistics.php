<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 27.07.2017
 * Time: 15:39
 */

namespace SmartCAT\Drupal\DB\Entity;

class Statistics {

  /**
   * @return int
   */
  public function get_id() {
    return $this->id;
  }

  /**
   * @param int $id
   *
   * @return Statistics
   */
  public function set_id($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * @return string
   */
  public function get_entity_type() {
    return $this->entity_type;
  }

  /**
   * @param string $entity_type
   *
   * @return Statistics
   */
  public function set_entity_type(string $entity_type) {
    $this->entity_type = $entity_type;
    return $this;
  }

  /**
   * @return int
   */
  public function get_task_id() {
    return $this->task_id;
  }

  /**
   * @param int $task_id
   *
   * @return Statistics
   */
  public function set_task_id($task_id) {
    $this->task_id = $task_id;

    return $this;
  }

  /**
   * @return int
   */
  public function get_entity_id() {
    return $this->entity_id;
  }

  /**
   * @param int $entity_id
   *
   * @return Statistics
   */
  public function set_entity_id($entity_id) {
    $this->entity_id = $entity_id;

    return $this;
  }

  /**
   * @return string
   */
  public function get_source_language() {
    return $this->source_language;
  }

  /**
   * @param string $source_language
   *
   * @return Statistics
   */
  public function set_source_language($source_language) {
    $this->source_language = $source_language;

    return $this;
  }

  /**
   * @return string
   */
  public function get_target_language() {
    return $this->target_language;
  }

  /**
   * @param string $target_language
   *
   * @return Statistics
   */
  public function set_target_language($target_language) {
    $this->target_language = $target_language;

    return $this;
  }

  /**
   * @return float
   */
  public function get_progress() {
    return $this->progress;
  }

  /**
   * @param float $progress
   *
   * @return Statistics
   */
  public function set_progress($progress) {
    $this->progress = $progress;

    return $this;
  }

  /**
   * @return int
   */
  public function get_words_count() {
    return $this->words_count;
  }

  /**
   * @param int $words_count
   *
   * @return Statistics
   */
  public function set_words_count($words_count) {
    $this->words_count = $words_count;

    return $this;
  }

  /**
   * @return int
   */
  public function get_target_entityid() {
    return $this->target_entity_id;
  }

  /**
   * @param int $target_entity_id
   *
   * @return Statistics
   */
  public function set_target_entityid($target_entity_id) {
    $this->target_entity_id = $target_entity_id;

    return $this;
  }

  /**
   * @return string
   */
  public function get_document_id() {
    return $this->document_id;
  }

  /**
   * @param string $document_id
   *
   * @return Statistics
   */
  public function set_document_id($document_id) {
    $this->document_id = $document_id;

    return $this;
  }

  /**
   * @return string
   */
  public function get_status() {
    return $this->status;
  }

  /**
   * @param string $status
   *
   * @return Statistics
   */
  public function set_status($status) {
    $this->status = $status;

    return $this;
  }

  /** @var  integer */
  private $id;

  /** @var  integer */
  private $task_id;

  /** @var  integer */
  private $entity_id;

  /** @var  string */
  private $entity_type;

  /** @var  string */
  private $source_language;

  /** @var  string */
  private $target_language;

  /** @var  float */
  private $progress;

  /** @var  integer */
  private $words_count;

  /** @var  integer */
  private $target_entity_id;

  /** @var  string */
  private $document_id;

  /** @var  string */
  private $status;

  /** @var  integer */
  private $error_count;

  /**
   * @return int
   */
  public function get_error_count() {
    return $this->error_count;
  }

  /**
   * @param int $error_count
   *
   * @return Statistics
   */
  public function set_error_count($error_count) {
    $this->error_count = $error_count;

    return $this;
  }

  /**
   * @param int $inc
   *
   * @return Statistics
   */
  public function inc_error_count($inc = 1) {
    $this->set_error_count($this->get_error_count() + $inc);

    return $this;
  }

}