<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:48
 */

namespace SmartCAT\Drupal\DB\Repository;

use SmartCAT\Drupal\DB\Entity\Task;


/** Репозиторий таблицы обмена */
class TaskRepository extends RepositoryAbstract {

  const TABLE_NAME = 'tasks';

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
          'sourceLanguage' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'targetLanguages' => ['type' => 'text', 'not null' => TRUE],
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
          'status' => [
            'type' => 'varchar',
            'length' => 20,
            'not null' => TRUE,
            'default' => 'new',
          ],
          'projectID' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => FALSE,
          ],
        ],
        'primary key' => ['id'],
        'indexes' => [
          'status' => ['status'],
        ],
      ],
    ];
    return $schema;
  }

  /**
   * @return Task[]
   */
  public function get_new_task() {
    $table_name = $this->get_table_name();
    $results = db_select($table_name, 't')
      ->fields('t')
      ->condition('t.status', 'new')
      ->execute()
      ->fetchAll();

    return $this->prepare_result($results);
  }

  public function add(Task $task) {
    $table_name = $this->get_table_name();

    $data = [
      'sourceLanguage' => $task->get_source_language(),
      'targetLanguages' => serialize($task->get_target_languages()),
      'status' => $task->get_status(),
      'projectID' => $task->get_project_d(),
      'entityID' => $task->get_entity_id(),
      'entityType' => $task->get_entity_type(),
    ];

    if (!empty($task->get_id())) {
      $data['id'] = $task->get_id();
    }

    $insert_id = FALSE;

    try {
      $insert_id = db_insert($table_name)
        ->fields($data)
        ->execute();
      $task->set_id($insert_id);
    } catch (\Exception $e) {
    }

    return $insert_id;
  }

  public function update(Task $task) {
    $table_name = $this->get_table_name();

    if (!empty($task->get_id())) {
      $data = [
        'sourceLanguage' => $task->get_source_language(),
        'targetLanguages' => serialize($task->get_target_languages()),
        'status' => $task->get_status(),
        'projectID' => $task->get_project_d(),
        'entityID' => $task->get_entity_id(),
        'entityType' => $task->get_entity_type(),
      ];

      try {
        return db_update($table_name)
          ->fields($data)
          ->condition('id', $task->get_id())
          ->execute();
      } catch (\Exception $e) {
      }
    }
    return FALSE;
  }

  protected function do_flush(array $persists) {
    /* @var Task[] $persists */
    foreach ($persists as $task) {
      if (get_class($task) === 'SmartCAT\Drupal\DB\Entity\Task') {
        if (empty($task->get_id())) {
          if ($res = $this->add($task)) {
            $task->set_id($res);
          }
        }
        else {
          $this->update($task);
        }
      }
    }
  }

  protected function to_entity($row) {
    $result = new Task();

    if (isset($row->id)) {
      $result->set_id(intval($row->id));
    }

    if (isset($row->sourceLanguage)) {
      $result->set_source_language($row->sourceLanguage);
    }

    if (isset($row->targetLanguages)) {
      $result->set_target_languages(unserialize($row->targetLanguages));
    }

    if (isset($row->entityID)) {
      $result->set_entity_id(intval($row->entityID));
    }

    if (isset($row->entityType)) {
      $result->set_entity_type(intval($row->entityType));
    }

    if (isset($row->status)) {
      $result->set_status($row->status);
    }

    if (isset($row->projectID)) {
      $result->set_project_id($row->projectID);
    }

    return $result;
  }
}