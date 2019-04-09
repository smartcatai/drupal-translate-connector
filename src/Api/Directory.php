<?php

namespace Drupal\smartcat_translation_manager\Api;

/**
 * Part facade API  for work with derectory resource.
 */
class Directory extends ApiBaseAbstract {
  protected $directories = [];

  /**
   * Getting any directory.
   *
   * @param string $type
   *
   * @return \SmartCat\Client\Model\DirectoryModel
   */
  public function get(string $type) {
    if (!array_key_exists($type, $this->directories)) {
      $this->directories[$type] = $this->api->getDirectoriesManager()->directoriesGet(['type' => $type]);
    }
    return $this->directories[$type];
  }

  /**
   * Getting any directory and transform array DirectoryItemModel[] to array id=>name.
   *
   * @param string $type
   *
   * @return array
   */
  public function getItemsAsArray(string $type) {
    $array = [];
    $items = $this->get($type)->getItems();
    foreach ($items as $item) {
      $array[$item->getId()] = $item->getName();
    }
    return $array;
  }

}
