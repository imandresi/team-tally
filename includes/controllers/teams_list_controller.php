<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 02/03/2023
 * Time: 11:15
 */

namespace TEAMTALLY\Controllers;

use TEAMTALLY\Core\Admin\Teams_List_Table;
use TEAMTALLY\Models\Generic_Model;
use TEAMTALLY\Models\Leagues_Model;
use TEAMTALLY\Models\Teams_Model;
use TEAMTALLY\System\Helper;
use TEAMTALLY\System\Singleton;

class Teams_List_Controller extends Singleton {

	/** @var Teams_Controller $teams */
	private $teams;

	private $last_post;  // used as a cache

	/**
	 * Customize the teams page
	 *
	 * @return void
	 */
	public function customize_teams_list_page() {
		global $post_type_object, $post_new_file;

		// title initialization
		$league_name                    = Helper::get_var( $this->teams->league['data']['league_name'] );
		$new_title                      = sprintf(
			__( 'TEAMS MANAGEMENT (League : %s)', TEAMTALLY_TEXT_DOMAIN ),
			$league_name
		);
		$post_type_object->labels->name = $new_title;

		// button add new
		$post_new_file = add_query_arg( array(
			'post_type' => 'teamtally_teams',
			'league_id' => Helper::get_var( $this->teams->league['data']['term_id'] ),
		), 'post-new.php' );

		// teams list table views
		add_filter( 'views_edit-teamtally_teams', array(
			$this,
			'customize_teams_list_table_views'
		), 10, 1 );

	}

	/**
	 * Customize the links 'All (3) | Published (3) | Draft (3) | Trash (3)'
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	public function customize_teams_list_table_views( $views ) {

		foreach ( $views as $class => $view ) {
			$league_id = $this->teams->league_id;
			$args      = array();

			switch ( $class ) {
				case 'publish':
					$args = array(
						'post_status' => 'publish'
					);
					break;

				case 'draft':
					$args = array(
						'post_status' => 'draft'
					);

					break;

				case 'trash':
					$args = array(
						'post_status' => 'trash'
					);
					break;

				case 'all':
				default:
					$args = array(
						'post_status' => array( 'publish', 'draft' )
					);
					break;

			}

			$posts = Generic_Model::get_posts_linked_to_taxonomy_term(
				Teams_Model::TEAMS_POST_TYPE,
				$league_id,
				Leagues_Model::LEAGUES_TAXONOMY_NAME,
				$args
			);

			$count = $posts->post_count;

			// change URL
			$regexp      = "/<a href=\"(.+?)\"/i";
			$replacement = "<a href=\"$1&#038;league_id={$league_id}\"";
			$view        = preg_replace( $regexp, $replacement, $view );

			// change count
			$regexp      = "/<span class=\"count\">\(\d+\)<\/span>/i";
			$replacement = "<span class=\"count\">({$count})</span>";
			$view        = preg_replace( $regexp, $replacement, $view );

			// update view
			$views[ $class ] = $view;

		}

		return $views;

	}

	/**
	 * Adds additional columns to teams list table
	 *
	 * @return void
	 */
	public function customize_teams_list_table_display() {

		add_filter( 'wp_list_table_class_name', function ( $class_name, $args ) {

			// uses another list table instead of WP_Posts_List_Table
			// in order to implement filtering by league
			$screen = Helper::get_var( $args['screen'] );

			if ( $screen ) {
				$use_alternative_table = $screen->id === 'edit-teamtally_teams';
				$class_name            = $use_alternative_table ? Teams_List_Table::class : $class_name;
			}

			return $class_name;
		}, 10, 2 );

		// Customize the columns header
		add_filter( 'manage_' . Teams_Model::TEAMS_POST_TYPE . '_posts_columns', function ( $columns ) {
			$columns = array(
				'cb'       => $columns['cb'],
				'id'       => '#ID',
				'title'    => __( 'Name', TEAMTALLY_TEXT_DOMAIN ),
				'nickname' => __( 'Nickname', TEAMTALLY_TEXT_DOMAIN ),
				'history'  => __( 'History', TEAMTALLY_TEXT_DOMAIN ),
				'logo'     => __( 'Logo', TEAMTALLY_TEXT_DOMAIN ),
				'date'     => $columns['date'],
			);

			return $columns;
		} );

		// Customize the columns content
		// manage_teamtally_teams_posts_custom_column
		$action_name = 'manage_' . Teams_Model::TEAMS_POST_TYPE . '_posts_custom_column';
		add_action( $action_name, function ( $column, $post_id ) {

			$last_post_id = Helper::get_var( $this->last_post['data']['ID'] );
			$do_load_post = ( $last_post_id != $post_id );

			if ( $do_load_post ) {
				$this->last_post = Teams_Model::get_team( $post_id );
			}

			switch ( $column ) {
				case 'id':
					print $post_id;
					break;

				case 'nickname':
					print $this->last_post['data'][ Teams_Model::TEAMS_FIELD_NICKNAME ];
					break;

				case 'logo':
					$url = Helper::get_var( $this->last_post['data'][ Teams_Model::TEAMS_FIELD_LOGO ]['URL'], '' );
					print <<<EOT
<div class="teams-logo" style="background-image: url({$url})"></div>
EOT;
					break;

				case 'history':
					$excerpt = get_the_excerpt( $this->last_post['raw'] );
					print $excerpt;
					break;
			}

		}, 10, 2 );

	}

	/**
	 * Initialization routine
	 */
	protected function init( $args ) {
		$this->teams = $args[0];

		// league_id has to be provided if we want to view the list of teams about a league
		// otherwise restrict access
		$league_id = Helper::get_var( $this->teams->league_id );
		if ( ! $league_id ) {
			$url = add_query_arg( array(
				'page' => 'teamtally_leagues_view'
			), admin_url( 'admin.php' ) );

			wp_redirect( $url );
			exit;
		}

		add_action( 'admin_head-edit.php', array( $this, 'customize_teams_list_page' ) );

		// customize the teams listing
		$this->customize_teams_list_table_display();

		// Adds the 'league_id' hidden field to the quick edit form
		// in order to prevent bug
		add_action( 'quick_edit_custom_box', function ( $column_name, $post_type, $taxonomy ) {
			if ( $column_name == 'nickname' ) {
				print "<input type=\"hidden\" name=\"league_id\" value=\"{$this->teams->league_id}\">";
			}
		}, 10, 3 );

	}


	/**
	 * @param Teams_Controller $teams
	 *
	 * @return void
	 */
	public static function run( $teams ) {
		self::get_instance( $teams );

	}

}