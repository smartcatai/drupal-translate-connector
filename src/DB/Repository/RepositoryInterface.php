<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:27
 */

namespace Drupal\smartcat_translation_manager\DB\Repository;


interface RepositoryInterface {
	public function getTableName();

	public function getSchema();

	public function persist( $o );

	public function flush();
}