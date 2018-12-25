<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:48
 */

namespace Smartcat\Drupal\DB\Repository;

use Smartcat\Drupal\DB\Entity\Project;


/** Репозиторий таблицы обмена */
class ProjectRepository extends RepositoryAbstract {

  const TABLE_NAME = 'projects';

  public function getTableName() {
    return self::TABLE_PREFIX . self::TABLE_NAME;
  }

  public function getSchema() {
    $table_name = $this->getTableName();
    $schema = [
      $table_name => [
        'fields' => [
          'id' => [
            'type' => 'serial',
            'size' => 'big',
            'unsigned' => TRUE,
            'not null' => TRUE,
          ],
          'name' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => FALSE,
          ],
          'entityId' => [
            'type' => 'int',
            'size' => 'big',
            'not null' => TRUE,
          ],
          'profileId' => [
            'type' => 'int',
            'size' => 'big',
            'not null' => TRUE,
          ],
          'status' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'externalProjectId' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => FALSE,
          ],
        ],
        'primary key' => ['id'],
      ],
    ];
    return $schema;
  }

  public function add(Profile $project) {
    $table_name = $this->getTableName();

    $data = [
      'entityId' => $project->getEntityId(),
      'profileId' => serialize($project->getProfileId()),
      'status' => $project->getStatus(),
    ];

    if($project->getName() === NULL){
      $data['name'] = $project->getName();
    }

    if($project->getExternalProjectId() === NULL){
      $data['externalProjectId'] = serialize($project->getExternalProjectId());
    }

    if (!empty($project->getId())) {
      $data['id'] = $project->getId();
    }

    $insert_id = FALSE;

    try {
      $insert_id = $this->connection->insert($table_name)
        ->fields($data)
        ->execute();
      $project->setId($insert_id);
    } catch (\Exception $e) {
      throw new \Exception('Table '.$table_name .': ' . $e->getMessage());
    }

    return $insert_id;
  }

  public function update(Project $project) {
    $table_name = $this->getTableName();

    if (!empty($project->getId())) {
      $data = [
        'entityId' => $project->getEntityId(),
        'profileId' => serialize($project->getProfileId()),
        'status' => $project->getStatus(),
      ];

      if($project->getName() === NULL){
        $data['name'] = $project->getName();
      }

      if($project->getExternalProjectId() === NULL){
        $data['externalProjectId'] = serialize($project->getExternalProjectId());
      }

      try {
        return $this->connection->update($table_name)
          ->fields($data)
          ->condition('id', $project->getId())
          ->execute();
      } catch (\Exception $e) {
      }
    }
    return FALSE;
  }

  protected function doFlush(array $persists) {
    /* @var Task[] $persists */
    foreach ($persists as $project) {
      if (get_class($project) === 'Smartcat\Drupal\DB\Entity\Project') {
        if (empty($project->getId())) {
          if ($res = $this->add($project)) {
            $project->setId($res);
          }
        }
        else {
          $this->update($project);
        }
      }
    }
  }

  protected function toEntity($row) {
    $result = new Project();

    if (isset($row->id)) {
      $result->setId(intval($row->id));
    }

    if (isset($row->name)) {
      $result->setName($row->name);
    }

    if (isset($row->entityId)) {
      $result->setEntityId($row->entityId);
    }

    if (isset($row->profileId)) {
      $result->setProfileId(unserialize($row->profileId));
    }

    if (isset($row->status)) {
      $result->setStatus($row->status);
    }

    if (isset($row->externalProjectId)) {
      $result->setExternalProjectId(unserialize($row->externalProjectId));
    }

    return $result;
  }
}