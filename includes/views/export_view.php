<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 03/04/2023
 * Time: 18:51
 */

namespace TEAMTALLY\Views;

use TEAMTALLY\System\Template;

class Export_View {

	public static function display_export_page( $args ) {
		Template::pparse( 'admin/export/export_page.php', $args );

	}
}