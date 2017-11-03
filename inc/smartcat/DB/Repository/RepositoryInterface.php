<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 16.06.2017
 * Time: 18:27
 */

namespace SmartCAT\Drupal\DB\Repository;


interface RepositoryInterface {
	/**
	 * RepositoryInterface constructor.
	 *
	 * @param string $prefix
	 */
	public function __construct( $prefix );

	public function get_table_name();

	public function get_schema();

	public function persist( $o );

	public function flush();
}