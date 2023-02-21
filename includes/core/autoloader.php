<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 08/12/2018
 * Time: 21:25
 */

namespace TEAMTALLY\Core;

use TEAMTALLY\System\Helper;

if ( ! class_exists( __NAMESPACE__ . '\Autoloader' ) ) {

	class Autoloader {

		public static function autoload_class( $classname ) {
			$parts = explode( '\\', $classname );

			array_walk( $parts, function ( &$value, $key ) {
				$value = strtolower( $value );
			} );

			$class_in_project = ( strtolower(PROJECT_NAME) == $parts[0] );

			if ( count( $parts ) > 1 ) {
				$parts[0] = untrailingslashit( TEAMTALLY_INCLUDES_DIR );
			}

			$filename = implode( '/', $parts ) . '.php';

			if ( $class_in_project ) {
				if ( file_exists( $filename ) ) {
					require_once( $filename );
				} else {
					die ( "Unable to load class $classname : $filename" );
				}
			}

		}

		public static function init() {
			spl_autoload_register( array( __CLASS__, 'autoload_class' ) );
		}

	}

}