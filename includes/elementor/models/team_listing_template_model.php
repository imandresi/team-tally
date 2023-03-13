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

class Team_Listing_Template_Model {

	const DB_FILE = __DIR__ . '/team_template.xml';
	const DB_FILE_INIT = __DIR__ . '/team_template_init.xml';

	/**
	 * Returns an array of all templates
	 *
	 * @return array
	 */
	public static function get_all_templates() {

		$templates = array();

		$xml = simplexml_load_file( self::DB_FILE );

		foreach ( $xml->template as $template ) {
			$templates[] = array(
				'name'      => trim( (string) $template->name ),
				'container' => trim( (string) $template->container ),
				'item'      => trim( (string) $template->item ),
			);
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
		$template_name      = trim( $template_data['name'] );
		$template_container = trim( $template_data['container'] );
		$template_item      = trim( $template_data['item'] );

		$xml = simplexml_load_file( self::DB_FILE, SimpleXMLElement_Extended::class );

		$template_selector = "//template[name='" . addslashes( $template_name ) . "']";
		$template          = $xml->xpath( $template_selector );

		$is_new_template = false;

		if ( $template ) {
			$template = $template[0];
			unset( $template->container );
			unset( $template->item );
		} else {
			$is_new_template = true;
			$node = $xml->xpath( "/templates" );
			$node = $node ? $node[0] : $xml->addChild( 'templates' );

			$template = $node->addChild( 'template' );
			$template->addChild( 'name', $template_name );
		}

		$template->addChildWithCData( 'container', $template_container );
		$template->addChildWithCData( 'item', $template_item );

		$xml->asXML( self::DB_FILE );

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
		$xml = simplexml_load_file( self::DB_FILE, SimpleXMLElement_Extended::class );

		$template_selector = "//template[name='" . addslashes( $template_name ) . "']";
		$template          = $xml->xpath( $template_selector );

		if ( $template ) {
			$dom = dom_import_simplexml( $template[0] );
			$dom->parentNode->removeChild( $dom );
			$xml->asXML( self::DB_FILE );

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
		$template_name = trim( strtoupper( $template_name ) );

		$xml = simplexml_load_file( self::DB_FILE );

		foreach ( $xml->template as $template ) {
			$xml_template_name = trim( strtoupper( $template->name ) );
			if ( $template_name == $xml_template_name ) {
				return array(
					'name'      => trim( (string) $template->name ),
					'container' => trim( (string) $template->container ),
					'item'      => trim( (string) $template->item ),
				);
			}
		}

		return false;

	}

	/**
	 * Deletes and reset the templates
	 *
	 * @return void
	 */
	public static function reset() {
		@unlink( self::DB_FILE );
		@copy( self::DB_FILE_INIT, self::DB_FILE );
	}

}