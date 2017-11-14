<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 27.07.2017
 * Time: 15:39
 */

namespace SmartCAT\Drupal\DB\Entity;

class Statistics implements \Serializable {

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
  public function get_target_entity_id() {
    return $this->target_entity_id;
  }

  /**
   * @param int $target_entity_id
   *
   * @return Statistics
   */
  public function set_target_entity_id($target_entity_id) {
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

  /**
   * String representation of object
   *
   * @link http://php.net/manual/en/serializable.serialize.php
   * @return string the string representation of the object or null
   * @since 5.1.0
   */
  public function serialize() {
    return serialize([
      'document_id' => $this->get_document_id(),
      'entity_id' => $this->get_entity_id(),
      'entity_type' => $this->get_entity_type(),
      'error_count' => $this->get_error_count(),
      'id' => $this->get_id(),
      'progress' => $this->get_progress(),
      'source_language' => $this->get_source_language(),
      'status' => $this->get_status(),
      'target_entity_id' => $this->get_target_entity_id(),
      'target_language' => $this->get_target_language(),
      'task_id' => $this->get_task_id(),
      'words_count' => $this->get_words_count(),
    ]);
  }

  /**
   * Constructs the object
   *
   * @link http://php.net/manual/en/serializable.unserialize.php
   *
   * @param string $serialized <p>
   * The string representation of the object.
   * </p>
   *
   * @return void
   * @since 5.1.0
   */
  public function unserialize($serialized) {
    $data = unserialize($serialized);
    if (isset($data['document_id'])) {
      $this->set_document_id($data['document_id']);
    }
    if (isset($data['entity_id'])) {
      $this->set_entity_id($data['entity_id']);
    }
    if (isset($data['entity_type'])) {
      $this->set_entity_type($data['entity_type']);
    }
    if (isset($data['error_count'])) {
      $this->set_error_count($data['error_count']);
    }
    if (isset($data['id'])) {
      $this->set_id($data['id']);
    }
    if (isset($data['progress'])) {
      $this->set_progress($data['progress']);
    }
    if (isset($data['source_language'])) {
      $this->set_source_language($data['source_language']);
    }
    if (isset($data['status'])) {
      $this->set_status($data['status']);
    }
    if (isset($data['target_entity_id'])) {
      $this->set_target_entity_id($data['target_entity_id']);
    }
    if (isset($data['target_language'])) {
      $this->set_target_language($data['target_language']);
    }
    if (isset($data['task_id'])) {
      $this->set_task_id($data['task_id']);
    }
    if (isset($data['words_count'])) {
      $this->set_words_count($data['words_count']);
    }
  }

  /**
   * @return null|string
   */
  public function get_localized_status_name() {
    if (!empty($this->status)) {
      switch ($this->status) {
        case 'new':
          $status = t('Submitted', [], ['context' => 'translation_connectors']);
          break;
        case 'sended':
        case 'export':
          $status = t('In progress', [], ['context' => 'translation_connectors']);
          break;
        case 'completed':
          $status = t('Completed', [], ['context' => 'translation_connectors']);
          break;
        default:
          $status = $this->status;
      }
      return $status;
    }
    return NULL;
  }
}