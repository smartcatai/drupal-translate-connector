<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:48
 */

namespace Drupal\smartcat_translation_manager\DB\Repository;

use Drupal\smartcat_translation_manager\DB\Entity\Document;


/** Репозиторий таблицы обмена */
class DocumentRepository extends RepositoryAbstract {

  const TABLE_NAME = 'documents';

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
          'entityTypeId' => [
            'type' => 'varchar',
            'length' => 100,
            'not null' => TRUE,
          ]
          ,'sourceLanguage' => [
            'type' => 'varchar',
            'length' => 100,
            'not null' => TRUE,
          ],
          'targetLanguage' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'status' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
          ],
          'externalExportId' => [
            'type' => 'varchar',
            'length' => 255,
            'not null' => FALSE,
          ],
          'externalProjectId' => [
            'type' => 'varchar',
            'length' => 100,
            'not null' => TRUE,
          ],
          'externalDocumentId' => [
            'type' => 'varchar',
            'length' => 100,
            'not null' => TRUE,
          ],
        ],
        'primary key' => ['id'],
      ],
    ];
    return $schema;
  }

  public function add(Document $document) {
    $table_name = $this->getTableName();

    $data = [
      'entityId' => $document->getEntityId(),
      'entityTypeId' => $document->getEntityTypeId(),
      'sourceLanguage' => $document->getSourceLanguage(),
      'targetLanguage' => $document->getTargetLanguage(),
      'status' => $document->getStatus(),
      'externalProjectId' => $document->getExternalProjectId(),
      'externalDocumentId' => $document->getExternalDocumentId(),
    ];

    if($document->getName() !== NULL){
      $data['name'] = $document->getName();
    }

    if($document->getExternalExportId() !== NULL){
      $data['externalExportId'] = $document->getExternalExportId();
    }

    if (!empty($document->getId())) {
      $data['id'] = $document->getId();
    }

    $insert_id = FALSE;

    try {
      $insert_id = $this->connection->insert($table_name)
        ->fields($data)
        ->execute();
      $document->setId($insert_id);
    } catch (\Exception $e) {
      throw new \Exception('Table '.$table_name .': ' . $e->getMessage());
    }

    return $insert_id;
  }

  public function delete($documentId)
  {
    return $this->connection->delete($this->getTableName())
      ->condition('id', $documentId)
      ->execute();
  }

  public function update(Document $document) {
    $table_name = $this->getTableName();

    if (!empty($document->getId())) {
      $data = [
        'entityId' => $document->getEntityId(),
        'entityTypeId' => $document->getEntityTypeId(),
        'externalProjectId' => $document->getExternalProjectId(),
        'externalDocumentId' => $document->getExternalDocumentId(),
        'sourceLanguage' => $document->getSourceLanguage(),
        'targetLanguage' => $document->getTargetLanguage(),
        'status' => $document->getStatus(),
      ];

      if($document->getName() !== NULL){
        $data['name'] = $document->getName();
      }

      if($document->getExternalExportId() !== NULL){
        $data['externalExportId'] = $document->getExternalExportId();
      }

      try {
        return $this->connection->update($table_name)
          ->fields($data)
          ->condition('id', $document->getId())
          ->execute();
      } catch (\Exception $e) {
        var_dump($e->getMessage());
        die;
      }
    }
    return FALSE;
  }

  protected function doFlush(array $persists) {
    /* @var Project[] $persists */
    foreach ($persists as $document) {
      if (get_class($document) === 'Drupal\smartcat_translation_manager\DB\Entity\Document') {
        if (empty($document->getId())) {
          if ($res = $this->add($document)) {
            $document->setId($res);
          }
        }
        else {
          $this->update($document);
        }
      }
    }
  }

  protected function toEntity($row) {
    $result = new Document();

    if (isset($row->id)) {
      $result->setId(intval($row->id));
    }

    if (isset($row->name)) {
      $result->setName($row->name);
    }

    if (isset($row->entityId)) {
      $result->setEntityId($row->entityId);
    }

    if (isset($row->entityTypeId)) {
      $result->setEntityTypeId($row->entityTypeId);
    }

    if (isset($row->externalProjectId)) {
      $result->setExternalProjectId($row->externalProjectId);
    }

    if (isset($row->externalDocumentId)) {
      $result->setExternalDocumentId($row->externalDocumentId);
    }

    if (isset($row->sourceLanguage)) {
      $result->setSourceLanguage($row->sourceLanguage);
    }

    if (isset($row->targetLanguage)) {
      $result->setTargetLanguage($row->targetLanguage);
    }

    if (isset($row->status)) {
      $result->setStatus($row->status);
    }

    if (isset($row->externalExportId)) {
      $result->setExternalExportId($row->externalExportId);
    }

    return $result;
  }
}