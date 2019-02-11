<?php

namespace Drupal\smartcat_translation_manager\Api;

use \SmartCat\Client\SmartCat;

class Directory extends ApiBaseAbstract
{
    protected $directories = [];

    public function get(string $type)
    {
        if(!array_key_exists($type, $this->directories)){
            $this->directories[$type] = $this->api->getDirectoriesManager()->directoriesGet(['type'=>$type]);
        }
        return $this->directories[$type];
    }

    public function getItemsAsArray(string $type)
    {
        $array = [];
        $items = $this->get($type)->getItems();
        foreach ($items as $item){
            $array[$item->getId()] = $item->getName();
        }
        return $array;
    }

}