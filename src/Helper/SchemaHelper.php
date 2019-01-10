<?php 

namespace Smartcat\Drupal\Helper;

use Smartcat\Drupal\DB\Repository\ProfileRepository;
use Smartcat\Drupal\DB\Repository\ProjectRepository;

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