<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 21/02/2023
 * Time: 11:25
 */

namespace TEAMTALLY;

require_once( 'includes/system/helper.php' );

use TEAMTALLY\System\Helper;

define( 'PROJECT_NAME', __NAMESPACE__ );
define( 'TEAMTALLY_VERSION', '1.0' );
define( 'TEAMTALLY_DEV_MODE', true );

/**
 * Activate Debugging
 */
//error_reporting( E_ALL );
//ini_set( 'display_errors', 'On' );

/**
 * Path / Url constants
 */

define( 'TEAMTALLY_TEXT_DOMAIN', 'team-tally' );

define( 'TEAMTALLY_PLUGIN_ENTRY', __FILE__ );
define( 'TEAMTALLY_ROOT_DIR', plugin_dir_path( TEAMTALLY_PLUGIN_ENTRY ) );
define( 'TEAMTALLY_INCLUDES_DIR', TEAMTALLY_ROOT_DIR . 'includes/' );

define( 'TEAMTALLY_LANGUAGES_DIR', TEAMTALLY_ROOT_DIR . 'languages/' );
define( 'TEAMTALLY_TEMPLATES_DIR', TEAMTALLY_INCLUDES_DIR . 'templates/' );
define( 'TEAMTALLY_SHORTCODES_DIR', TEAMTALLY_INCLUDES_DIR . 'shortcodes/' );

define( 'TEAMTALLY_ASSETS', 'assets/' );
define( 'TEAMTALLY_ASSETS_IMAGES', TEAMTALLY_ASSETS . 'images/' );
define( 'TEAMTALLY_ASSETS_IMAGES_DIR', TEAMTALLY_ROOT_DIR . TEAMTALLY_ASSETS_IMAGES );

define( 'TEAMTALLY_PLUGIN_URI', plugin_dir_url( TEAMTALLY_PLUGIN_ENTRY ) );
define( 'TEAMTALLY_ASSETS_URI', TEAMTALLY_PLUGIN_URI . 'assets/' );
define( 'TEAMTALLY_ASSETS_IMAGES_URI', TEAMTALLY_PLUGIN_URI . TEAMTALLY_ASSETS_IMAGES );
define( 'TEAMTALLY_ASSETS_CSS_URI', TEAMTALLY_ASSETS_URI . 'css/' );
define( 'TEAMTALLY_ASSETS_SCRIPTS_URI', TEAMTALLY_ASSETS_URI . 'js/' );


require_once( TEAMTALLY_INCLUDES_DIR . 'core/loader.php' );