<?php

namespace SmartCAT\Drupal\Helpers\Language;

//фактически выходит - relations
final class LanguageEntity {
	private $drupal_name;
	private $sc_name;
	private $drupal_code;
	private $sc_code;

	/**
	 * @return mixed
	 */
	public function get_drupal_name() {
		return $this->drupal_name;
	}

	/**
	 * @param mixed $drupal_name
	 *
	 * @return LanguageEntity
	 */
	public function set_drupal_name($drupal_name ) {
		$this->drupal_name = $drupal_name;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function get_sc_name() {
		return $this->sc_name;
	}

	/**
	 * @param mixed $sc_name
	 *
	 * @return LanguageEntity
	 */
	public function set_sc_name( $sc_name ) {
		$this->sc_name = $sc_name;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function get_drupal_code() {
		return $this->drupal_code;
	}

	/**
	 * @param mixed $drupal_code
	 *
	 * @return LanguageEntity
	 */
	public function set_drupal_code($drupal_code ) {
		$this->drupal_code = $drupal_code;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function get_sc_code() {
		return $this->sc_code;
	}

	/**
	 * @param mixed $sc_code
	 *
	 * @return LanguageEntity
	 */
	public function set_sc_code( $sc_code ) {
		$this->sc_code = $sc_code;

		return $this;
	}

	public function __construct($drupal_code, $sc_code, $drupal_name, $sc_name = null ) {
		$this->set_sc_code( $sc_code );
		$this->set_drupal_code( $drupal_code );
		$this->set_drupal_name( $drupal_name );
		$this->set_sc_name( ! is_null( $sc_name ) ? $sc_name : $drupal_name );
	}
}