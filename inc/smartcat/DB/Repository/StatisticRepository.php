<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:48
 */

namespace SmartCAT\Drupal\DB\Repository;

use Psr\Container\ContainerInterface;
use SmartCAT\API\Model\DocumentModel;
use SmartCAT\Drupal\Connector;
use SmartCAT\Drupal\DB\Entity\Statistics;
use SmartCAT\Drupal\DB\Entity\Task;
use SmartCAT\Drupal\Helpers\Language\LanguageConverter;


/** Репозиторий таблицы статистики */
class StatisticRepository extends RepositoryAbstract {

  const TABLE_NAME = 'statistic';

  public function get_table_name() {
    return $this->prefix . self::TABLE_NAME;
  }

  public function get_schema() {
    $table_name = $this->get_table_name();
    $schema = [
      $table_name => [
        'fields' => [
          'id' => [
            'type' => 'serial',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => TRUE,
          ],
          'taskId' => [
            'type' => 'int',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => TRUE,
          ],
          'entityID' => [
            'type' => 'int',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => TRUE,
          ],
          'entityType' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'sourceLanguage' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'targetLanguage' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'progress' => [
            'type' => 'numeric',
            'size' => 'normal',
            'not null' => TRUE,
            'default' => 0,
            'precision' => 10,
            'scale' => 2,
          ],
          'wordsCount' => [
            'type' => 'int',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => FALSE,
          ],
          'targetEntityID' => [
            'type' => 'int',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => FALSE,
          ],
          'documentID' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => FALSE,
          ],
          'status' => [
            'type' => 'varchar',
            'length' => 20,
            'not null' => TRUE,
            'default' => 'new',
          ],
          'errorCount' => [
            'type' => 'int',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => FALSE,
            'default' => 0,
          ],
        ],
        'primary key' => ['id'],
        'indexes' => [
          'documentID' => ['documentID'],
          'filter' => ['sourceLanguage', 'targetLanguage', 'status'],
        ],
      ],
    ];
    return $schema;
  }

  /**
   * @param $status
   *
   * @return array
   */
  private function convert_localized_status($status){
    switch ($status){
      case t('Submitted', [], ['context' => 'translation_connectors']):
        return ['new'];
        break;
      case t('In progress', [], ['context' => 'translation_connectors']):
        return ['sended', 'export'];
        break;
      case t('Completed', [], ['context' => 'translation_connectors']):
        return ['completed'];
        break;
      default:
        return [$status];
    }
  }
  /**
   * @param int $from
   * @param int $limit
   *
   * @return Statistics[]
   */
  public function get_statistics($header, $filters = []) {
    //    $from = intval($from);
    //    $from >= 0 || $from = 0;
    //    $limit = intval($limit);

    $query = db_select("{$this->get_table_name()}", "s")
      ->fields('s');
    if (count($filters) > 0) {
      foreach ($filters as $key => $value) {
        if ($key != 'status') {
          $query->condition($key, $value);
        } else {
          $statuses = $this->convert_localized_status($value);
          $condition = db_or();
          foreach ($statuses as $status) {
            $condition->condition('status', $status);
          }
          $query->condition($condition);
        }
      }
    }
    $query->extend('PagerDefault')
      ->limit(100)
      ->extend('TableSort')
      ->orderByHeader($header);
    //      ->range($from, $limit);

    $results = $query->execute()->fetchAll();

    return $this->prepare_result($results);
  }

  /**
   * Возращает список постов ожидающих перевода
   *
   * @param array $documents = [] - если передан параметр то из списка
   *   исключаются все докумениты не попавшие в массив
   *
   * @return Statistics[]
   */
  public function get_sended(array $documents = []) {
    $query = db_select("{$this->get_table_name()}", "s")
      ->condition('s.status', 'sended')
      ->fields('s');

    $documents_count = count($documents);
    if ($documents_count > 0) {
      $query->condition('s.documentID', $documents, 'IN');
    }

    $results = $query->execute()->fetchAll();

    return $this->prepare_result($results);
  }

  public function add(Statistics $stat) {
    $data = [
      'taskId' => $stat->get_task_id(),
      'entityID' => $stat->get_entity_id(),
      'entityType' => $stat->get_entity_type(),
      'sourceLanguage' => $stat->get_source_language(),
      'targetLanguage' => $stat->get_target_language(),
      'progress' => $stat->get_progress(),
      'wordsCount' => $stat->get_words_count(),
      'targetEntityID' => $stat->get_target_entity_id(),
      'documentID' => $stat->get_document_id(),
      'status' => $stat->get_status(),
      'errorCount' => $stat->get_error_count(),
    ];

    if (!empty($stat->get_id())) {
      $data['id'] = $stat->get_id();
    }

    //TODO: м.б. заменить на try-catch
    try {
      if ($id = db_insert($this->get_table_name())->fields($data)->execute()) {
        $stat->set_id($id);
        return $id;
      }
    } catch (\Exception $e) {

    }

    return FALSE;
  }

  public function update(Statistics $stat) {
    $data = [
      'taskId' => $stat->get_task_id(),
      'entityID' => $stat->get_entity_id(),
      'entityType' => $stat->get_entity_type(),
      'sourceLanguage' => $stat->get_source_language(),
      'targetLanguage' => $stat->get_target_language(),
      'progress' => $stat->get_progress(),
      'wordsCount' => $stat->get_words_count(),
      'targetEntityID' => $stat->get_target_entity_id(),
      'documentID' => $stat->get_document_id(),
      'status' => $stat->get_status(),
      'errorCount' => $stat->get_error_count(),
    ];

    if (!empty($stat->get_id())) {
      //TODO: м.б. заменить на try-catch
      db_update($this->get_table_name())
        ->fields($data)
        ->condition('id', $stat->get_id())
        ->execute();
      return TRUE;
    }

    return FALSE;
  }

  public function delete_by_entity_id($entity_id) {
    if (!is_null($entity_id) && !empty($entity_id)) {
      return db_delete($this->get_table_name())
        ->condition('entityID', $entity_id)
        ->execute();
    }

    return FALSE;
  }

  public function delete(Statistics $stat) {
    if (!empty($stat->get_id())) {
      //TODO: м.б. заменить на try-catch
      return db_delete($this->get_table_name())
        ->condition('id', $stat->get_id())
        ->execute();
    }

    return FALSE;
  }

  protected function to_entity($row) {
    $result = new Statistics();

    if (isset($row->id)) {
      $result->set_id($row->id);
    }

    if (isset($row->taskId)) {
      $result->set_task_id($row->taskId);
    }

    if (isset($row->entityID)) {
      $result->set_entity_id($row->entityID);
    }

    if (isset($row->entityType)) {
      $result->set_entity_type($row->entityType);
    }

    if (isset($row->sourceLanguage)) {
      $result->set_source_language($row->sourceLanguage);
    }

    if (isset($row->targetLanguage)) {
      $result->set_target_language($row->targetLanguage);
    }

    if (isset($row->progress)) {
      $result->set_progress($row->progress);
    }

    if (isset($row->wordsCount)) {
      $result->set_words_count($row->wordsCount);
    }

    if (isset($row->targetEntityID)) {
      $result->set_target_entity_id($row->targetEntityID);
    }

    if (isset($row->documentID)) {
      $result->set_document_id($row->documentID);
    }

    if (isset($row->status)) {
      $result->set_status($row->status);
    }

    if (isset($row->errorCount)) {
      $result->set_error_count($row->errorCount);
    }

    return $result;
  }

  protected function do_flush(array $persists) {
    /* @var Statistics[] $persists */
    foreach ($persists as $stat) {
      if (get_class($stat) === 'SmartCAT\Drupal\DB\Entity\Statistics') {
        if (empty($stat->get_id())) {
          if ($res = $this->add($stat)) {
            $stat->set_id($res);
          }
        }
        else {
          $this->update($stat);
        }
      }
    }
  }

  public function link_to_smartcat_document(Task $task, DocumentModel $document) {
    /** @var ContainerInterface $container */
    $container = Connector::get_container();

    /** @var LanguageConverter $converter */
    $converter = $container->get('language.converter');

    $table_name = $this->get_table_name();
    $data = [
      'documentID' => $document->getId(),
      'status' => 'sended',
    ];

    try {
      return db_update($table_name)
        ->fields($data)
        ->condition('taskId', $task->get_id())
        ->condition('targetLanguage', $converter->get_drupal_code_by_sc($document->getTargetLanguage())
          ->get_drupal_code())
        ->execute();
    } catch (\Exception $e) {

    }

    return FALSE;
  }

  /**
   * @param array $criterias
   *
   * @return Statistics[]|null
   */
  public function get_by(array $criterias) {
    $table_name = $this->get_table_name();
    $query = db_select($table_name, 's')
      ->fields('s');

    foreach ($criterias as $key => $value) {
      $query->condition($key, $value);
    }

    $result = $query->execute()->fetchAll();
    return $result ? $this->prepare_result($result) : NULL;
  }

  public function get_statuses_list() {
    $table_name = $this->get_table_name();
    $query = db_select($table_name, 's')
      ->fields('s', ['status'])
      ->groupBy('status');
    $result = $query->execute()->fetchCol();
    return $result;
  }

  public function get_localized_statuses_list() {
    $results = $this->get_statuses_list();
    $res = [NULL => NULL];
    $s = new Statistics();
    foreach ($results as $result) {
      $s->set_status($result);
      $val = $s->get_localized_status_name();
      $res[$val] = $val;
    }
    return $res;
  }
}