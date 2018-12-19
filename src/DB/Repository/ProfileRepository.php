<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:48
 */

namespace Smartcat\Drupal\DB\Repository;

use Smartcat\Drupal\DB\Entity\Profile;


/** Репозиторий таблицы обмена */
class ProfileRepository extends RepositoryAbstract {

  const TABLE_NAME = 'profiles';

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
            'not null' => TRUE,
          ],
          'sourceLanguage' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'targetLanguages' => ['type' => 'text', 'not null' => TRUE],
          'vendor' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'workflowStages' => ['type' => 'text', 'not null' => TRUE],
          'entityType' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'autoPublish' => [
            'type' => 'int',
            'size' => 'tiny',
            'unsigned' => TRUE,
            'not null' => TRUE,
          ],
          'autoTranslate' => [
            'type' => 'int',
            'size' => 'tiny',
            'unsigned' => TRUE,
            'not null' => TRUE,
          ],
        ],
        'primary key' => ['id'],
      ],
    ];
    return $schema;
  }

  public function add(Profile $profile) {
    $table_name = $this->getTableName();

    $data = [
      'name' => $profile->getName(),
      'sourceLanguage' => $profile->getSourceLanguage(),
      'targetLanguages' => serialize($profile->getTargetLanguages()),
      'vendor' => $profile->getVendor(),
      'workflowStages' => serialize($profile->getWorkflowStages()),
      'entityType' => $profile->getEntityType(),
      'autoPublish' => $profile->getAutoPublish() ? 1 : 0,
      'autoTranslate' => $profile->getAutoTranslate() ? 1 : 0,
    ];

    if (!empty($profile->getId())) {
      $data['id'] = $profile->getId();
    }

    $insert_id = FALSE;

    try {
      $insert_id = $this->connection->insert($table_name)
        ->fields($data)
        ->execute();
      $profile->setId($insert_id);
    } catch (\Exception $e) {
      throw new \Exception('Table '.$table_name .': ' . $e->getMessage());
    }

    return $insert_id;
  }

  public function update(Profile $profile) {
    $table_name = $this->getTableName();

    if (!empty($profile->getId())) {
      $data = [
        'name' => $profile->getName(),
        'sourceLanguage' => $profile->getSourceLanguage(),
        'targetLanguages' => serialize($profile->getTargetLanguages()),
        'vendor' => $profile->getVendor(),
        'workflowStages' => serialize($profile->getWorkflowStages()),
        'entityType' => $profile->getEntityType(),
        'autoPublish' => $profile->getAutoPublish() ? 1 : 0,
        'autoTranslate' => $profile->getAutoTranslate() ? 1 : 0,
      ];

      try {
        return $this->connection->update($table_name)
          ->fields($data)
          ->condition('id', $profile->getId())
          ->execute();
      } catch (\Exception $e) {
      }
    }
    return FALSE;
  }

  protected function doFlush(array $persists) {
    /* @var Task[] $persists */
    foreach ($persists as $project) {
      if (get_class($project) === 'Smartcat\Drupal\DB\Entity\Profile') {
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
    $result = new Profile();

    if (isset($row->id)) {
      $result->setId(intval($row->id));
    }

    if (isset($row->name)) {
      $result->setName($row->name);
    }

    if (isset($row->sourceLanguage)) {
      $result->setSourceLanguage($row->sourceLanguage);
    }

    if (isset($row->targetLanguages)) {
      $result->setTargetLanguages(unserialize($row->targetLanguages));
    }

    if (isset($row->vendor)) {
      $result->setVendor($row->vendor);
    }

    if (isset($row->workflowStages)) {
      $result->setWorkflowStages(unserialize($row->workflowStages));
    }

    if (isset($row->entityType)) {
      $result->setEntityType($row->entityType);
    }

    if (isset($row->autoPublish)) {
      $result->setAutoPublish($row->autoPublish);
    }

    if (isset($row->autoTranslate)) {
      $result->setAutoTranslate($row->autoTranslate);
    }

    return $result;
  }
}