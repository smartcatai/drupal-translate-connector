<?php

namespace Drupal\smartcat_translation_manager\Api;

use \SmartCat\Client\SmartCat;

class ApiBaseAbstract
{
    /**
     * @param SmartCat $api
     */
    public function __construct(SmartCat $api)
    {
        $this->api = $api;
    }
}