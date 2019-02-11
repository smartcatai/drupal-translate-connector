<?php 

namespace Drupal\smartcat_translation_manager\Helper;

use Drupal\smartcat_translation_manager\DB\Repository\ProfileRepository;
use Drupal\smartcat_translation_manager\DB\Repository\ProjectRepository;

class SchemaHelper
{
    protected static $repositoryList = [
        ProjectRepository::class,
    ];

    public static function getSchemas()
    {
        $schemas = [];
        foreach(self::$repositoryList as $classRepository){
            $repository = new $classRepository;
            $schemas = array_merge($repository->getSchema(), $schemas);
        }
        return $schemas;
    }
}