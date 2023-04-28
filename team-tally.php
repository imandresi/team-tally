<?php
/*
* Plugin Name:       TEAM Tally
* Description:       This plugin allows you to create, manage and display sports leagues and teams with Elementor integration.
* Version:           1.0.0
* Requires at least: 5.3
* Requires PHP:      7.2
* Author:            Itanjaka Mandresi
* Author URI:        mailto:contact@mandresi.net
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       team-tally
* Domain Path:       /languages
* Elementor tested up to: 3.11.2
*/

/*
TEAM Tally is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

TEAM Tally is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/


// Prevent Direct call for security reason
if ( ! defined( 'ABSPATH' ) ) {
	exit; // silence is golden
}

add_action( 'plugins_loaded', function () {
	require_once( 'includes/bootstrap.php' );
} );

