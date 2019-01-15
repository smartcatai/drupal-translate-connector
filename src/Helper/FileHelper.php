<?php

namespace Smartcat\Drupal\Helper;

class FileHelper
{
    const REPLACABLE = '%';
    const FIELD_OPEN_TAG = '<field id="%">';
    const FIELD_CLOSE_TAG = '</field>';

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param array $useFields ['title','body','comment']
     * @return string $dest
     */
    public function createFileByEntity($useFields = [])
    {
        $file = \file_save_data($this->generateHtmlMarkup($useFields));

        if(!$file){
            return '';
        }

        return \Drupal::service('file_system')->realpath($file->get('uri')->value);
    }

    /**
     * @return string
     */
    public function generateSourceFileName()
    {
        return $this->entity->getEntityTypeId() . '.' . $this->entity->bundle() . '.' . $this->entity->id() . '.html';
    }

    /**
     * @return string
     */
    public function generateTranslatedFileName($lang)
    {
        return 'translated-to-' . $lang . '.' . $this->generateSourceFileName();
    }

    /**
     * @return string
     */
    protected function generateHtmlMarkup($useFields)
    {
        $data = [];
        $fields = $this->entity->getFieldDefinitions();

        foreach($fields as $field){
            if(!empty($useFields) && !in_array($field->getName(), $useFields)){
                continue;
            }
            $data[] = str_replace(self::REPLACABLE, $field->getName(), self::FIELD_OPEN_TAG) 
                .$this->entity->get($field->getName())->value 
                .self::FIELD_CLOSE_TAG;
        }
        
        return '<html><head></head><body>' . implode('',$data) . '</body></html>';
    }
}