<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 03/04/2023
 * Time: 19:12
 */

namespace TEAMTALLY\Views;

use TEAMTALLY\System\Template;

class About_View {

	/**
	 * Displays infos about the author
	 *
	 * @return void
	 */
	public static function display_about_page() {
		Template::pparse( 'admin/about/about_page.php' );

	}

}