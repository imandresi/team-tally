<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 01/04/2023
 * Time: 18:49
 */

namespace TEAMTALLY\Views;

use TEAMTALLY\System\Template;

class Import_View {

	public static function display_import_page( $args ) {
		Template::pparse( 'admin/import/import_page.php', $args );

	}

}