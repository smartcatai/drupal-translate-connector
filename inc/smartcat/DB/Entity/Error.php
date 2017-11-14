<?php
/**
 * Created by PhpStorm.
 * User: Diversant_
 * Date: 09.08.2017
 * Time: 15:28
 */

namespace SmartCAT\Drupal\DB\Entity;


class Error {
	/** @var  integer */
	private $id;

	/** @var  \DateTime */
	private $date;

	/** @var  string */
	private $type;

	/** @var  string */
	private $short_message;

	/** @var  string */
	private $message;

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return Error
	 */
	public function set_id( int $id ): Error {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return Error
	 */
	public function set_date( \DateTime $date ): Error {
		$this->date = $date;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return Error
	 */
	public function set_type( string $type ): Error {
		$this->type = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_short_message() {
		return $this->short_message;
	}

	/**
	 * @param string $short_message
	 *
	 * @return Error
	 */
	public function set_short_message( string $short_message ): Error {
		$this->short_message = $short_message;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * @param string $message
	 *
	 * @return Error
	 */
	public function set_message( string $message ): Error {
		$this->message = $message;

		return $this;
	}

}