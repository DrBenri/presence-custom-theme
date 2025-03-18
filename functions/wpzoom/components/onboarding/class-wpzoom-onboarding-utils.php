<?php
/**
 * Onboarding utils.
 *
 * @package WPZOOM
 * @subpackage Onboarding
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPZOOM_Onboarding_Utils' ) ) {

	/**
	 * Generic utilities for WPZOOM Onboarding.
	 *
	 * All methods are static, poor-dev name-spacing class wrapper.
	 *
	 * @since 2.0.0
	 *
	 * @package WPZOOM
	 */
	class WPZOOM_Onboarding_Utils {
		/**
		 * Wrap an arbitrary string in <ul> tags.
		 *
		 * @since 2.0.0
		 *
		 * @static
		 *
		 * @param string $string Text to be wrapped.
		 * @param array  $atts <li> element attributes (e.g. array('property' => 'value')).
		 * @return string
		 */
		public static function wrap_in_ul( $string, $atts = array() ) {
			if ( ! empty( $atts ) && is_array( $atts ) ) {
				$attr = '';
				foreach ( $atts as $prop => $value ) {
					$attr .= ' ' . $prop . '="' . esc_attr( $value ) . '"';
				}
				return '<ul ' . $attr . '>' . wp_kses_post( $string ) . '</ul>';
			}
			return '<ul>' . wp_kses_post( $string ) . '</ul>';
		}

		/**
		 * Wrap an arbitrary string in <li> tags. Meant to be used in combination with array_map().
		 *
		 * @since 2.0.0
		 *
		 * @static
		 *
		 * @param string $string Text to be wrapped.
		 * @param array  $atts <li> element attributes (e.g. array('property' => 'value')).
		 * @return string
		 */
		public static function wrap_in_li( $string, $atts = array() ) {
			if ( ! empty( $atts ) && is_array( $atts ) ) {
				$attr = '';
				foreach ( $atts as $prop => $value ) {
					$attr .= ' ' . $prop . '="' . esc_attr( $value ) . '"';
				}
				return '<li ' . $attr . '>' . wp_kses_post( $string ) . '</li>';
			}
			return '<li>' . wp_kses_post( $string ) . '</li>';
		}

		/**
		 * Removes the contents of the cache key in the group.
		 *
		 * If the cache key does not exist in the group, then nothing will happen.
		 *
		 * @param int|string $key        What the contents in the cache are called.
		 * @param string     $group      Optional. Where the cache contents are grouped. Default ''.
		 * @return bool False if the contents weren't deleted and true on success.
		 */
		public static function onboarding_cache_delete( $key, $group = '' ) {
			$option = $key;
			if ( ! empty( $group ) ) {
				$option = "{$key}_{$group}";
			}
			return delete_option( $option );
		}

		/**
		 * Retrieves the cache contents, if it exists.
		 *
		 * The contents will be first attempted to be retrieved by searching by the
		 * key in the cache group. If the cache is hit (success) then the contents
		 * are returned.
		 *
		 * @param int|string $key   The key under which the cache contents are stored.
		 * @param string     $group Optional. Where the cache contents are grouped. Default 'default'.
		 * @return mixed|false The cache contents on success, false on failure to retrieve contents.
		 */
		public static function onboarding_cache_get( $key, $group = '' ) {
			$option = $key;
			if ( ! empty( $group ) ) {
				$option = "{$key}_{$group}";
			}
			$cache = get_option( $option, false );
			return $cache;
		}

		/**
		 * Sets the data contents into the cache.
		 *
		 * The cache contents are grouped by the $group parameter followed by the
		 * $key. This allows for duplicate IDs in unique groups. Therefore, naming of
		 * the group should be used with care and should follow normal function
		 * naming guidelines outside of core WordPress usage.
		 *
		 * @param int|string $key    What to call the contents in the cache.
		 * @param mixed      $data   The contents to store in the cache.
		 * @param string     $group  Optional. Where to group the cache contents. Default ''.
		 * @return true Always returns true.
		 */
		public static function onboarding_cache_set( $key, $data, $group = '' ) {
			$option = $key;
			if ( ! empty( $group ) ) {
				$option = "{$key}_{$group}";
			}
			$cache = get_option( $option, false );
			if ( $cache ) {
				update_option( $option, $data );
			} else {
				add_option( $option, $data );
			}
			return true;
		}

		/**
		 * Extract design slug from file name
		 *
		 * @param string $file_name The file name to extract design slug.
		 * @return mixed Design slug if found in file name, otherwise false.
		 */
		public static function extract_design_slug_from_filename( $file_name ) {
			$regex = '/^(.*?)(\-import|\-backup)/';
			preg_match( $regex, $file_name, $matches, PREG_OFFSET_CAPTURE, 0 );
			if ( isset( $matches[1] ) && isset( $matches[1][0] ) ) {
				return $matches[1][0];
			}
			return false;
		}

		/**
		 * Get file size
		 *
		 * @param string $file The file to get size for.
		 * @return int
		 */
		public static function get_file_size( $file ) {
			return WPZOOM_Demo_Import::get_instance()->get_filesystem()->size( $file );
		}

		/**
		 * Get file modified time
		 *
		 * @param string $file The file to get modified time for.
		 * @return int
		 */
		public static function get_file_mtime( $file ) {
			return WPZOOM_Demo_Import::get_instance()->get_filesystem()->mtime( $file );
		}

		/**
		 * Get date and time in i18n format for file
		 *
		 * @param string $file The file to get date and time for.
		 * @return mixed
		 */
		public static function get_file_date_time( $file ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			$file_mtime  = WPZOOM_Demo_Import::get_instance()->get_filesystem()->mtime( $file );
			if ( $file_mtime ) {
				/* translators: %1$s Date, %2$s Time */
				return sprintf( _x( '%1$s at %2$s', 'Snapshot file date and time', 'wpzoom' ), gmdate( $date_format, $file_mtime ), gmdate( $time_format, $file_mtime ) );
			}
			return false;
		}

	} // End of class WPZOOM_Onboarding_Utils

} // End of class_exists wrapper
