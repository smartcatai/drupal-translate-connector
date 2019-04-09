<?php

namespace Drupal\smartcat_translation_manager\DB\Repository;

/**
 *
 */
interface RepositoryInterface {

  /**
   *
   */
  public function getTableName();

  /**
   *
   */
  public function getSchema();

  /**
   *
   */
  public function persist($o);

  /**
   *
   */
  public function flush();

}
