<?php 

namespace Smartcat\Drupal\Helper;

use Smartcat\Drupal\DB\Repository\ProfileRepository;

class SchemaHelper
{
    protected static $repositoryList = [
        ProfileRepository::class,
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