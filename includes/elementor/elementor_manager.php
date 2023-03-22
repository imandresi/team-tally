<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 05/03/2023
 * Time: 19:05
 */

namespace TEAMTALLY\Elementor;

use Elementor\Core\Admin\Admin;
use TEAMTALLY\Elementor\Includes\Elementor_Team_Listing_Ajax;
use TEAMTALLY\Elementor\Widgets\Elementor_Team_Listing_Widget;
use TEAMTALLY\System\Admin_Notices;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Hook_Recorder;

class Elementor_Manager {

	const ELEMENTOR_ACTIVE = 1;
	const ELEMENTOR_PRO_ACTIVE = 2;

	const ELEMENTOR_CATEGORY_SLUG = TEAMTALLY_SLUG;

	/**
	 * Used to register all widgets
	 *
	 * Fired by 'elementor/widgets/register' action hook
	 *
	 * @param $widgets_manager
	 *
	 * @return void
	 */
	public static function register_widgets( $widgets_manager ) {
		$widgets_manager->register( new Elementor_Team_Listing_Widget() );
	}

	/**
	 * Used to register all controls
	 *
	 * Fired by 'elementor/controls/register' action hook
	 *
	 * @param $controls_manager
	 *
	 * @return void
	 */
	public static function register_controls( $controls_manager ) {

	}

	/**
	 * Checks that every requirement are satisfied for the plugin to work correctly
	 *
	 * @return bool
	 */
	private static function is_compatible() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			Admin_Notices::set_message(
				__( 'TEAM TALLY plugin needs that Elementor is installed and activated.', TEAMTALLY_TEXT_DOMAIN ),
				Admin_Notices::ADMIN_NOTICE_ERROR
			);

			return false;
		}

		$is_compatible = true;

		if ( ! version_compare( ELEMENTOR_VERSION, MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			Admin_Notices::set_message(
				sprintf(
					__( 'TEAM TALLY plugin needs at least Elementor version %s', TEAMTALLY_TEXT_DOMAIN ),
					MINIMUM_ELEMENTOR_VERSION
				),
				Admin_Notices::ADMIN_NOTICE_ERROR
			);
			$is_compatible = false;
		}

		if ( version_compare( PHP_VERSION, MINIMUM_PHP_VERSION, '<' ) ) {
			Admin_Notices::set_message(
				sprintf(
					__( 'TEAM TALLY plugin needs at least PHP version %s', TEAMTALLY_TEXT_DOMAIN ),
					MINIMUM_ELEMENTOR_VERSION
				),
				Admin_Notices::ADMIN_NOTICE_ERROR
			);
			$is_compatible = false;
		}

		return $is_compatible;
	}

	/**
	 * Setup everything for elementor interfaces of the plugin
	 *
	 * @return void
	 */
	public static function setup_environment() {

		// Adding the TEAM TALLY Elementor Category in the Editor
		add_action( 'elementor/elements/categories_registered', function ( $elements_manager ) {
			$elements_manager->add_category(
				self::ELEMENTOR_CATEGORY_SLUG,
				array(
					'title' => __( 'TEAM TALLY', TEAMTALLY_TEXT_DOMAIN ),
					'icon'  => 'fa fa-plug',
				)
			);
		} );

		// Init Widgets
		add_action( 'elementor/widgets/register', array( self::class, 'register_widgets' ) );

		// Init Controls
		add_action( 'elementor/controls/register', array( self::class, 'register_controls' ) );

	}

	/**
	 * Initialization
	 *
	 * @return void
	 */
	public static function init() {

		// checks if elementor can be used with the plugin
		if ( ! self::is_compatible() ) {
			return;
		}

		// setup environment
		add_action( 'elementor/init', array( self::class, 'setup_environment' ) );

		// initialize all ajax
		Elementor_Team_Listing_Ajax::init();

	}

}