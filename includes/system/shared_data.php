<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 13/03/2023
 * Time: 19:08
 */

namespace TEAMTALLY\System;

class Shared_Data extends Singleton {

	private $handles = array();

	/**
	 * Shares PHP data with JS scripts
	 *
	 * @param string $js_handle
	 * @param array $data
	 * @param string $position - 'after' or 'before'
	 *
	 * @return void
	 */
	public static function share_to_js_script( $js_handle, $data, $position = 'after' ) {
		$instance = self::get_instance();

		if ( isset( $instance->handles[ $js_handle ] ) ) {
			return;
		}

		$js_code = Template::parse(
			'admin/common/shared_data.php',
			array(
				'shared_data' => $data
			)
		);

		wp_add_inline_script( $js_handle, $js_code, $position );
		$instance->handles[ $js_handle ] = true;

	}

	/**
	 * Automatic initialization routine
	 */
	protected function init() {
		$this->handles = array();

	}

	/**
	 * Loading the class
	 */
	public static function load() {
		self::get_instance();
	}


}