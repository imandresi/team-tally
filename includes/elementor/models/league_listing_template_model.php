<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 08/03/2023
 * Time: 06:20
 */

namespace TEAMTALLY\Elementor\Models;

class League_Listing_Template_Model extends Template_Base_Model_Abstract {

	const DB_FILE = __DIR__ . '/league_template.xml';
	const DB_FILE_INIT = __DIR__ . '/league_template_init.xml';

	/**
	 * Name of current template database
	 *
	 * @return string
	 */
	protected function template_name() {
		return self::DB_FILE;
	}

	/**
	 * Name of former template database
	 *
	 * Used for initialization
	 *
	 * @return string
	 */
	protected function template_init_name() {
		return self::DB_FILE_INIT;
	}

}