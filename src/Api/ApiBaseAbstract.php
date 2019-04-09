<?php

namespace Drupal\smartcat_translation_manager\Api;

use SmartCat\Client\SmartCat;

/**
 * Base API  .
 */
class ApiBaseAbstract {

  /**
   * @param \SmartCat\Client\SmartCat $api
   */
  public function __construct(SmartCat $api) {
    $this->api = $api;
  }

}
