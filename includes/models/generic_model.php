<?php
/**
 * Created by PhpStorm.
 * User: Itanjaka Mandresi
 * Date: 23/02/2023
 * Time: 16:07
 */

namespace TEAMTALLY\Models;

use TEAMTALLY\System\Helper;
use WP_Post;
use WP_Query;
use WP_Term;

class Generic_Model {

	/**
	 * Retrieves post data with its associated post meta datas
	 *
	 * @param int|WP_Post $post
	 * @param string $post_type
	 *
	 * @return array|false
	 */
	public static function get_post( $post, $post_type = '' ) {

		if ( ! $post ) {
			return false;
		}

		if ( is_numeric( $post ) ) {
			/** @var WP_Post $post */
			$post = get_post( $post );
		}

		if ( $post_type && ( $post->post_type != $post_type ) ) {
			return false;
		}

		// Retrieves post_meta associated to the post
		$post_meta_list = get_post_meta( $post->ID, '', true );

		$post_meta = array();

		if ( is_array( $post_meta_list ) ) {
			foreach ( $post_meta_list as $meta_key => $meta_value ) {
				$meta_value             = $meta_value[0];
				$post_meta[ $meta_key ] = $meta_value;
			}
		}

		$post_data = array(
			'raw'  => $post,
			'meta' => $post_meta,
		);

		return $post_data;

	}


	/**
	 * Retrieves info about a particular taxonomy term
	 *
	 * @param int|WP_Term $taxonomy_term
	 * @param $taxonomy_name
	 *
	 * @return array|false
	 */
	public static function get_taxonomy_term_info( $taxonomy_term, $taxonomy_name ) {
		$data = false;

		/** @var WP_Term $taxonomy */
		$taxonomy = get_term( $taxonomy_term, $taxonomy_name );

		if ( $taxonomy instanceof WP_Term ) {
			$meta = get_term_meta( $taxonomy->term_id, '', true );

			foreach ( $meta as &$meta_value ) {
				if ( is_array( $meta_value ) ) {
					$meta_value = $meta_value[0];
				}
			}

			$data = array(
				'raw'  => $taxonomy,
				'meta' => $meta,
			);
		}

		return $data;

	}

	/**
	 * Returns a list of posts that are associated to a particular taxonomy term
	 *
	 * @param string $post_type
	 * @param int|string $taxonomy_term
	 * @param string $taxonomy_name
	 *
	 * @return WP_Query
	 */
	public static function get_posts_linked_to_taxonomy_term( $post_type, $taxonomy_term, $taxonomy_name, $args = array() ) {
		$default_args = array(
			'post_type' => $post_type,
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy_name,
					'field'    => 'id',
					'terms'    => $taxonomy_term
				)
			)
		);

		$args = array_merge( $default_args, $args );

		$posts = new WP_Query( $args );

		return $posts;

	}

}