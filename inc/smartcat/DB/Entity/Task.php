<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 17.07.2017
 * Time: 14:11
 */

namespace SmartCAT\Drupal\DB\Entity;


class Task {

  /** @var  integer */
  private $id;

  /** @var  string */
  private $source_language;

  /** @var  string[] */
  private $target_languages;

  /** @var  integer */
  private $entity_id;

  /** @var  string */
  private $entity_type;

  /** @var  string */
  private $status;

  /** @var  string */
  private $project_id = NULL;

  /**
   * @return int
   */
  public function get_id() {
    return $this->id;
  }

  /**
   * @param int $id
   *
   * @return Task
   */
  public function set_id($id) {
    $this->id = $id;

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
   * @return Task
   */
  public function set_source_language($source_language) {
    $this->source_language = $source_language;

    return $this;
  }

  /**
   * @return string[]
   */
  public function get_target_languages() {
    return $this->target_languages;
  }

  /**
   * @param string[] $target_languages
   *
   * @return Task
   */
  public function set_target_languages($target_languages) {
    $this->target_languages = $target_languages;

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
   * @return Task
   */
  public function set_entity_id($entity_id) {
    $this->entity_id = $entity_id;

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
   * @return Task
   */
  public function set_entity_type(string $entity_type) {
    $this->entity_type = $entity_type;
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
   * @return Task
   */
  public function set_status($status) {
    $this->status = $status;

    return $this;
  }

  /**
   * @return string|null
   */
  public function get_project_d() {
    return $this->project_id;
  }

  /**
   * @param string $project_id
   *
   * @return Task
   */
  public function set_project_id($project_id) {
    $this->project_id = $project_id;

    return $this;
  }
}