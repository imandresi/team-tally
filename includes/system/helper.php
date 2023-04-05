<?php

/**
 * This class gathers a list of useful utilities.
 * It is intended to be used by PHP versions prior to 5.3
 *
 * Author: Itanjaka Mandresi
 * Date: 09/01/2016
 * Time: 10:52
 *
 */

namespace TEAMTALLY\System;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

if ( ! class_exists( __NAMESPACE__ . '\Helper' ) ) {

	class Helper {

		/**
		 * used by the self::value_type()
		 */
		const VALUE_TYPE_OTHER = 0;
		const VALUE_TYPE_PERCENTAGE = 1;
		const VALUE_TYPE_UNSIGNED_INT = 2;
		const VALUE_TYPE_NULL = 3;
		const VALUE_TYPE_STRING = 4;

		const VALUE_EMPTY_QUERY_PARAM = 'Ø';

		public static $DEBUG_FILENAME = '';

		/**
		 * debug
		 *
		 * Used for debugging a variable.
		 *
		 * @param        $variable
		 * @param string $varname
		 * @param boolean $dump_to_filename
		 * @param int $container_index
		 *
		 * @since   1.0.0
		 * @access  public
		 *
		 */
		public static function debug( &$variable, $varname = "", $dump_to_filename = false, $container_index = null ) {

			$dump_filename = empty( self::$DEBUG_FILENAME ) ?
				dirname( __FILE__ ) . '/debug.txt' :
				self::$DEBUG_FILENAME;

			$container_template = array(
				0 => "<pre style=\"margin-left: 300px;\">{SKIN:VARNAME}{SKIN:DATA}</pre>",
				1 => "{SKIN:VARNAME}<textarea cols=80 rows=10>{SKIN:DATA}</textarea><br><br>",
				2 => "\n{SKIN:VARNAME}{SKIN:DATA}\n",
			);

			$data         = "";
			$varname_data = "";
			$vartype      = gettype( $variable );

			if ( is_null( $container_index ) ) {
				$container_index = $dump_to_filename ? 2 : 0;
			}

			if ( $varname ) {
				switch ( $container_index ) {
					case 0 :
						$varname_data = "<b>($vartype) $varname:</b> ";
						break;
					case 1 :
						$varname_data = "<b>($vartype) $varname:</b><br>";
						break;
					case 2 :
						$varname_data = "($vartype) $varname:\n";
						break;
				}
			}

			$debug_data = print_r( $variable, true );;

			if ( $container_index == 0 ) {
				$debug_data = str_replace( '<', '&#60;', $debug_data );
				$debug_data = str_replace( '<', '&#62;', $debug_data );
			}

			$data .= $debug_data;

			if ( $container_index == 3 ) {
				$data = htmlentities( $data );
			}

			$value = str_replace( '{SKIN:DATA}', $data, $container_template[ $container_index ] );
			$value = str_replace( '{SKIN:VARNAME}', $varname_data, $value );
			if ( $dump_to_filename ) {
				$handle = fopen( $dump_filename, 'a' );
				fputs( $handle, $value . "\n" );
				fputs( $handle, "------------------------------------\n\n" );
				fclose( $handle );
			} else {
				print $value;
			}
		}

		/**
		 * Writes information inside the debugger file
		 *
		 * @param $line
		 * @param bool $linefeed
		 */
		public static function debugger_print( $line, $linefeed = false ) {
			$dump_filename = empty( self::$DEBUG_FILENAME ) ?
				dirname( __FILE__ ) . '/debug.txt' :
				self::$DEBUG_FILENAME;

			$line .= $linefeed ? "\n\n" : "\n";
			file_put_contents( $dump_filename, $line, FILE_APPEND );
		}

		/**
		 * get_defined_constants
		 *
		 * Returns a list of all the constants of the plugin.
		 *
		 * @return array
		 * @since   1.0.0
		 * @access  public
		 *
		 */
		public static function get_defined_constants() {
			$theme_constants = array();
			$constants_list  = get_defined_constants( true );

			if ( isset( $constants_list['user'] ) ) {
				$regexp = "/^" . PROJECT_NAME . "_/is";
				foreach ( $constants_list['user'] as $constant_name => $constant_value ) {
					if ( preg_match( $regexp, $constant_name ) ) {
						$theme_constants[ $constant_name ] = $constant_value;
					}
				}
			}

			return $theme_constants;
		}

		/**
		 * Returns the value of a variable if it exists or a default value if not.
		 * This function is pretty useful to check values of an associative arrays
		 *
		 * @param mixed $var
		 * @param null|mixed $default_value
		 *
		 * @return null|mixed
		 */
		public static function get_var( &$var, $default_value = null ) {
			return isset( $var ) ? $var : $default_value;
		}

		/**
		 * Normalizes a path
		 *
		 * Converts all backslashes "\" to slashes "/"
		 * Removes all doubloons of slashes
		 * If $add_trailing_slash is TRUE, adds a trailing slash at the end of the path
		 *
		 * This is an enhanced version of the WordPress function wp_normalize_path()
		 * because it also checks the path for '.' and '..' directories and cleans them
		 * according found results.
		 *
		 * @param string $path
		 * @param bool|FALSE $add_trailing_slash Adds a trailing slash at the end of the path if TRUE
		 *
		 * @return string
		 */
		public static function normalize_path( $path, $add_trailing_slash = false ) {

			// checks if the $path is in fact a url
			// if this is the case, the scheme is extracted before processing
			$scheme = '';
			$regexp = "/^((http|https|ftp)\:\/\/)(.+)/is";
			if ( preg_match( $regexp, $path, $matches ) ) {
				$scheme = $matches[1];
				$path   = $matches[3];
			}

			$path = str_replace( '\\', '/', $path );
			$path = preg_replace( '|/+|', '/', $path );

			$parts = array();

			foreach ( explode( '/', $path ) as $i => $fold ) {
				if ( ( '' == $fold ) || ( '.' == $fold ) ) {
					continue;
				}
				if ( ( '..' == $fold ) && ( $i > 0 ) && ( end( $parts ) != '..' ) ) {
					array_pop( $parts );
				} else {
					$parts[] = $fold;
				}
			}

			$path = ( '/' == $path[0] ? '/' : '' ) . join( '/', $parts );

			if ( $add_trailing_slash ) {
				$path .= '/';
			}

			return $scheme . $path;
		}

		/**
		 * Checks if an array is a list or an associative array
		 *
		 * @param $array
		 *
		 * @return bool
		 */
		public static function array_is_list( $array ) {
			return ctype_digit( join( '', array_keys( $array ) ) );
		}

		/**
		 * Compiles an array of properties into a string
		 *
		 * This method will be most used for compiling 'style' attributes
		 * in an html tag.
		 *
		 * $properties = array(
		 *     'width' => '500px',
		 *     'display' => 'none',
		 *     'margin-top' => '15px'
		 * )
		 *
		 * Will be compiled as:
		 * width: 500px; display: none; margin-top: 15px;
		 *
		 * @param array $properties
		 *
		 * @return string
		 */
		public static function compile_properties( $properties ) {

			$compiled_properties = '';

			if ( ! is_array( $properties ) ) {
				return '';
			}

			$properties = array_change_key_case( $properties, CASE_LOWER );

			foreach ( $properties as $key => $value ) {
				if ( $value ) {
					$key   = trim( $key, ": \t\n\r" );
					$value = trim( $value, "; \t\n\r" );

					$compiled_properties .= $key . ': ' . $value . '; ';
				}
			}

			return $compiled_properties;

		}

		/**
		 * Checks if a variable contains an unsigned integer value.
		 *
		 * This function does not care whether the variable contains a string or an integer
		 *
		 * @param string|int $value
		 *
		 * @return bool
		 */
		public static function is_unsigned_int( $value ) {

			if ( ! is_numeric( $value ) ) {
				return false;
			}

			if ( '' === $value ) {
				return false;
			}

			return ctype_digit( (string) $value );

		}

		/**
		 * Builds an html tag from attributes values
		 *
		 * @param string $tag html tag
		 * @param array $attributes Associative array of the attributes (key / values)
		 *                                         values may also be another associative array in case
		 *                                         of css styles.
		 * @param null $content Text content of the tag
		 * @param bool|TRUE $auto_close_single_tag If true, ends the tag with a / if it is a single one
		 *
		 * @return string
		 */
		public static function build_html_tag( $tag, $attributes = array(), $content = null, $auto_close_single_tag = true ) {

			$attributes = array_change_key_case( $attributes, CASE_LOWER );

			$attributes_list = array( $tag );

			foreach ( $attributes as $attribute_key => $attribute_value ) {

				// Null ?
				if ( is_null( $attribute_value ) ) {
					continue;
				}

				// Boolean attribute - attribute without value
				if ( ( is_bool( $attribute_value ) ) && ( $attribute_value == true ) ) {
					$attributes_list[] = $attribute_key;
					continue;
				}

				// integer attribute
				if ( self::is_unsigned_int( $attribute_value ) ) {
					$attributes_list[] = "{$attribute_key}=\"{$attribute_value}\"";
					continue;
				}

				// processing css style, class attributes ...
				if ( is_array( $attribute_value ) ) {

					// empty ?
					if ( ! $attribute_value ) {
						continue;
					}

					// The array is a list - 'class' attribute for example
					if ( self::array_is_list( $attribute_value ) ) {
						$class_value = trim( join( ' ', $attribute_value ) );

						if ( $class_value ) {
							$attributes_list[] = "{$attribute_key}=\"{$class_value}\"";
						}

						continue;
					}

					// The array is an associative array - 'style' attribute for example
					$style_value = self::compile_properties( $attribute_value );

					if ( $style_value ) {
						$attributes_list[] = "{$attribute_key}=\"{$style_value}\"";
					}

					continue;
				}

				// string attribute
				if ( is_string( $attribute_value ) && ( $attribute_value ) ) {
					$attribute_value   = htmlentities( $attribute_value, ENT_COMPAT );
					$attributes_list[] = "{$attribute_key}=\"{$attribute_value}\"";
					continue;
				}

			}

			if ( ( $auto_close_single_tag ) && ( is_null( $content ) ) ) {
				$attributes_list[] = "/";
			}

			$html = join( " ", $attributes_list );
			$html = "<" . $html . ">";

			if ( ! $auto_close_single_tag ) {
				$html .= $content . "</{$tag}>";
			}

			return $html;

		}

		/**
		 * Calculates final new dimensions from resizing an item according to a given width or height
		 * or both.
		 *
		 * If both width and height are given, the final new returned dimensions are constrained.
		 * Otherwise, the missing size is calculated and returned.
		 *
		 * @param int $former_width
		 * @param int $former_height
		 * @param null|int $final_width
		 * @param null|int $final_height
		 *
		 * @return array|bool Returns FALSE if there is an error or returns the found sizes in an array
		 *                    $final_size[width]
		 *                               [height]
		 */
		public static function get_resize_info( $former_width, $former_height, $final_width = null, $final_height = null ) {

			// checks if parameters are ok
			if ( ( ! self::is_unsigned_int( $former_width ) ) || ( ! self::is_unsigned_int( $former_height ) ) ) {
				return false;
			}

			if ( ( 0 == $former_width ) || ( 0 == $former_height ) ) {
				return false;
			}

			// initialization
			$final_size = array(
				'width'  => $former_width,
				'height' => $former_height,
			);

			$width_set  = self::is_unsigned_int( $final_width );
			$height_set = self::is_unsigned_int( $final_height );

			// no size specified for both ?
			if ( ! $width_set && ! $height_set ) {
				return $final_size;
			}

			// constrain ?
			if ( $width_set && $height_set ) {
				$final_size = array(
					'width'  => $final_width,
					'height' => $final_height,
				);

				$ratio  = $final_width * 100 / $former_width;
				$height = round( $former_height * $ratio / 100 );

				if ( $height > $final_height ) {
					$ratio               = $final_height * 100 / $former_height;
					$final_size['width'] = round( $former_width * $ratio / 100 );
				} else {
					$final_size['height'] = $height;
				}

			} // finds missing width or height
			else {
				$final_size = array(
					'width'  => $final_width,
					'height' => $final_height,
				);

				// finds the width
				if ( ! $width_set ) {
					$ratio               = $final_height * 100 / $former_height;
					$final_size['width'] = round( $former_width * $ratio / 100 );
				}

				// finds the height
				if ( ! $height_set ) {
					$ratio                = $final_width * 100 / $former_width;
					$final_size['height'] = round( $former_height * $ratio / 100 );
				}

			}

			return $final_size;

		}

		/**
		 * Checks if a value is in a percentage form
		 *
		 * @param $value
		 *
		 * @return bool
		 */
		public static function is_percentage( $value ) {
			$result = false;

			if ( is_string( $value ) ) {
				$regexp = "/^[0-9]+\s*%$/";
				$result = preg_match( $regexp, $value ) ? true : false;
			}

			return $result;
		}

		/**
		 * Returns the type of a value as an integer result
		 *
		 * This private method is used by self::build_image_tag
		 *
		 * @param $value - self::VALUE_TYPE_PERCENTAGE
		 *               - self::VALUE_TYPE_UNSIGNED_INT
		 *               - self::VALUE_TYPE_NULL
		 *               - self::VALUE_TYPE_STRING
		 *               - self::VALUE_TYPE_OTHER
		 *
		 * @return int
		 */

		private static function value_type( $value ) {
			if ( self::is_percentage( $value ) ) {
				return self::VALUE_TYPE_PERCENTAGE;
			}

			if ( self::is_unsigned_int( $value ) ) {
				return self::VALUE_TYPE_UNSIGNED_INT;
			}

			if ( is_null( $value ) ) {
				return self::VALUE_TYPE_NULL;
			}

			if ( is_string( $value ) ) {
				return self::VALUE_TYPE_STRING;
			}

			return self::VALUE_TYPE_OTHER;

		}

		/**
		 * Generates an html image tag from an image source that may be an url or a local
		 * image filename with given array attributes.
		 *
		 * - If only width or height attribute is given then the image is virtually
		 * resized according to that size.
		 *
		 * - If both width and height attributes are given then the image is virtually
		 * constrained to that size.
		 *
		 * - If there is no width or height attribute, then the real size of the image is used.
		 *
		 * @param string $image
		 * @param array $attributes
		 *
		 * @return string
		 */
		public static function build_image_tag( $image, $attributes = array() ) {

			// checks if $attributes parameter is ok
			if ( ! is_array( $attributes ) ) {
				$attributes = array();
			}

			$default_attributes = array(
				'border' => 0,
			);

			// -- processing image source
			// is it an url or a local file ?
			$src        = false;
			$image      = trim( $image );
			$image_path = $image;

			if ( filter_var( $image, FILTER_VALIDATE_URL ) ) {
				$src        = $image;
				$image_path = self::path_from_url( $image );
			} elseif ( is_file( $image ) ) {
				// converts the local file to an url
				$src = self::url_from_path( $image );
			}

			// no image source ? no html tag generated
			if ( ! $src ) {
				return '';
			}

			$attributes['src'] = $src;

			// -- processing image sizes and potential virtual image resizing
			// gets image info
			$image_info    = getimagesize( $image_path );
			$former_width  = $image_info[0];
			$former_height = $image_info[1];

			$attributes = array_change_key_case( $attributes, CASE_LOWER );

			$attributes_width  = self::get_var( $attributes['width'], null );
			$attributes_height = self::get_var( $attributes['height'], null );

			$width_type  = self::value_type( $attributes_width );
			$height_type = self::value_type( $attributes_height );

			// checks if one of the attributes has invalid value
			if ( ( self::VALUE_TYPE_STRING == $width_type ) || ( self::VALUE_TYPE_OTHER == $width_type ) ) {
				$attributes_width = null;
			}

			if ( ( self::VALUE_TYPE_STRING == $height_type ) || ( self::VALUE_TYPE_OTHER == $height_type ) ) {
				$attributes_height = null;
			}

			// check if one of the attributes is percentage value
			if ( ( self::VALUE_TYPE_PERCENTAGE == $width_type ) || ( self::VALUE_TYPE_PERCENTAGE == $height_type ) ) {

				if ( is_null( $attributes_width ) ) {
					unset ( $attributes['width'] );
				}

				if ( is_null( $attributes_height ) ) {
					unset( $attributes['height'] );
				}

			} else {

				// Attributes value are unsigned integer or null
				// We can apply some resizing depending of the given width / height

				// new virtual size is automatically calculated according to the given width or height
				// attributes. If both width or height are given then the image is constrained.
				// if there is no width and height attribute then the real size is used.
				$resize_info = self::get_resize_info( $former_width, $former_height, $attributes_width, $attributes_height );

				if ( $resize_info ) {
					$attributes['width']  = $resize_info['width'];
					$attributes['height'] = $resize_info['height'];
				} else {
					$attributes['width']  = $former_width;
					$attributes['height'] = $former_height;
				}

			}

			// apply default attributes
			$attributes = array_merge( $default_attributes, $attributes );

			// final image tag
			$html = self::build_html_tag( 'img', $attributes );

			return $html;

		}

		/**
		 * Browses an associative array following the combinations of keys/sub-keys supplied
		 * in the $keystr variable and returns the founded value.
		 *
		 * If you want a reference to the array value to be returned, don't forget to prefix
		 * the function call with the '&' sign.
		 *
		 * $data = &array_value_from_string($my_array, 'key:key1:key2', $found);
		 *
		 * @param array $the_array The array to be browsed
		 * @param string $keystr Combination of keys / sub-keys separated by a ':'
		 * @param boolean $found TRUE if the value is found / FALSE otherwise
		 * @param string $separator The default separator is ':'
		 * @param boolean $auto_create When TRUE, the key is automatically created if it
		 *                             does not exist
		 *
		 * @return NULL|mixed        A NULL value is also returned if the key is not found
		 *
		 */
		public static function &array_value_from_string( &$the_array, $keystr, &$found, $auto_create = false, $separator = ':' ) {

			$found       = false;
			$null_result = null; // value if key not found and $auto_create is FALSE

			$key_list = explode( $separator, $keystr );

			if ( ! is_array( $key_list ) ) {
				return $null_result;
			}

			$the_value   = &$the_array;
			$keys_number = count( $key_list );
			$key_counter = 0;

			foreach ( $key_list as $key ) {
				$key_counter ++;
				$key = trim( $key );

				if ( '' == $key ) {
					return $null_result;
				}

				if ( ! isset( $the_value[ $key ] ) ) {
					// When auto-creating the key, we must take in account
					// the fact that if we are not at the end of the array,
					// it should be initialized with an empty array otherwise
					// it has to be initialized with a null value
					if ( $auto_create ) {
						$the_value[ $key ] = ( $key_counter < $keys_number ) ? array() : null;
					} else {
						return $null_result;
					}
				}

				$the_value = &$the_value[ $key ];

			}

			$found = true;

			return $the_value;

		}


		/**
		 * This function compares the current page request URI (merged with parameters
		 * that may be sent to $_POST) with a list of request data. It returns true
		 * if a match is found.
		 *
		 * The request data can be an array (see the description below) or an URI string.
		 *
		 * $request_data['path']
		 *              ['query'][param1]
		 *                       [param2]
		 *                       ...
		 *
		 * OU
		 *
		 * $request_data['regexp_path']
		 *
		 * @param $request_data_list
		 *
		 * @return bool
		 */
		public static function compare_page_request_to( ...$request_data_list ) {

			// --- builds page request
			$uri_parts = parse_url( $_SERVER['REQUEST_URI'] );

			if ( ! isset( $uri_parts['path'] ) ) {
				return false;
			}

			$page_request_data = array(
				'path'  => $uri_parts['path'],
				'query' => array()
			);

			if ( ! empty( $uri_parts['query'] ) ) {
				parse_str( $uri_parts['query'], $page_request_data['query'] );
			}

			// merges $_POST data if it exists
			if ( isset( $_POST ) ) {
				$page_request_data['query'] = array_merge( $page_request_data['query'], $_POST );
			}

			if ( ! is_array( $request_data_list ) ) {
				return false;
			}

			// --- COMPARISON OPERATION

			foreach ( $request_data_list as $request_data ) {

				// It is an URL
				if ( is_string( $request_data ) ) {
					$url       = $request_data;
					$uri_parts = parse_url( $url );

					$request_data = array(
						'path'  => $uri_parts['path'],
						'query' => array(),
					);

					if ( ! empty( $uri_parts['query'] ) ) {
						parse_str( $uri_parts['query'], $request_data['query'] );
					}

				}

				// checks path
				if ( ! empty( $request_data['path'] ) ) {
					if ( $request_data['path'] != $page_request_data['path'] ) {
						continue;
					}
				}

				// checks regexp_path
				if ( ! empty( $request_data['regexp_path'] ) ) {
					$regexp = $request_data['regexp_path'];
					if ( ! preg_match( $regexp, $page_request_data['path'] ) ) {
						continue;
					}
				}

				// checks query
				if ( empty( $request_data['query'] ) ) {
					return true;
				}

				if ( empty( $page_request_data['query'] ) ) {
					continue;
				}

				if ( ! is_array( $request_data['query'] ) ) {
					$request_data['query'] = array(
						$request_data['query'] => ''
					);
				}

				if ( ! is_array( $page_request_data['query'] ) ) {
					$page_request_data['query'] = array(
						$page_request_data['query'] => ''
					);
				}

				foreach ( $request_data['query'] as $key => $value ) {

					if ( ! isset( $page_request_data['query'][ $key ] ) ) {
						continue 2;
					}

					if ( $value == self::VALUE_EMPTY_QUERY_PARAM ) {
						continue;
					}

					if ( $page_request_data['query'][ $key ] != $value ) {
						continue 2;
					}

				}

				return true;

			}

			return false;

		}

		/**
		 * @param $path
		 * @param array $arguments
		 *
		 * @return string
		 */
		public static function build_url( $path, $arguments = array() ) {
			$url = get_site_url( null, $path );

			if ( ! empty( $arguments ) ) {
				$url .= '?' . http_build_query( $arguments );
			}

			return $url;

		}

		/**
		 * Merges only existing keys in two arrays by replacing the value of the first with
		 * the value of the second.
		 *
		 * Ex:
		 * $arr1[$key] = string1
		 * $arr2[$key] = string2
		 * $result[$key] = string2
		 *
		 * $arr1[$key] = array1
		 * $arr2[$key] = string2
		 * $result[$key] = array1
		 *
		 * $arr1[$key] = string1
		 * $arr2[$key] = array2
		 * $result[$key] = array2
		 *
		 * $arr1[$key] = array1
		 * $arr2[$key] = array2
		 * $result[$key] = array2
		 *
		 * @param $arr1
		 * @param $arr2
		 *
		 * @return array
		 */
		public static function array_recursive_merge_if_key_exists( $arr1, $arr2 ) {
			$arr1 = (array) $arr1;
			$arr2 = (array) $arr2;

			$out = array();

			// merging numeric keys of $arr2
			foreach ( $arr2 as $key => $value ) {
				if ( is_numeric( $key ) ) {
					$out[] = $value;
				}
			}

			foreach ( $arr1 as $key => $value ) {

				// merging numeric keys of $arr1
				if ( is_numeric( $key ) ) {
					$out[] = $value;
					continue;
				}

				if ( array_key_exists( $key, $arr2 ) ) {
					if ( is_array( $value ) ) {
						$out[ $key ] = self::array_recursive_merge_if_key_exists( $value, $arr2[ $key ] );
					} else {
						$out[ $key ] = $arr2[ $key ];
					}
				} else {
					$out[ $key ] = $value;
				}
			}

			return $out;

		}

		/**
		 * Fatal Error
		 *
		 * @param $error_message
		 */
		public static function raise_fatal_error( $error_message ) {
			print "<p><strong>FATAL ERROR: </strong>$error_message</p>";
			die;
		}


		/**
		 * Writes a base64 image data to a disk
		 * The file extension is automatically reajusted in function of the image type.
		 * Returns the new filename if the function succeed or FALSE if the function fails.
		 *
		 * @param string $base64_image_data
		 * @param string $filename
		 *
		 * @return string|bool
		 */
		public static function save_base64_image( $base64_image_data, $filename ) {

			$regexp = "/^data:(image\/\w+);base64,(.+)is/";

			if ( ! preg_match( $regexp, $base64_image_data, $matches ) ) {
				return false;
			}

			$type = $matches[1];

			switch ( $type ) {
				case 'image/jpeg':
					$extension = ".jpg";
					break;

				case 'image/png':
					$extension = ".png";
					break;

				case 'image/bmp':
					$extension = ".bmp";
					break;

				default:
					return false;
			}

			$regexp   = "/\.+(\w+)?$/is";
			$filename = preg_replace( $regexp, '', $filename ) . $extension;

			$data = $matches[2];
			$data = base64_decode( $data );

			$status = false;

			if ( $data !== false ) {
				$status = file_put_contents( $filename, $data ) === false ? false : $filename;
			}

			return $status;

		}

		/**
		 * Similar to the dirname function, except that this version works also with
		 * PHP version lower than 7.0
		 *
		 * @param string $path
		 * @param int $levels
		 *
		 * @return string
		 */
		public static function dirname( $path, $levels = 1 ) {
			if ( $levels < 0 ) {
				return '';
			}

			while ( $levels > 0 ) {
				$path = dirname( $path );
				$levels --;
			}

			return $path;
		}

		/**
		 * Resizes an image
		 *
		 * @param $file
		 * @param $w
		 * @param $h
		 * @param null|string $dest_file If given, then the resulting image is saved
		 * @param bool $crop
		 *
		 * @return resource
		 */
		public static function resize_image( $file, $w, $h, $dest_file = null, $crop = false ) {

			list( $width, $height ) = getimagesize( $file );
			$r = $width / $height;
			if ( $crop ) {
				if ( $width > $height ) {
					$width = ceil( $width - ( $width * abs( $r - $w / $h ) ) );
				} else {
					$height = ceil( $height - ( $height * abs( $r - $w / $h ) ) );
				}
				$newwidth  = $w;
				$newheight = $h;
			} else {
				if ( $w / $h > $r ) {
					$newwidth  = $h * $r;
					$newheight = $h;
				} else {
					$newheight = $w / $r;
					$newwidth  = $w;
				}
			}

			$src = imagecreatefromjpeg( $file );
			$dst = imagecreatetruecolor( $newwidth, $newheight );

			imagecopyresampled( $dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

			if ( $dest_file ) {
				imagejpeg( $dst, $dest_file );
			}

			return $dst;

		}


		/**
		 * Returns an array of all tags which contains defined attributes
		 *
		 * EXAMPLE:
		 * $result = extract_tag_with_attributes_from_html($content, 'img', array('class', 'src'));
		 *
		 * Array (
		 *     [<img src="/templates/Default/images/logAT.png" title="Annuaire Telechargement" alt="Annuaire Telechargement" >] => Array (
		 *         [src] => "/templates/Default/images/logAT.png"
		 *     )
		 *
		 *     [<img class="mainimg" data-newsid="880" src="https://zone-image.com/uploads/5ryLJ.jpg" width="145" height="193" border="0">] => Array (
		 *         [class] => "mainimg"
		 *         [src] => "https://zone-image.com/uploads/5ryLJ.jpg"
		 *     )
		 * )
		 *
		 * @param string $content
		 * @param string $tag_name
		 * @param array $attributes
		 *
		 * @return array
		 */
		public static function extract_tag_with_attributes_from_html( $content, $tag_name, $attributes ) {

			if ( ( empty( $attributes ) ) || ( ! is_array( $attributes ) ) ) {
				return array();
			}

			$tags   = array();
			$regexp = '/<' . $tag_name . '[^>]+>/i';

			if ( preg_match_all( $regexp, $content, $result ) ) {
				$atts   = join( '|', $attributes );
				$regexp = '/(' . $atts . ')\s*=\s*"([^"]*)"/i';
				foreach ( $result[0] as $html_tag ) {
					if ( preg_match_all( $regexp, $html_tag, $result_1 ) ) {
						$tags[ $html_tag ] = array_change_key_case( array_combine( $result_1[1], $result_1[2] ) );
					}
				}

			}

			return $tags;

		}

		/**
		 * Case-insensitive search in an array
		 *
		 * @param $needle
		 * @param $haystack
		 *
		 * @return bool
		 */
		public static function case_in_array( $needle, $haystack ) {

			if ( ( is_array( $haystack ) ) && ( ! empty( $haystack ) ) ) {
				foreach ( $haystack as $item ) {
					if ( 0 === strcasecmp( $needle, $item ) ) {
						return true;
					}
				}
			}

			return false;

		}

		/**
		 * Builds sql query conditions from a query array data
		 *
		 * $query_args = array(
		 *     'relation' => 'OR', // Optional, defaults to "AND"
		 *     array(
		 *         'field'   => '_my_custom_key',
		 *         'value'   => 'Value I am looking for',
		 *         'compare' => '='
		 *     ),
		 *     array(
		 *         'relation' => 'AND',
		 *         array(
		 *             'field'   => '_my_custom_key_2',
		 *             'value'   => '2000',
		 *             'compare' => '>',
		 *             'type' => 'NUMERIC',
		 *         ),
		 *         array(
		 *             'field'   => '_my_custom_key_3',
		 *             'value'   => 'Value I am looking for 3',
		 *             'compare' => '='
		 *         )
		 *     )
		 * );
		 *
		 * @param $query_args
		 *
		 * @return string
		 */
		public static function build_sql_query_conditions( $query_args ) {

			if ( ! is_array( $query_args ) ) {
				return '';
			}

			$relation = self::get_var( $query_args['relation'], 'AND' );

			$query_field = self::get_var( $query_args['field'], null );
			$query_value = self::get_var( $query_args['value'], null );
			$compare     = self::get_var( $query_args['compare'], '=' );

			if ( $query_field ) {
				$type = self::get_var( $query_args['type'], 'string' );
				$type = strtolower( $type );

				if ( $type == 'string' ) {
					$query_value = addslashes( $query_value );
					$query_value = "'{$query_value}'";
				}

				$sql_part = "({$query_field}{$compare}{$query_value})";

				if ( $sql_part ) {
					return $sql_part;
				}

			}

			$query_args = array_filter( $query_args, function ( $key ) {
				return is_numeric( $key );
			}, ARRAY_FILTER_USE_KEY );

			$sql_part       = '';
			$max_conditions = count( $query_args );
			for ( $i = 0; $i < $max_conditions; $i ++ ) {
				$query_datum = $query_args[ $i ];
				$sql_part    .= self::build_sql_query_conditions( $query_datum );
				if ( $i < $max_conditions - 1 ) {
					$sql_part .= " {$relation} ";
				}
			}

			$sql_part = $sql_part ? "({$sql_part})" : "";

			return $sql_part;

		}

		/**
		 * Returns a version number and an unique id to be used to load css or js
		 *
		 * @param $force_reload
		 *
		 * @return string
		 */
		public static function version( $force_reload = false ) {
			$version = TEAMTALLY_VERSION . ( $force_reload ? '_' . uniqid() : '' );

			return $version;

		}

		/**
		 * Enqueues a css style or a script from a string data
		 *
		 * This function is similar to wp_enqueue_script / wp_enqueue_style
		 * The script or style filename is relative to the assets theme directory
		 *
		 * $script_info structure:
		 * 1.  handle:the_handle_value|src:the_src_value|deps:the_dep_value1,the_dep_value2|ver:1.0
		 * 2.  the_handle_value|the_src_value|the_dep_value1,the_dep_value2|1.0
		 * 3.  the_src_value
		 *
		 * With the (format N°2), it is possible to omit parameters from the right
		 *
		 * @param string $script_info
		 *
		 * @return void
		 */
		public static function str_enqueue_script( $script_info, $force_reload = false ) {
			$params = array(
				'handle' => null,
				'src'    => null,
				'deps'   => null,
				'ver'    => null,
			);

			$params_list = array_keys( $params );

			$script_data = explode( '|', $script_info );

			if ( count( $script_data ) > 1 ) {
				for ( $i = 0; $i < count( $script_data ); $i ++ ) {
					$attr = $script_data[ $i ];

					$regexp = "/^\s*(.*?)\s*\:\s*(.+)\s*/is";
					if ( preg_match( $regexp, $attr, $matches ) ) {
						$param_name  = strtolower( $matches[1] );
						$param_value = $matches[2];
					} else {
						$param_name  = $params_list[ $i ];
						$param_value = $attr;
					}

					if ( is_null( $params[ $param_name ] ) ) {
						if ( 'deps' == $param_name ) {
							$param_value = explode( ',', $param_value );
						}

						if ( is_string( $param_value ) ) {
							$param_value = trim( $param_value );
						}

						$params[ $param_name ] = $param_value;

					}
				}
			} else {
				$params['src'] = (string) $script_info;
			}

			// abort if no src
			if ( is_null( $params['src'] ) ) {
				return;
			}

			// 'handle' - default value
			if ( is_null( $params['handle'] ) ) {
				$regexp = "/^(.+)\.(min\.css|min\.js|css|js)$/is";
				preg_match( $regexp, $params['src'], $matches );

				$handle           = strtolower( PROJECT_NAME ) . '-' . basename( $matches[1] );
				$handle           = str_replace( '.', '-', $handle );
				$params['handle'] = $handle;
			}

			// 'deps' - default value
			if ( is_null( $params['deps'] ) ) {
				$params['deps'] = array();
			}

			// 'ver' - default value
			if ( is_null( $params['ver'] ) ) {
				$params['ver'] = TEAMTALLY_VERSION;
			}

			// Enqueue script / style
			$src = $params['src'];
			$ext = strtolower( pathinfo( $src, PATHINFO_EXTENSION ) );

			$src     = TEAMTALLY_ASSETS_URI . $src;
			$version = $params['ver'] . ( $force_reload ? '_' . uniqid() : '' );

			switch ( $ext ) {
				case 'css':
					wp_enqueue_style(
						$params['handle'],
						$src,
						$params['deps'],
						$version
					);
					break;

				case 'js':
					wp_enqueue_script(
						$params['handle'],
						$src,
						$params['deps'],
						$version,
						true
					);
					break;
			}
		}


		/**
		 * Enqueues a list of styles or script
		 *
		 * @param $script_list
		 * @param bool $force_reload
		 *
		 * @return void
		 */
		public static function str_enqueue_script_list( $script_list, $force_reload = false ) {
			foreach ( $script_list as $script_info ) {
				self::str_enqueue_script( $script_info, $force_reload );
			}

		}

		/**
		 * Returns an asset url from a relative filename
		 *
		 * @param $relative_filename
		 * @param bool $display
		 *
		 * @return string
		 */
		public static function assets_url( $relative_filename, $display = true ) {
			$url = TEAMTALLY_ASSETS_URI . $relative_filename;

			if ( $display ) {
				print $url;
			}

			return $url;

		}

		/**
		 * Explodes a string into an array an only keeps non-empty values
		 *
		 * @param string $delimiter
		 * @param string $string
		 *
		 * @return array
		 */
		public static function explode_non_empty( $delimiter, $string ) {
			$string = array_values( array_filter( explode( $delimiter, $string ) ) );

			return $string;
		}

		/**
		 * Converts SVG data into a Base 64 data URI
		 *
		 * @param string $svg_data
		 *
		 * @return string
		 */
		public static function svg_to_base64( $svg_data ) {
			$base64_data = base64_encode( $svg_data );
			$data_uri    = 'data:image/svg+xml;base64,' . $base64_data;

			return $data_uri;
		}

		/**
		 * Returns a string containing a base64 data URI version of an SVG file
		 *
		 * @param $filename
		 *
		 * @return false|string
		 */
		public static function svg_file_to_base64( $filename ) {

			if ( ! file_exists( $filename ) ) {
				return false;
			}

			$svg_data = file_get_contents( $filename );

			return self::svg_to_base64( $svg_data );

		}

		/**
		 * Checks if the provided filename is of an image
		 *
		 * @param $filename
		 *
		 * @return false|int
		 */
		public static function filename_is_image( $filename ) {
			return preg_match( "/\.(png|jpg|jpeg|gif|bmp)\s*$/i", $filename );
		}


		/**
		 * Removes php code from a string
		 *
		 * @param string $value
		 *
		 * @return string
		 */
		public static function remove_php_code( $value ) {
			$value = str_replace( '<?php', '&lt;php', $value );
			$value = str_replace( '?>', '?&gt;', $value );
			$value = str_replace( '<?=', '&lt;?=', $value );

			return $value;

		}

		/**
		 * Remove html comments from a string
		 *
		 * @param $value
		 *
		 * @return array|string|string[]|null
		 */
		public static function remove_html_comments( $value ) {
			$clean_string = preg_replace( '/<!--(?!>)(?:(?!-->).)*-->/s', '', $value );

			return $clean_string;
		}

		/**
		 * Deletes a directory recursively
		 *
		 * @param $path
		 * @param $safeguard - absolute folder from which deletion is forbidden
		 *
		 * @return void
		 */
		public static function delete_directory( $path, $safeguard = false ) {
			$path = self::normalize_path( $path, false );

			if ( $safeguard ) {
				$safeguard = self::normalize_path( $safeguard, true );
			}

			$do_delete = ! $safeguard || (bool) strstr( $path, $safeguard );

			if ( is_dir( $path ) ) {
				$files = scandir( $path );
				foreach ( $files as $file ) {
					if ( $file != '.' && $file != '..' ) {
						self::delete_directory( $path . '/' . $file, $safeguard );
					}
				}

				if ( $do_delete ) {
					rmdir( $path );
				}

			} else {
				if ( $do_delete ) {
					unlink( $path );
				}
			}

		}

		/**
		 * Returns the upload_dir specific for the plugin
		 *
		 * @return array|false
		 */
		public static function upload_dir( $subdir = '' ) {
			$wp_upload_dir = wp_upload_dir( null, true );

			if ( ! $wp_upload_dir ) {
				return false;
			}

			$suffix = '/' . strtolower( PROJECT_NAME ) . '/' . $subdir;

			$upload_dir = self::normalize_path( $wp_upload_dir['basedir'], false ) . $suffix;

			$upload_dir = self::normalize_path( $upload_dir, true );

			if ( ! is_dir( $upload_dir ) ) {
				wp_mkdir_p( $upload_dir );
			}

			$upload_url = $wp_upload_dir['baseurl'] . $suffix;

			return array(
				'upload_dir' => $upload_dir,
				'upload_url' => $upload_url,
			);

		}

		/**
		 * Compresses a folder into a ZIP file
		 *
		 * @param $folder_path
		 * @param $zip_filename
		 *
		 * @return boolean
		 */
		public static function create_zip_file( $folder_path, $zip_filename ) {
			$folder_path = self::normalize_path( $folder_path, false );

			// Create a new ZipArchive object
			$zip = new ZipArchive();

			// Open the zip file for writing
			if ( $zip->open( $zip_filename, ZipArchive::CREATE ) !== true ) {
				return false;
			}

			// Add the files from the folder to the zip file
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $folder_path ),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ( $files as $name => $file ) {
				// Skip directories (they are added automatically)
				if ( ! $file->isDir() ) {
					// Get real and relative path for current file
					$filePath     = $file->getRealPath();
					$relativePath = substr( $filePath, strlen( $folder_path ) + 1 );

					// Add current file to archive
					$zip->addFile( $filePath, $relativePath );
				}
			}

			// Close the zip file
			$zip->close();

			return true;
		}

		/**
		 * Unzip a file into a folder
		 *
		 * @param $zip_file
		 * @param $extract_path
		 *
		 * @return bool
		 */
		public static function unzip_file( $zip_file, $extract_path ) {
			$status       = false;
			$extract_path = self::normalize_path( $extract_path, false );
			$zip          = new ZipArchive;

			if ( $zip->open( $zip_file ) === true ) {
				$zip->extractTo( $extract_path );
				$zip->close();
				$status = true;
			}

			return $status;

		}

		/**
		 * Deletes the media if it is unused
		 *
		 * @param $media_id
		 *
		 * @return bool
		 */
		public static function check_media_used_by_any_post( $media_id ) {

			// checks if the media is used by a post
			$posts = get_children( array(
				'post_type'   => 'any',
				'post_status' => 'any',
				'numberposts' => - 1,
				'meta_key'    => '_thumbnail_id',
				'meta_value'  => $media_id,
			) );

			$media_used = (boolean) $posts;

			return $media_used;
		}


	} /* End of class Helper */

	Helper::$DEBUG_FILENAME = Helper::dirname( __DIR__, 6 ) . '/debug.txt';


}