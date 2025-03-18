<?php
/**
 * WordPress Importer
 * https://github.com/humanmade/WordPress-Importer
 *
 * Released under the GNU General Public License v2.0
 * https://github.com/humanmade/WordPress-Importer/blob/master/LICENSE
 *
 * @since 2.0.0
 *
 * @package WPZOOM
 */

if ( ! class_exists( 'WPZOOM_WXR_Import_Info' ) && class_exists( 'WXR_Import_Info' ) ) {

	/**
	 * WPZOOM Import Info extends WXR Import Info
	 *
	 * @since 2.0.0
	 */
	class WPZOOM_WXR_Import_Info extends WXR_Import_Info {

		/**
		 * Navigation menu count
		 *
		 * @var Nav Menu Count
		 */
		public $nav_menu_count = 0;

		/**
		 * Page count
		 *
		 * @var Page Count
		 */
		public $page_count = 0;

		/**
		 * Portfolio count
		 *
		 * @var Portfolio Count
		 */
		public $portfolio_count = 0;

		/**
		 * Other count
		 *
		 * @var Other Count
		 */
		public $other_count = 0;

		/**
		 * Sort data to run import process one by one
		 *
		 * @var Sort data
		 */
		public $sort_data = array();

	}

}
