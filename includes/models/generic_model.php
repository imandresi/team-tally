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

		$post_meta_list = get_post_meta( $post->ID, '', true );

		$post_meta = array();

		if ( is_array( $post_meta_list ) ) {
			foreach ( $post_meta_list as $meta_key => $meta_value ) {
				$meta_value             = $meta_value[0];
				$post_meta[ $meta_key ] = $meta_value;
			}
		}

		$post              = (array) $post;
		$post['post_meta'] = $post_meta;

		return $post;

	}
}