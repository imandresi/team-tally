<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 08/03/2023
 * Time: 06:20
 */

namespace TEAMTALLY\Elementor\Models;

use TEAMTALLY\System\Helper;
use TEAMTALLY\System\SimpleXMLElement_Extended;
use TEAMTALLY\System\Singleton;

abstract class Template_Base_Model_Abstract extends Singleton {

	const DB_FILE = __DIR__ . '/team_template.xml';
	const DB_FILE_INIT = __DIR__ . '/team_template_init.xml';

	/**
	 * Name of current template database
	 *
	 * @return string
	 */
	abstract protected function template_name();

	/**
	 * Name of former template database
	 *
	 * Used for initialization
	 *
	 * @return string
	 */
	abstract protected function template_init_name();


	/**
	 * Checks if the supplied template is among the default template
	 *
	 * @param string $template_name
	 *
	 * @return bool
	 */
	public static function is_default_template( $template_name ) {
		$instance      = self::get_instance();
		$result        = false;
		$template_name = trim( $template_name );

		if ( ! file_exists( $instance->template_init_name() ) ) {
			return false;
		}

		$xml = simplexml_load_file( $instance->template_init_name() );

		if ( $xml && $xml->template ) {
			foreach ( $xml->template as $template ) {
				$name = trim( $template->name );
				if ( strcasecmp( $template_name, $name ) === 0 ) {
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns an array of all templates
	 *
	 * @return array
	 */
	public static function get_all_templates() {
		$instance  = self::get_instance();
		$templates = array();

		self::verify();

		$xml = simplexml_load_file( $instance->template_name() );

		if ( $xml && $xml->template ) {
			foreach ( $xml->template as $template ) {
				$templates[] = array(
					'name'      => trim( (string) $template->name ),
					'container' => Helper::remove_php_code( (string) $template->container ),
					'item'      => Helper::remove_php_code( (string) $template->item ),
				);
			}
		}

		return $templates;

	}

	/**
	 * Updates or adds a new template
	 *
	 * @param array $template_data
	 *
	 * @return boolean
	 */
	public static function update_template( $template_data ) {
		$instance           = self::get_instance();
		$template_name      = trim( $template_data['name'] );
		$template_container = trim( $template_data['container'] );
		$template_item      = trim( $template_data['item'] );

		self::verify();

		$xml = simplexml_load_file( $instance->template_name(), SimpleXMLElement_Extended::class );

		$template_selector = "//template[name='" . addslashes( $template_name ) . "']";
		$template          = $xml->xpath( $template_selector );

		$is_new_template = false;

		if ( $template ) {
			$template = $template[0];
			unset( $template->container );
			unset( $template->item );
		} else {
			$is_new_template = true;
			$node            = $xml->xpath( "/templates" );
			$node            = $node ? $node[0] : $xml->addChild( 'templates' );

			$template = $node->addChild( 'template' );
			$template->addChild( 'name', $template_name );
		}

		$template->addChildWithCData( 'container', $template_container );
		$template->addChildWithCData( 'item', $template_item );

		$xml->asXML( $instance->template_name() );

		return $is_new_template;

	}

	/**
	 * Deletes a template
	 *
	 * @param $template_name
	 *
	 * @return boolean
	 */
	public static function delete_template( $template_name ) {
		$instance = self::get_instance();
		self::verify();

		$xml = simplexml_load_file( $instance->template_name(), SimpleXMLElement_Extended::class );

		$template_selector = "//template[name='" . addslashes( $template_name ) . "']";
		$template          = $xml->xpath( $template_selector );

		if ( $template ) {
			$dom = dom_import_simplexml( $template[0] );
			$dom->parentNode->removeChild( $dom );
			$xml->asXML( $instance->template_name() );

			return true;
		}

		return false;
	}

	/**
	 * Returns the data corresponding to a $template_name
	 *
	 * @param $template_name
	 *
	 * @return array|false
	 */
	public static function get_template( $template_name ) {
		$instance      = self::get_instance();
		$template_name = trim( strtoupper( $template_name ) );

		self::verify();
		$xml = simplexml_load_file( $instance->template_name() );

		foreach ( $xml->template as $template ) {
			$xml_template_name = trim( strtoupper( $template->name ) );
			if ( $template_name == $xml_template_name ) {
				return array(
					'name'      => trim( (string) $template->name ),
					'container' => Helper::remove_php_code( (string) $template->container ),
					'item'      => Helper::remove_php_code( (string) $template->item ),
				);
			}
		}

		return false;

	}

	/**
	 * Verifiies if the database is ok
	 *
	 * @return void
	 */
	public static function verify() {
		$instance = self::get_instance();
		if ( ! file_exists( $instance->template_name() ) ) {
			@copy( $instance->template_init_name(), $instance->template_name() );
		}
	}

	/**
	 * Deletes and reset the templates
	 *
	 * @return void
	 */
	public static function reset() {
		$instance = self::get_instance();
		@unlink( $instance->template_name() );
		@copy( $instance->template_init_name(), $instance->template_name() );
	}

}