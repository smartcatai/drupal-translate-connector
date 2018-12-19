<?php

namespace Smartcat\Drupal\Api;

use \SmartCat\Client\SmartCat;

class Directory
{
    protected $directories = [];

    /**
     * @param SmartCat $api
     */
    public function __construct(SmartCat $api)
    {
        $this->api = $api;
    }

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