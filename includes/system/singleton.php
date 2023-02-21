<?php

/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 28/12/2015
 * Time: 08:43
 */

namespace TEAMTALLY\System;


/**
 * Class Singleton
 * @package TEAMTALLY\System
 */

abstract class Singleton {

	/**
	 * Contains all data used by each instances.
	 *
	 * It is an associative array which is keyed by class names implementing this singleton function
	 *
	 * $_instances[class_name]['instance']            <-- instance of the singleton
	 *                        ['using_get_instance']  <-- prevent multiple execution of the *get_instance* method
	 * @var array
	 */
	private static $_instances = array();

	/**
	 * @var array containing the dynamic variables set by user
	 */
	private $data = array();

	/**
	 * Returns the content of a runtime defined property
	 *
	 * @param string $name
	 *
	 * @return mixed | null
	 */
	public function &__get( $name ) {

		$value = NULL;

		if ( isset( $this->data[ $name ] ) ) {
			$value = $this->data[ $name ];
		}

		return $value;
	}

	/**
	 * Defines a property at runtime
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set( $name, $value ) {
		$this->data[ $name ] = $value;
	}

	/**
	 * Checks if a property exists or not
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return isset( $this->data[ $name ] );
	}

	/**
	 * Unsets a runtime defined property
	 *
	 * @param string $name
	 */
	public function __unset( $name ) {
		if ( isset( $this->data[ $name ] ) ) {
			unset( $this->data[ $name ] );
		}
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return $this The *Singleton* instance.
	 */
	public static function get_instance() {

		// checks multiple execution of this method due to the 'init' method
		$class_name = get_called_class();
		$instance   = &self::$_instances[ $class_name ];

		$using_get_instance = Helper::get_var( $instance['using_get_instance'], FALSE );

		// returns the instance if it already exists
		if ( $using_get_instance ) {
			return $instance['instance'];
		}

		$instance['using_get_instance'] = TRUE;

		if ( ! isset( $instance['instance'] ) ) {

			$instance['instance'] = new static();

			// execution of initialisation method
			if ( method_exists( $instance['instance'], 'init' ) ) {
				$instance['instance']->init();
			}

		}

		$instance['using_get_instance'] = FALSE;

		return $instance['instance'];

	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {
		// do nothing
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
		// do nothing
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
		// do nothing
	}
}