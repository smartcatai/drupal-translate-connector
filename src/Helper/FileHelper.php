<?php

namespace Drupal\smartcat_translation_manager\Helper;

use Drupal\Core\Entity\EntityInterface;
/**
 *
 */
class FileHelper {
  const FIELD_TAG = '<field id="%s">%s</field>';

  /**
   * @param EntityInterface $entity
   */
  public function __construct($entity) {
    $this->entity = $entity;
  }

  /**
   * @param array $useFields
   *   ['title','body','comment'].
   * @return string $dest
   */
  public function createFileByEntity($useFields = []) {
    $file = \file_save_data($this->generateHtmlMarkup($useFields));

    if (!$file) {
      return '';
    }

    return \Drupal::service('file_system')->realpath($file->get('uri')->value);
  }

  /**
   * @return string
   */
  public function generateSourceFileName() {
    return $this->entity->getEntityTypeId() . '.' . $this->entity->bundle() . '.' . $this->entity->id() . '.html';
  }

  /**
   * @return string
   */
  public function generateTranslatedFileName($lang) {
    return 'translated-to-' . $lang . '.' . $this->generateSourceFileName();
  }

  /**
   * Method generate markup by entity
   * @param array
   * @return string
   */
  protected function generateHtmlMarkup($useFields) {
    $data = [];
    $fields = $this->entity->getFieldDefinitions();

    foreach ($fields as $field) {
      if (!$field->isTranslatable()) {
        continue;
      }
      if (!empty($useFields) && !in_array($field->getName(), $useFields)) {
        continue;
      }
      $data[] = sprintf(self::FIELD_TAG, $field->getName(), $this->entity->get($field->getName())->value);
      if ($field->getName() === 'body') {
        $data[] = sprintf(self::FIELD_TAG, $field->getName() . '_summary', $this->entity->get($field->getName())->summary);
      }
    }

    return '<html><head></head><body>' . implode('', $data) . '</body></html>';
  }

  /**
   * Create or update entity translation
   * @param string $content
   * @param string $langcode
   * @return EntityInterface
   */
  public function markupToEntityTranslation($content, $langcode) {
    $fieldPattern = str_replace('/', '\/', sprintf(self::FIELD_TAG, '(.+?)', '(.*?)'));

    $matches = [];

    preg_match_all('/' . $fieldPattern . '/is', $content, $matches);

    if ($this->hasTranslation($langcode)) {
      $entity_translation = $this->entity->getTranslation($langcode);
    }
    else {
      $entity_translation = $this->entity->addTranslation($langcode, $this->entity->toArray());
    }

    foreach ($matches[1] as $i => $field) {
      $value = $matches[2][$i];

      if ($field === 'body' || $field === 'comment_body') {
        $value = [
          'value' => $value,
          'format' => $entity_translation->get($field)->format,
        ];
      }
      else {
        $value = $this->specialcharsDecode($value);
      }

      if ($field === 'body_summary') {
        $field = 'body';
        $value = [
          'value' => $entity_translation->get($field)->value,
          'summary' => $value,
          'format' => $entity_translation->get($field)->format,
        ];
      }

      try {
        $entity_translation->set($field, $value);
      }
      catch (\Exception $e) {
        throw new \Exception("field = $field, value = $value, error = {$e->getMessage()}");
      }
    }

    return $entity_translation;
  }

  /**
   * Decode unicode special char
   * @param string $str
   * @return string
   */
  public function specialcharsDecode($str) {
    $str = html_entity_decode($str);
    $str = str_replace('&#39;', "'", $str);
    return $str;
  }

  /**
   * Check existing translation
   * @param string $langcode
   * @return boolean
   */
  public function hasTranslation($langcode) {
    $existing_translation = \Drupal::service('entity.repository')->getTranslationFromContext($this->entity, $langcode);
    return ($existing_translation->langcode->value === $langcode) ? TRUE : FALSE;
  }

}
